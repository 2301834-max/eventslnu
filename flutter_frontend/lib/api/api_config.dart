class ApiConfig {
  ApiConfig._();

  /// Base URL for Laravel API (no trailing slash).
  ///
  /// IMPORTANT when using a **physical phone**:
  /// - Replace this with your computer's LAN IP address.
  ///   Example: 'http://192.168.1.23:8000'
  /// - Make sure Docker/Laravel is listening on 0.0.0.0:8000 (your setup already does).
  static const String baseUrl = 'http://10.10.224.94:8000';

  static String resolveUrl(String? value) {
    if (value == null || value.trim().isEmpty) {
      return '';
    }

    final trimmed = value.trim();
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }

    if (trimmed.startsWith('/')) {
      return '$baseUrl$trimmed';
    }

    return '$baseUrl/$trimmed';
  }
}

