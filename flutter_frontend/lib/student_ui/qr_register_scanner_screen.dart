import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/qr_display_screen.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QRRegisterScannerScreen extends StatefulWidget {
  const QRRegisterScannerScreen({super.key});

  @override
  State<QRRegisterScannerScreen> createState() => _QRRegisterScannerScreenState();
}

class _QRRegisterScannerScreenState extends State<QRRegisterScannerScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _submitting = false;
  String? _lastPayload;
  DateTime? _lastScanAt;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _handlePayload(String payload) async {
    if (_submitting) return;

    final now = DateTime.now();
    if (_lastPayload == payload &&
        _lastScanAt != null &&
        now.difference(_lastScanAt!).inSeconds < 3) {
      return;
    }

    setState(() {
      _submitting = true;
      _lastPayload = payload;
      _lastScanAt = now;
    });

    await _controller.stop();

    try {
      final json =
          await ServiceLocator.instance.registrationService.registerViaQr(payload);
      if (!mounted) return;

      ApiRegistration? registration;
      final data = json['data'];
      if (data is Map<String, dynamic>) {
        registration = ApiRegistration.fromJson(data);
      }

      final alreadyRegistered = json['already_registered'] == true;
      final message = (json['message'] ??
              (alreadyRegistered
                  ? 'You are already registered for this event.'
                  : 'Registration successful.'))
          .toString();

      final showQr = await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text(alreadyRegistered ? 'Already Registered' : 'Registration Complete'),
          content: Text(message),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Close'),
            ),
            if (registration?.qrPayload != null)
              FilledButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Show My QR'),
              ),
          ],
        ),
      );

      if (!mounted) return;

      if (showQr == true && registration?.qrPayload != null) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => QRDisplayScreen(
              eventId: registration!.eventId,
              qrPayload: registration.qrPayload!,
              expiresAt: registration.qrExpiresAt,
            ),
          ),
        );
        return;
      }

      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      final msg = e is ApiException ? e.message : e.toString();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg)),
      );
      await _controller.start();
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan Event Registration QR')),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: (capture) {
              final barcodes = capture.barcodes;
              if (barcodes.isEmpty) return;
              final raw = barcodes.first.rawValue;
              if (raw == null || raw.isEmpty) return;
              unawaited(_handlePayload(raw));
            },
          ),
          Positioned(
            left: 20,
            right: 20,
            top: 20,
            child: const StudentHeaderCard(
              kicker: 'QR Registration',
              title: 'Point your camera at the organizer QR.',
              subtitle:
                  'If you are already registered, the app will show your current registration state instead of creating a duplicate.',
            ),
          ),
          if (_submitting)
            Align(
              alignment: Alignment.bottomCenter,
              child: Container(
                margin: const EdgeInsets.all(20),
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.72),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    ),
                    SizedBox(width: 12),
                    Text(
                      'Checking registration...',
                      style: TextStyle(color: Colors.white),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
