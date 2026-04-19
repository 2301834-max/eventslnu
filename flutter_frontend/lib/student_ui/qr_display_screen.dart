import 'package:flutter/material.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';
import 'package:qr_flutter/qr_flutter.dart';

class QRDisplayScreen extends StatelessWidget {
  const QRDisplayScreen({
    super.key,
    required this.eventId,
    required this.qrPayload,
    this.expiresAt,
  });

  final int eventId;
  final String qrPayload;
  final DateTime? expiresAt;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Attendance QR')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const StudentHeaderCard(
            kicker: 'Attendance Pass',
            title: 'Present this QR when the organizer scans attendance.',
            subtitle:
                'Keep your screen visible and bright so the check-in scanner can read it quickly.',
          ),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(28),
                  ),
                  child: QrImageView(
                    data: qrPayload,
                    size: 250,
                    backgroundColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  'Event ID $eventId',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: StudentPalette.textPrimary,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  expiresAt == null
                      ? 'This QR will stay active until the event team revokes it.'
                      : 'Expires ${formatEventDateTime(expiresAt)}',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: StudentPalette.textSecondary,
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
