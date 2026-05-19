import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/services/api_auth_service.dart';

class ApiRegistrationService {
  ApiRegistrationService(this._api, this._authService);

  final ApiClient _api;
  final ApiAuthService _authService;

  Future<List<ApiRegistration>> listMyRegistrations() async {
    final json = await _api.getJson('/api/registrations/me');
    final data = json['data'];
    if (data is! List) return const [];

    return data
        .whereType<Map<String, dynamic>>()
        .map(ApiRegistration.fromJson)
        .toList();
  }

  Future<ApiRegistration?> getMyRegistration(int eventId) async {
    final json = await _api.getJson('/api/events/$eventId/registrations/me');
    final data = json['data'];
    if (data == null) return null;
    if (data is! Map<String, dynamic>) return null;
    return ApiRegistration.fromJson(data);
  }

  Future<ApiRegistration> registerForEvent(int eventId) async {
    final studentId = await _requiredStudentId();
    final json = await _api.postJson(
      '/api/events/$eventId/registrations',
      body: {'student_id': studentId},
    );
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw ApiException('Unexpected registration response.');
    }
    return ApiRegistration.fromJson(data);
  }

  Future<Map<String, dynamic>> registerViaQr(String qrPayload) async {
    final studentId = await _requiredStudentId();
    return _api.postJson(
      '/api/qr/register',
      body: {'qr_code': qrPayload, 'student_id': studentId},
    );
  }

  Future<String> _requiredStudentId() async {
    final user = await _authService.getMe();
    final studentId = user.studentId?.trim();
    if (studentId == null || studentId.isEmpty) {
      throw ApiException(
        'Your account is missing a student ID. Please update your profile before registering.',
      );
    }
    return studentId;
  }
}
