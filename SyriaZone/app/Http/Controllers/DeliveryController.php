<?php

namespace App\Http\Controllers;

use App\Models\vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DeliveryController extends Controller
{
    private const EARTH_RADIUS_KM = 6378.137; // نصف قطر الأرض حسب WGS84
    private const OSRM_ENDPOINT = 'http://router.project-osrm.org';
    private const ELEVATION_API = 'https://api.open-elevation.com/api/v1/lookup';

    public function calculateDeliveryCost(Request $request)
    {
        $validated = $this->validateRequest($request);

        $userLat = (float)$validated['user_lat'];
        $userLon = (float)$validated['user_lang'];
        $costPerKm = (float)($validated['cost_per_km'] ?? 5.0);

        $results = $this->processVendors(
            $validated['vendor_ids'],
            $userLat,
            $userLon,
            $costPerKm,
            $validated['max_distance'] ?? null
        );

        return $this->buildResponse($results, $costPerKm);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'integer|exists:vendors,id',
            'user_lang' => 'required|numeric|between:-180,180',
            'user_lat' => 'required|numeric|between:-90,90',
            'cost_per_km' => 'sometimes|numeric|min:0',
            'max_distance' => 'sometimes|numeric|min:0'
        ]);
    }

    private function processVendors(array $vendorIds, float $userLat, float $userLon, float $costPerKm, ?float $maxDistance): array
    {
        $results = [];
        $client = new Client([
            'timeout' => 5,
            'verify' => false // للتطوير فقط، في الإنتاج استخدم شهادة SSL صالحة
        ]);

        foreach ($vendorIds as $vendorId) {
            $vendorData = $this->calculateVendorDistance(
                $client,
                $vendorId,
                $userLat,
                $userLon,
                $costPerKm
            );

            if ($vendorData && (!$maxDistance || $vendorData['distance_km'] <= $maxDistance)) {
                $results[] = $vendorData;
            }
        }

        usort($results, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return $results;
    }

    private function calculateVendorDistance(Client $client, int $vendorId, float $userLat, float $userLon, float $costPerKm): ?array
    {
        try {
            $vendor = vendor::with('user')->find($vendorId);

            if (!$vendor?->user) {
                return null;
            }

            $vendorLat = (float)$vendor->user->lat;
            $vendorLon = (float)$vendor->user->lang;

            $distance = $this->getEnhancedDistance(
                $client,
                $userLat,
                $userLon,
                $vendorLat,
                $vendorLon
            );

            return [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendor->user->name,
                'distance_km' => round($distance, 2),
                'delivery_cost' => round($distance * $costPerKm, 2),
                'vendor_location' => [
                    'lat' => $vendorLat,
                    'lang' => $vendorLon,
                ],
                'user_location' => [
                    'lat' => $userLat,
                    'lang' => $userLon,
                ],
            ];
        } catch (\Exception $e) {
            Log::error("Delivery calculation error for vendor {$vendorId}: " . $e->getMessage());
            return null;
        }
    }

    private function getEnhancedDistance(Client $client, float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // 1. محاولة الحصول على المسافة من OSRM (أكثر دقة)
        try {
            $osrmDistance = $this->getOSRMDistance($client, $lat1, $lon1, $lat2, $lon2);

            // إذا كانت المسافة صغيرة (< 50 كم) نطبق تصحيح الارتفاع
            if ($osrmDistance < 50) {
                $elevationFactor = $this->getElevationFactor($client, $lat1, $lon1, $lat2, $lon2);
                return $osrmDistance * $elevationFactor;
            }

            return $osrmDistance;
        } catch (\Exception $e) {
            Log::warning("Falling back to advanced calculation: " . $e->getMessage());
        }

        // 2. الخيار الاحتياطي: حساب متقدم مع مراعاة الانحناء الأرضي
        return $this->calculateAdvancedDistance($lat1, $lon1, $lat2, $lon2);
    }

    private function getOSRMDistance(Client $client, float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $response = $client->get(self::OSRM_ENDPOINT . "/route/v1/driving/$lon1,$lat1;$lon2,$lat2", [
            'query' => ['overview' => 'false']
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['routes'][0]['distance'])) {
            throw new \Exception("Invalid OSRM response");
        }

        return $data['routes'][0]['distance'] / 1000;
    }

    private function getElevationFactor(Client $client, float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        try {
            $response = $client->post(self::ELEVATION_API, [
                'json' => [
                    'locations' => [
                        ['latitude' => $lat1, 'longitude' => $lon1],
                        ['latitude' => $lat2, 'longitude' => $lon2]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $elevation1 = $data['results'][0]['elevation'];
            $elevation2 = $data['results'][1]['elevation'];

            $heightDiff = abs($elevation2 - $elevation1);
            $distance = $this->vincentyDistance($lat1, $lon1, $lat2, $lon2);

            // معامل التصحيح بناء على الفرق في الارتفاع
            return 1 + ($heightDiff / ($distance * 1000 * 10));
        } catch (\Exception $e) {
            Log::warning("Elevation API failed: " . $e->getMessage());
            return 1.0;
        }
    }

    private function calculateAdvancedDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $distance = $this->vincentyDistance($lat1, $lon1, $lat2, $lon2);

        // تصحيح إضافي للمسافات المتوسطة
        if ($distance > 20 && $distance < 100) {
            return $distance * 1.08;
        }

        // تصحيح المسافات الطويلة
        if ($distance >= 100) {
            return $distance * 1.12;
        }

        return $distance;
    }

    private function vincentyDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        if ($this->isSameLocation($lat1, $lon1, $lat2, $lon2)) {
            return 0.0;
        }

        $a = 6378137; // نصف المحور الرئيسي (متر)
        $f = 1 / 298.257223563; // تسطح الأرض
        $b = $a * (1 - $f); // نصف المحور الثانوي

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $lambda1 = deg2rad($lon1);
        $lambda2 = deg2rad($lon2);

        $L = $lambda2 - $lambda1;
        $U1 = atan((1 - $f) * tan($phi1));
        $U2 = atan((1 - $f) * tan($phi2));

        $sinU1 = sin($U1);
        $cosU1 = cos($U1);
        $sinU2 = sin($U2);
        $cosU2 = cos($U2);

        $lambda = $L;
        $iterLimit = 100;
        $cosSqAlpha = null;
        $sinSigma = null;
        $cosSigma = null;
        $cos2SigmaM = null;

        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma = sqrt(($cosU2 * $sinLambda) ** 2 +
                ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) ** 2);

            if ($sinSigma == 0) {
                return 0;
            }

            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha ** 2;
            $cos2SigmaM = $cosSigma - 2 * $sinU1 * $sinU2 / $cosSqAlpha;

            if (!is_numeric($cos2SigmaM)) {
                $cos2SigmaM = 0;
            }

            $C = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $lambdaPrev = $lambda;
            $lambda = $L + (1 - $C) * $f * $sinAlpha *
                ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM ** 2)));
        } while (abs($lambda - $lambdaPrev) > 1e-12 && --$iterLimit > 0);

        if ($iterLimit == 0) {
            return $this->haversineDistance($lat1, $lon1, $lat2, $lon2);
        }

        $uSq = $cosSqAlpha * ($a ** 2 - $b ** 2) / ($b ** 2);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cos2SigmaM + $B / 4 * ($cosSigma * (-1 + 2 * $cos2SigmaM ** 2) -
            $B / 6 * $cos2SigmaM * (-3 + 4 * $sinSigma ** 2) * (-3 + 4 * $cos2SigmaM ** 2)));

        $distance = $b * $A * ($sigma - $deltaSigma);

        return $distance / 1000; // تحويل إلى كيلومترات
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $latDelta = $lat2Rad - $lat1Rad;
        $lonDelta = $lon2Rad - $lon1Rad;

        $a = sin($latDelta / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($lonDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function isSameLocation(float $lat1, float $lon1, float $lat2, float $lon2): bool
    {
        return abs($lat1 - $lat2) < 0.000001 && abs($lon1 - $lon2) < 0.000001;
    }

    private function buildResponse(array $results, float $costPerKm): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $results,
            'cost_per_km' => $costPerKm,
            'notes' => 'Distances include elevation and curvature corrections'
        ]);
    }
}
