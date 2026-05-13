import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';

class ApiRegistrationService {
  ApiRegistrationService(this._api);

  final ApiClient _api;

  Future<ApiRegistration?> getMyRegistration(int eventId) async {
    final json = await _api.getJson('/api/events/$eventId/registrations/me');
    final data = json['data'];
    if (data == null) return null;
    if (data is! Map<String, dynamic>) return null;
    return ApiRegistration.fromJson(data);
  }

  Future<ApiRegistration> registerForEvent(int eventId) async {
    final json = await _api.postJson('/api/events/$eventId/registrations', body: const {});
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw ApiException('Unexpected registration response.');
    }
    return ApiRegistration.fromJson(data);
  }

  Future<Map<String, dynamic>> registerViaQr(String qrPayload) async {
    return _api.postJson(
      '/api/qr/register',
      body: {
        'qr_code': qrPayload,
      },
    );
  }
}

