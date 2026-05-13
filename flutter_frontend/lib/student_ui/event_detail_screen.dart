import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/qr_display_screen.dart';
import 'package:flutter_smart_event/theme/app_theme.dart';

class EventDetailScreen extends StatefulWidget {
  const EventDetailScreen({super.key, required this.event});

  final ApiEvent event;

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  bool _loading = true;
  String? _error;
  ApiRegistration? _myReg;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final reg = await ServiceLocator.instance.registrationService
          .getMyRegistration(widget.event.id);
      if (!mounted) return;
      setState(() => _myReg = reg);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _register() async {
    try {
      final reg = await ServiceLocator.instance.registrationService
          .registerForEvent(widget.event.id);
      await _load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            (reg.status.toLowerCase() == 'approved')
                ? 'Registration successful.'
                : 'Registration successful. Waiting for approval.',
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e is ApiException ? e.message : e.toString())),
      );
    }
  }

  String _dateLabel(DateTime? value) {
    if (value == null) return 'To be announced';
    final local = value.toLocal();
    final month = local.month.toString().padLeft(2, '0');
    final day = local.day.toString().padLeft(2, '0');
    final hour = local.hour.toString().padLeft(2, '0');
    final minute = local.minute.toString().padLeft(2, '0');
    return '${local.year}-$month-$day $hour:$minute';
  }

  @override
  Widget build(BuildContext context) {
    final event = widget.event;

    return Scaffold(
      appBar: AppBar(title: const Text('Event Details')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _HeroPanel(event: event),
          const SizedBox(height: 14),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Schedule', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 12),
                  _DetailRow(icon: Icons.play_arrow, label: 'Start', value: _dateLabel(event.startDate)),
                  const SizedBox(height: 10),
                  _DetailRow(icon: Icons.flag_outlined, label: 'End', value: _dateLabel(event.endDate)),
                  if (event.location.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    _DetailRow(
                      icon: Icons.location_on_outlined,
                      label: 'Venue',
                      value: event.location,
                    ),
                  ],
                ],
              ),
            ),
          ),
          if (event.description.isNotEmpty) ...[
            const SizedBox(height: 14),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('About this event', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 10),
                    Text(
                      event.description,
                      style: const TextStyle(height: 1.45, color: AppTheme.ink),
                    ),
                  ],
                ),
              ),
            ),
          ],
          const SizedBox(height: 14),
          _RegistrationPanel(
            loading: _loading,
            error: _error,
            registration: _myReg,
            onRegister: _register,
            onShowQr: (_myReg?.qrPayload == null)
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
          ),
        ],
      ),
    );
  }
}

class _HeroPanel extends StatelessWidget {
  const _HeroPanel({required this.event});

  final ApiEvent event;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppTheme.navy,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: AppTheme.yellow.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              (event.status.isEmpty ? 'POSTED' : event.status.toUpperCase()),
              style: const TextStyle(
                color: AppTheme.yellow,
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 14),
          Text(
            event.title,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 25,
              height: 1.12,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, color: AppTheme.navy, size: 20),
        const SizedBox(width: 10),
        SizedBox(
          width: 58,
          child: Text(label, style: const TextStyle(color: AppTheme.muted)),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
      ],
    );
  }
}

class _RegistrationPanel extends StatelessWidget {
  const _RegistrationPanel({
    required this.loading,
    required this.error,
    required this.registration,
    required this.onRegister,
    required this.onShowQr,
  });

  final bool loading;
  final String? error;
  final ApiRegistration? registration;
  final VoidCallback onRegister;
  final VoidCallback? onShowQr;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                const Icon(Icons.assignment_turned_in_outlined, color: AppTheme.navy),
                const SizedBox(width: 10),
                Text('My Registration', style: Theme.of(context).textTheme.titleMedium),
              ],
            ),
            if (loading) ...[
              const SizedBox(height: 14),
              const LinearProgressIndicator(),
            ],
            if (error != null) ...[
              const SizedBox(height: 12),
              Text(error!, style: const TextStyle(color: AppTheme.navy)),
            ],
            const SizedBox(height: 14),
            if (registration == null)
              ElevatedButton.icon(
                onPressed: loading ? null : onRegister,
                icon: const Icon(Icons.how_to_reg),
                label: const Text('Register for this event'),
              )
            else ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.navy.withValues(alpha: 0.06),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.verified, color: AppTheme.navy),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Status: ${registration!.status}',
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              ElevatedButton.icon(
                onPressed: onShowQr,
                icon: const Icon(Icons.qr_code_2),
                label: const Text('Show my QR code'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
