import 'package:flutter/material.dart';
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
      appBar: AppBar(title: const Text('My QR Code')),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              QrImageView(
                data: qrPayload,
                size: 260,
                backgroundColor: Colors.white,
              ),
              const SizedBox(height: 16),
              if (expiresAt != null)
                Text(
                  'Expires: ${expiresAt!.toLocal()}',
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              const SizedBox(height: 8),
              Text(
                'Event ID: $eventId',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

