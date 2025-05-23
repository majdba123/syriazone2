class ApiConfig {
  static String baseUrl = 'https://a017-149-34-244-157.ngrok-free.app';
  static const String apiPrefix = '/api';
  static String get apiUrl => '$baseUrl$apiPrefix';

  static String? authToken;

  static Map<String, String> get headers {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (authToken != null) 'Authorization': 'Bearer $authToken',
    };
  }

  static void updateBaseUrl(String newUrl) {
    baseUrl = newUrl;
  }

  static void setAuthToken(String token) {
    authToken = token;
  }
}
