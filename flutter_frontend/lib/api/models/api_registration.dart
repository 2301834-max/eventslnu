class ApiRegistration {
  ApiRegistration({
    required this.id,
    required this.eventId,
    required this.userId,
    required this.status,
    required this.registrationNumber,
    required this.qrPayload,
    required this.qrExpiresAt,
  });

  final int id;
  final int eventId;
  final int userId;
  final String status;
  final String? registrationNumber;
  final String? qrPayload;
  final DateTime? qrExpiresAt;

  bool get isApproved => status.toLowerCase() == 'approved';

  factory ApiRegistration.fromJson(Map<String, dynamic> json) {
    final qr = json['qr_code'] ?? json['qrCode'];
    String? payload;
    DateTime? expiresAt;
    if (qr is Map<String, dynamic>) {
      payload = qr['code']?.toString();
      final exp = qr['expires_at'];
      expiresAt = exp == null ? null : DateTime.tryParse(exp.toString());
    }

    return ApiRegistration(
      id: (json['id'] as num).toInt(),
      eventId: (json['event_id'] as num).toInt(),
      userId: (json['user_id'] as num).toInt(),
      status: (json['status'] ?? '').toString(),
      registrationNumber: json['registration_number']?.toString(),
      qrPayload: payload,
      qrExpiresAt: expiresAt,
    );
  }
}

