import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:syriazon/core/config/api_config.dart';
import 'package:syriazon/model/category_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class Vendor {
  final int id;
  final String name;

  Vendor({required this.id, required this.name});

  factory Vendor.fromJson(Map<String, dynamic> json) {
    return Vendor(id: json['id'], name: json['name']);
  }
}

class ProductService {
  static Future<Map<String, dynamic>> getFilteredProducts({
    int? categoryId,
    int? subCategoryId,
    int? vendorId,
    String? name,
    double? minPrice,
    double? maxPrice,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (categoryId != null)
        queryParams['category_id'] = categoryId.toString();
      if (subCategoryId != null)
        queryParams['subcategory_id'] = subCategoryId.toString();
      if (vendorId != null) queryParams['vendor_id'] = vendorId.toString();
      if (name != null && name.isNotEmpty) queryParams['name'] = name;
      if (minPrice != null)
        queryParams['min_price'] = minPrice.toStringAsFixed(2);
      if (maxPrice != null)
        queryParams['max_price'] = maxPrice.toStringAsFixed(2);

      final uri = Uri.parse(
        '${ApiConfig.apiUrl}/user/product/search',
      ).replace(queryParameters: queryParams);

      print('Request URL: ${uri.toString()}');

      final response = await http
          .get(uri, headers: ApiConfig.headers)
          .timeout(const Duration(seconds: 30));

      return _handleProductResponse(response);
    } on http.ClientException catch (e) {
      print('Client exception: $e');
      return _handleError(e);
    } catch (e) {
      print('Unexpected error: $e');
      return _handleError(e);
    }
  }

  static Future<bool> logout() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final response = await http.post(
        Uri.parse('${ApiConfig.apiUrl}/api/logout'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        await prefs.remove('auth_token');
        await prefs.remove('user_data');
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  static Future<Map<String, dynamic>> rateProduct({
    required int productId,
    required int rating,
    String? comment,
  }) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final response = await http.post(
        Uri.parse('${ApiConfig.apiUrl}/api/user/rate/store/$productId'),
        headers: ApiConfig.headers,
        body: {
          'num': rating.toString(),
          if (comment != null) 'comment': comment,
        },
      );

      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  // Category and Subcategory related methods
  static Future<List<Category>> getAllCategories() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.apiUrl}/user/categories/get_all'),
        headers: ApiConfig.headers,
      );

      if (response.statusCode == 200) {
        final List<dynamic> decodedList =
            jsonDecode(response.body) as List<dynamic>;

        return decodedList.map((item) {
          try {
            return Category.fromJson(item as Map<String, dynamic>);
          } catch (e) {
            print('Error parsing category: $e');
            print('Problematic item: $item');
            throw Exception('Failed to parse category: $e');
          }
        }).toList();
      } else {
        throw Exception('Failed with status: ${response.statusCode}');
      }
    } catch (e, stackTrace) {
      print('Error in getAllCategories: $e');
      print('Stack trace: $stackTrace');
      throw Exception('Failed to load categories: $e');
    }
  }

  static Future<List<SubCategory>> getAllSubCategories() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.apiUrl}/user/subcategories/getall'),
        headers: ApiConfig.headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as List;
        return data.map((json) => SubCategory.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load subcategories: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error loading subcategories: ${e.toString()}');
    }
  }

  static Future<Map<String, dynamic>> getProductsByCategory(
    int categoryId,
  ) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final headers = {
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

      final response = await http.get(
        Uri.parse('${ApiConfig.apiUrl}/user/product/category/$categoryId'),
        headers: ApiConfig.headers,
      );

      return _handleProductResponse(response);
    } catch (e) {
      return _handleError(e);
    }
  }

  static Future<Map<String, dynamic>> getProductsBySubCategory(
    int subCategoryId,
  ) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final headers = {
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

      final response = await http.get(
        Uri.parse(
          '${ApiConfig.apiUrl}/user/product/subcategory/$subCategoryId',
        ),
        headers: ApiConfig.headers,
      );

      return _handleProductResponse(response);
    } catch (e) {
      return _handleError(e);
    }
  }

  static Future<Map<String, dynamic>> searchProducts({
    String? name,
    double? minPrice,
    double? maxPrice,
    int? categoryId,
    int? subCategoryId,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = {
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (name != null && name.isNotEmpty) queryParams['name'] = name;
      if (minPrice != null) queryParams['min_price'] = minPrice.toString();
      if (maxPrice != null) queryParams['max_price'] = maxPrice.toString();
      if (categoryId != null)
        queryParams['category_id'] = categoryId.toString();
      if (subCategoryId != null)
        queryParams['subcategory_id'] = subCategoryId.toString();

      final uri = Uri.parse(
        '${ApiConfig.apiUrl}/user/product/search',
      ).replace(queryParameters: queryParams);

      final response = await http.get(uri, headers: ApiConfig.headers);
      return _handleProductResponse(response);
    } catch (e) {
      return _handleError(e);
    }
  }

  // Helper methods
  static Map<String, dynamic> _handleProductResponse(http.Response response) {
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final productsData = data['products'] ?? data['data'];

      if (productsData == null) {
        return {
          'success': false,
          'message': 'No products data found in response',
        };
      }

      return {
        'success': true,
        'message': data['message'] ?? 'Success',
        'products':
            (productsData is Map ? productsData['data'] : productsData) ?? [],
        'pagination': {
          'current_page':
              data['current_page'] ??
              (productsData is Map ? productsData['current_page'] : 1) ??
              1,
          'total_pages':
              data['last_page'] ??
              (productsData is Map ? productsData['last_page'] : 1) ??
              1,
          'total_items':
              data['total'] ??
              (productsData is Map ? productsData['total'] : 0) ??
              0,
        },
      };
    } else {
      return {
        'success': false,
        'message': 'Failed to fetch products: ${response.statusCode}',
      };
    }
  }

  static Map<String, dynamic> _handleError(dynamic e) {
    return {'success': false, 'message': 'Error: ${e.toString()}'};
  }

  static Future<Map<String, dynamic>> getProfile() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.apiUrl}/user/profile/my_info'),
        headers: ApiConfig.headers,
      );

      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> data, {
    File? image,
  }) async {
    try {


      var request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiConfig.apiUrl}/user/profile/update'),
      );

      request.headers.addAll(ApiConfig.headers);

      // Add image if exists
      if (image != null) {
        request.files.add(
          await http.MultipartFile.fromPath('image', image.path),
        );
      }

      // Add other fields
      data.forEach((key, value) {
        if (value != null) {
          request.fields[key] = value.toString();
        }
      });

      final response = await request.send();
      final responseData = await response.stream.bytesToString();

      return jsonDecode(responseData);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
}
