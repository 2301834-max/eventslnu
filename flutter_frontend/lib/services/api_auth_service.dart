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
      body: {'email': email, 'password': password},
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
    required String passwordConfirmation,
    String? studentId,
  }) async {
    final body = {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
    };
    if (studentId != null) {
      body['student_id'] = studentId;
    }
    await _api.postJson('/api/register', auth: false, body: body);
  }

  Future<ApiUser> getMe() async {
    final json = await _api.getJson('/api/user');
    return ApiUser.fromJson(json);
  }
}
