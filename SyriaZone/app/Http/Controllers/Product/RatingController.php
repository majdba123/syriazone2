<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Rating\UpdateRatingRequest;
use App\Http\Requests\Rating\RatingStoreRequest;
use App\Models\Product;
use App\Services\Rating\RatingService;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function rateProduct(RatingStoreRequest $request, $id)
    {
        $rating = $this->ratingService->rateProduct($id, $request->validated());

        return response()->json($rating, 201);
    }



    public function update(UpdateRatingRequest $request, $id)
    {
        $updatedRating = $this->ratingService->updateRating($id, $request->validated());

        return response()->json($updatedRating);
    }



    public function destroy($id)
    {
        $response = $this->ratingService->deleteRating($id);

        return $response;
    }

    public function getUserRatings()
    {
        $ratings = $this->ratingService->getUserRatings();

        return response()->json($ratings, 200);
    }

    public function getRateProduct($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            return ['message' => 'Product not found', 'status' => 404];
        }


        $result = $this->ratingService->GetAllRateProduct($product_id);


        return $result ;
    }
}
