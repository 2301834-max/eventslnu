import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/event_detail_screen.dart';
import 'package:flutter_smart_event/student_ui/login_screen.dart';

class EventsListScreen extends StatefulWidget {
  const EventsListScreen({super.key});

  @override
  State<EventsListScreen> createState() => _EventsListScreenState();
}

class _EventsListScreenState extends State<EventsListScreen> {
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
      setState(() {
        _events = events;
      });
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
        title: const Text('Events'),
        actions: [
          IconButton(
            tooltip: 'Logout',
            onPressed: () async {
              await ServiceLocator.instance.authService.logout();
              if (!context.mounted) return;
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            icon: const Icon(Icons.logout),
          ),
          IconButton(
            onPressed: _loading ? null : _load,
            icon: const Icon(Icons.refresh),
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
                          Center(child: Text('No events found.')),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(12),
                        itemCount: _events.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (context, index) {
                          final e = _events[index];
                          final subtitle = [
                            if (e.startDate != null) e.startDate!.toLocal().toString(),
                            if (e.location.isNotEmpty) e.location,
                          ].where((x) => x.isNotEmpty).join(' • ');

                          return Card(
                            child: ListTile(
                              title: Text(e.title),
                              subtitle: subtitle.isEmpty ? null : Text(subtitle),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => EventDetailScreen(event: e),
                                  ),
                                );
                              },
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}

