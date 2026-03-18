import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QRScannerScreen extends StatefulWidget {
  const QRScannerScreen({super.key, required this.eventId});

  final int eventId;

  @override
  State<QRScannerScreen> createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  bool _submitting = false;
  String? _lastPayload;
  DateTime? _lastScanAt;

  Future<void> _handlePayload(String payload) async {
    if (_submitting) return;

    // Simple throttle to avoid rapid duplicates from the camera.
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

    try {
      final json = await ServiceLocator.instance.attendanceService.checkIn(
        eventId: widget.eventId,
        qrPayload: payload,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text((json['message'] ?? 'Check-in success').toString())),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString())),
      );
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan QR')),
      body: Stack(
        children: [
          MobileScanner(
            onDetect: (capture) {
              final barcodes = capture.barcodes;
              if (barcodes.isEmpty) return;
              final raw = barcodes.first.rawValue;
              if (raw == null || raw.isEmpty) return;
              unawaited(_handlePayload(raw));
            },
          ),
          if (_submitting)
            Align(
              alignment: Alignment.topCenter,
              child: Container(
                margin: const EdgeInsets.all(12),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.65),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      height: 16,
                      width: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                    SizedBox(width: 10),
                    Text('Submitting...', style: TextStyle(color: Colors.white)),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

