import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/qr_display_screen.dart';

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
            // API always returns a message; use it if present.
            (reg.status.toLowerCase() == 'approved')
                ? 'Registration successful.'
                : 'Registration successful. Waiting for approval.',
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            e is ApiException ? e.message : e.toString(),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.event;

    return Scaffold(
      appBar: AppBar(title: Text(e.title)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(e.title, style: Theme.of(context).textTheme.headlineSmall),
          const SizedBox(height: 8),
          if (e.location.isNotEmpty) Text('Location: ${e.location}'),
          if (e.status.isNotEmpty) Text('Status: ${e.status}'),
          if (e.startDate != null) Text('Start: ${e.startDate!.toLocal()}'),
          if (e.endDate != null) Text('End: ${e.endDate!.toLocal()}'),
          if (e.description.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(e.description),
          ],
          const SizedBox(height: 16),
          if (_loading) const LinearProgressIndicator(),
          if (_error != null) ...[
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: Colors.red)),
          ],
          const SizedBox(height: 12),
          if (_myReg == null) ...[
            ElevatedButton(
              onPressed: _register,
              child: const Text('Register for this event'),
            ),
          ] else ...[
            Text('My registration: ${_myReg!.status}'),
            const SizedBox(height: 8),
            ElevatedButton(
              onPressed: (_myReg!.qrPayload == null)
                  ? null
                  : () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => QRDisplayScreen(
                            eventId: e.id,
                            qrPayload: _myReg!.qrPayload!,
                            expiresAt: _myReg!.qrExpiresAt,
                          ),
                        ),
                      );
                    },
              child: const Text('Show my QR code'),
            ),
          ],
        ],
      ),
    );
  }
}

