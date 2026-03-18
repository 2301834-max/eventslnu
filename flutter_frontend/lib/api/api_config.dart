class ApiConfig {
  ApiConfig._();

  /// Base URL for Laravel API (no trailing slash).
  ///
  /// IMPORTANT when using a **physical phone**:
  /// - Replace this with your computer's LAN IP address.
  ///   Example: 'http://192.168.1.23:8000'
  /// - Make sure Docker/Laravel is listening on 0.0.0.0:8000 (your setup already does).
  static const String baseUrl = 'http://192.168.1.32:8000';
  
}

