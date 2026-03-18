import 'package:flutter_smart_event/api/api_client.dart';

class ApiAttendanceService {
  ApiAttendanceService(this._api);

  final ApiClient _api;

  Future<Map<String, dynamic>> checkIn({
    required int eventId,
    required String qrPayload,
    String? location,
  }) {
    return _api.postJson(
      '/api/events/$eventId/attendance/check-in',
      body: {
        'qr_code': qrPayload,
        if (location != null) 'location': location,
      },
    );
  }
}

