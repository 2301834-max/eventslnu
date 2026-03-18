import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_user.dart';
import 'package:flutter_smart_event/api/token_store.dart';

class ApiAuthService {
  ApiAuthService(this._api, this._tokenStore);

  final ApiClient _api;
  final TokenStore _tokenStore;

  Future<ApiUser> login({
    required String email,
    required String password,
  }) async {
    final json = await _api.postJson(
      '/api/login',
      auth: false,
      body: {
        'email': email,
        'password': password,
      },
    );

    final token = (json['token'] ?? '').toString();
    final userJson = json['user'];
    if (token.isEmpty || userJson is! Map<String, dynamic>) {
      throw ApiException('Unexpected login response.');
    }

    await _tokenStore.writeToken(token);
    return ApiUser.fromJson(userJson);
  }

  Future<void> logout() async {
    try {
      await _api.postJson('/api/logout', body: const {});
    } catch (_) {
      // ignore: best-effort
    }
    await _tokenStore.clear();
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
  }) async {
    await _api.postJson(
      '/api/register',
      auth: false,
      body: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      },
    );
  }

  Future<ApiUser> getMe() async {
    final json = await _api.getJson('/api/user');
    final userJson = json is Map<String, dynamic> ? json : <String, dynamic>{};
    return ApiUser.fromJson(userJson);
  }
}

