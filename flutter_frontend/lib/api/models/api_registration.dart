import 'package:flutter_smart_event/api/models/api_event.dart';

class ApiRegistration {
  ApiRegistration({
    required this.id,
    required this.eventId,
    required this.userId,
    required this.status,
    required this.registrationNumber,
    required this.qrPayload,
    required this.qrExpiresAt,
    required this.event,
    required this.createdAt,
    required this.approvedAt,
    required this.attendance,
  });

  final int id;
  final int eventId;
  final int userId;
  final String status;
  final String? registrationNumber;
  final String? qrPayload;
  final DateTime? qrExpiresAt;
  final ApiEvent? event;
  final DateTime? createdAt;
  final DateTime? approvedAt;
  final ApiAttendanceProof? attendance;

  bool get isApproved => status.toLowerCase() == 'approved';
  bool get hasAttendanceProof => attendance?.checkedInAt != null;

  factory ApiRegistration.fromJson(Map<String, dynamic> json) {
    final qr = json['qr_code'] ?? json['qrCode'];
    String? payload;
    DateTime? expiresAt;
    if (qr is Map<String, dynamic>) {
      payload = qr['code']?.toString();
      final exp = qr['expires_at'];
      expiresAt = exp == null ? null : DateTime.tryParse(exp.toString());
    }
    final eventJson = json['event'];
    final attendanceJson =
        json['attendance_record'] ?? json['attendanceRecord'];
    DateTime? parseDate(dynamic value) =>
        value == null ? null : DateTime.tryParse(value.toString());

    return ApiRegistration(
      id: (json['id'] as num).toInt(),
      eventId: (json['event_id'] as num).toInt(),
      userId: (json['user_id'] as num).toInt(),
      status: (json['status'] ?? '').toString(),
      registrationNumber: json['registration_number']?.toString(),
      qrPayload: payload,
      qrExpiresAt: expiresAt,
      event: eventJson is Map<String, dynamic>
          ? ApiEvent.fromJson(eventJson)
          : null,
      createdAt: parseDate(json['created_at']),
      approvedAt: parseDate(json['approved_at']),
      attendance: attendanceJson is Map<String, dynamic>
          ? ApiAttendanceProof.fromJson(attendanceJson)
          : null,
    );
  }
}

class ApiAttendanceProof {
  ApiAttendanceProof({
    required this.checkedInAt,
    required this.checkedOutAt,
    required this.qrCodeReference,
    required this.location,
  });

  final DateTime? checkedInAt;
  final DateTime? checkedOutAt;
  final String? qrCodeReference;
  final String? location;

  factory ApiAttendanceProof.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic value) =>
        value == null ? null : DateTime.tryParse(value.toString());

    return ApiAttendanceProof(
      checkedInAt: parseDate(json['checked_in_at']),
      checkedOutAt: parseDate(json['checked_out_at']),
      qrCodeReference: json['qr_code_reference']?.toString(),
      location: json['check_in_location']?.toString(),
    );
  }
}
