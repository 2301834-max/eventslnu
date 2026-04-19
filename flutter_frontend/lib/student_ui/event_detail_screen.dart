import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/api/models/api_user.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/qr_display_screen.dart';
import 'package:flutter_smart_event/student_ui/qr_register_scanner_screen.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';

class EventDetailScreen extends StatefulWidget {
  const EventDetailScreen({super.key, required this.event});

  final ApiEvent event;

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  final _registerFormKey = GlobalKey<FormState>();
  final TextEditingController _studentIdController = TextEditingController();
  bool _loading = true;
  bool _registering = false;
  String? _error;
  ApiRegistration? _myReg;
  ApiUser? _profile;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _studentIdController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final results = await Future.wait([
        ServiceLocator.instance.registrationService.getMyRegistration(widget.event.id),
        ServiceLocator.instance.authService.getMe(),
      ]);

      if (!mounted) return;
      final profile = results[1] as ApiUser;
      setState(() {
        _myReg = results[0] as ApiRegistration?;
        _profile = profile;
        if (_studentIdController.text.isEmpty) {
          _studentIdController.text = profile.studentId;
        }
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _register() async {
    if (!_registerFormKey.currentState!.validate()) {
      return;
    }

    setState(() => _registering = true);
    try {
      final reg = await ServiceLocator.instance.registrationService.registerForEvent(
        widget.event.id,
        studentId: normalizeStudentId(_studentIdController.text),
      );
      await _load();
      if (!mounted) return;
      final message = reg.isApproved
          ? 'Registration successful. Your event QR is ready.'
          : 'Registration submitted. Please wait for approval.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    } catch (e) {
      if (!mounted) return;
      final message = e is ApiException ? e.message : e.toString();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    } finally {
      if (mounted) setState(() => _registering = false);
    }
  }

  Future<void> _openRegistrationScanner() async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => const QRRegisterScannerScreen(),
      ),
    );

    if (!mounted) return;
    await _load();
  }

  bool get _isEventClosed {
    if (!widget.event.isRegistrationOpen) {
      return true;
    }

    final status = widget.event.status.toLowerCase();
    if (status == 'cancelled' || status == 'completed') {
      return true;
    }

    final end = widget.event.endDate;
    return end != null && end.isBefore(DateTime.now());
  }

  @override
  Widget build(BuildContext context) {
    final event = widget.event;

    return Scaffold(
      appBar: AppBar(title: const Text('Event Details')),
      body: StudentPageBackground(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            EventPoster(
              title: event.title,
              imageUrl: event.eventImageUrl,
              height: 220,
            ),
            const SizedBox(height: 18),
            StudentHeaderCard(
              kicker: formatStatusLabel(event.status),
              title: event.title,
              subtitle: event.description.isEmpty
                  ? 'Review the event schedule, venue, and your secure registration access.'
                  : event.description,
              trailing: Wrap(
                spacing: 10,
                runSpacing: 10,
                children: [
                  _InfoChip(
                    icon: Icons.schedule_rounded,
                    label: formatCompactDateTime(event.startDate),
                  ),
                  _InfoChip(
                    icon: Icons.location_on_outlined,
                    label: event.location.isEmpty
                        ? 'Location to be announced'
                        : event.location,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            StudentSurfaceCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Registration',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                      color: StudentPalette.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 14),
                  if (_loading)
                    const LinearProgressIndicator()
                  else if (_error != null)
                    Text(
                      _error!,
                      style: const TextStyle(color: StudentPalette.danger),
                    )
                  else if (_myReg == null)
                    Form(
                      key: _registerFormKey,
                      autovalidateMode: AutovalidateMode.onUserInteraction,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          StudentLabelValue(
                            label: 'Institutional Email',
                            value: _profile?.email ?? 'Not available',
                            icon: Icons.alternate_email_rounded,
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _studentIdController,
                            onChanged: (_) => setState(() {}),
                            decoration: studentInputDecoration(
                              label: 'Student ID',
                              hint: '2026-0001',
                              helper: 'This must match the student ID saved on your account.',
                              icon: Icons.badge_outlined,
                            ),
                            validator: validateStudentId,
                          ),
                          const SizedBox(height: 14),
                          Text(
                            _isEventClosed
                                ? 'This event is no longer open for new registrations.'
                                : 'Use your student ID below or scan the organizer QR to get approved instantly.',
                            style: const TextStyle(
                              color: StudentPalette.textSecondary,
                              height: 1.45,
                            ),
                          ),
                          const SizedBox(height: 16),
                          Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: [
                              FilledButton(
                                onPressed: _isEventClosed || _registering ? null : _register,
                                child: _registering
                                    ? const SizedBox(
                                        height: 18,
                                        width: 18,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.white,
                                        ),
                                      )
                                    : const Text('Register for this event'),
                              ),
                              OutlinedButton.icon(
                                onPressed: _isEventClosed ? null : _openRegistrationScanner,
                                icon: const Icon(Icons.qr_code_scanner_rounded),
                                label: const Text('Scan event QR'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    )
                  else
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 14,
                            vertical: 10,
                          ),
                          decoration: BoxDecoration(
                            color: statusFillColor(_myReg!.status),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            'My registration: ${formatStatusLabel(_myReg!.status)}',
                            style: TextStyle(
                              color: statusTextColor(_myReg!.status),
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),
                        StudentLabelValue(
                          label: 'Student ID',
                          value: _profile?.studentId.isEmpty ?? true
                              ? 'Not available'
                              : _profile!.studentId,
                          icon: Icons.badge_outlined,
                        ),
                        if (_myReg!.registrationNumber != null) ...[
                          const SizedBox(height: 12),
                          StudentLabelValue(
                            label: 'Registration Number',
                            value: _myReg!.registrationNumber!,
                            icon: Icons.confirmation_number_outlined,
                          ),
                        ],
                        const SizedBox(height: 16),
                        OutlinedButton.icon(
                          onPressed: (_myReg!.qrPayload == null)
                              ? null
                              : () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => QRDisplayScreen(
                                        eventId: event.id,
                                        qrPayload: _myReg!.qrPayload!,
                                        expiresAt: _myReg!.qrExpiresAt,
                                      ),
                                    ),
                                  );
                                },
                          icon: const Icon(Icons.qr_code_2_rounded),
                          label: Text(
                            _myReg!.qrPayload == null
                                ? 'Scan organizer QR to activate my pass'
                                : 'Open my event QR',
                          ),
                        ),
                        if (_myReg!.qrPayload == null) ...[
                          const SizedBox(height: 12),
                          OutlinedButton.icon(
                            onPressed: _isEventClosed ? null : _openRegistrationScanner,
                            icon: const Icon(Icons.camera_alt_rounded),
                            label: const Text('Scan event QR now'),
                          ),
                        ],
                        const SizedBox(height: 12),
                        Text(
                          _myReg!.qrPayload == null
                              ? 'If this registration is still pending from the old flow, scanning the organizer QR will approve it instantly.'
                              : 'QR access is restricted to registered students for this event only. Generic scanner access was removed from the dashboard.',
                          style: const TextStyle(
                            color: StudentPalette.textSecondary,
                            height: 1.45,
                          ),
                        ),
                      ],
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({
    required this.icon,
    required this.label,
  });

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: glassCardDecoration(),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white, size: 18),
          const SizedBox(width: 8),
          Flexible(
            child: Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
