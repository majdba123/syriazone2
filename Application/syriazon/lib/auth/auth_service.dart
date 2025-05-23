import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:syriazon/core/config/api_config.dart';

class AuthService {
  static Future<Map<String, dynamic>?> login(
    String email,
    String password,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.apiUrl}/login'),
        headers: ApiConfig.headers,
        body: jsonEncode({'email': email, 'password': password}),
      );

      if (response.statusCode == 200) {
        final responseData = jsonDecode(response.body);
        final token = responseData['access_token']['original']['token'];
        final userType = responseData['access_token']['original']['user_type'];

        ApiConfig.authToken = token;
        return {'token': token, 'user_type': userType};
      }
      return null;
    } catch (e) {
      throw Exception('فشل تسجيل الدخول: $e');
    }
  }

  static Future<bool> forgotPassword(String email) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.apiUrl}/forgot-password'),
        headers: ApiConfig.headers,
        body: jsonEncode({'email': email}),
      );

      return response.statusCode == 200;
    } catch (e) {
      throw Exception('Failed to send reset link: $e');
    }
  }

  static Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.apiUrl}/register'),
        headers: ApiConfig.headers,
        body: jsonEncode({'name': name, 'email': email, 'password': password}),
      );

      print('Register Response: ${response.body}');

      if (response.statusCode == 201 || response.statusCode == 200) {
        final responseData = jsonDecode(response.body);

        if (responseData['message']?.contains('successfully') ?? false) {
          return {
            'success': true,
            'user': responseData['user'],
            'message': responseData['message'],
          };
        }
      }

      return {
        'success': false,
        'message':
            response.statusCode == 422
                ? 'البريد الإلكتروني مسجل مسبقاً'
                : 'فشل في التسجيل',
      };
    } catch (e) {
      print('Register Error: $e');
      return {'success': false, 'message': 'حدث خطأ في الاتصال'};
    }
  }
}
