import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/qr_scanner_screen.dart';

/// Lets the user pick an event, then opens the QR scanner for that event.
class ScanEventSelectorScreen extends StatefulWidget {
  const ScanEventSelectorScreen({super.key});

  @override
  State<ScanEventSelectorScreen> createState() => _ScanEventSelectorScreenState();
}

class _ScanEventSelectorScreenState extends State<ScanEventSelectorScreen> {
  bool _loading = true;
  String? _error;
  List<ApiEvent> _events = const [];

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
      final events = await ServiceLocator.instance.eventService.listEvents();
      if (!mounted) return;
      setState(() => _events = events);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Select Event to Scan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loading ? null : _load,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      const SizedBox(height: 24),
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(_error!, style: const TextStyle(color: Colors.red)),
                      ),
                    ],
                  )
                : _events.isEmpty
                    ? ListView(
                        children: const [
                          SizedBox(height: 24),
                          Center(child: Text('No events available to scan.')),
                        ],
                      )
                    : ListView.separated(
                        itemCount: _events.length,
                        separatorBuilder: (_, __) => const Divider(height: 1),
                        itemBuilder: (context, index) {
                          final e = _events[index];
                          return ListTile(
                            title: Text(e.title),
                            subtitle: e.location.isEmpty ? null : Text(e.location),
                            trailing: const Icon(Icons.qr_code_scanner),
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => QRScannerScreen(eventId: e.id),
                                ),
                              );
                            },
                          );
                        },
                      ),
      ),
    );
  }
}

