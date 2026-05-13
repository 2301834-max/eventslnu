import 'package:flutter/material.dart';
import 'package:flutter_smart_event/admin_ui/create_event.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/theme/app_theme.dart';

class AdminDashboard extends StatefulWidget {
  const AdminDashboard({super.key});

  @override
  State<AdminDashboard> createState() => _AdminDashboardState();
}

class _AdminDashboardState extends State<AdminDashboard> {
  int _totalEvents = 0;

  @override
  void initState() {
    super.initState();
    _refreshEventCount();
  }

  Future<void> _refreshEventCount() async {
    final events = await ServiceLocator.instance.eventService.getUpcomingEvents();
    if (!mounted) return;
    setState(() => _totalEvents = events.length);
  }

  Future<void> _openCreateEvent() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CreateEvent()),
    );

    if (!mounted) {
      return;
    }

    await _refreshEventCount();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Event Administration')),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 760),
          child: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppTheme.navy,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Container(
                      height: 58,
                      width: 58,
                      decoration: BoxDecoration(
                        color: AppTheme.yellow,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.admin_panel_settings, color: AppTheme.navy),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'LNU Event Office',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '$_totalEvents upcoming ${_totalEvents == 1 ? 'event' : 'events'} scheduled',
                            style: const TextStyle(color: Color(0xFFD8DFEA)),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                icon: const Icon(Icons.add),
                label: const Text('Create Event'),
                onPressed: _openCreateEvent,
              ),
              const SizedBox(height: 16),
              _AdminAction(
                icon: Icons.event_note,
                title: 'Manage Events',
                subtitle: 'Review schedules, venues, and event details.',
                onTap: _refreshEventCount,
              ),
              const SizedBox(height: 12),
              _AdminAction(
                icon: Icons.qr_code_scanner,
                title: 'Scan Attendance',
                subtitle: 'Validate attendee QR codes during check-in.',
                onTap: () {},
              ),
              const SizedBox(height: 12),
              _AdminAction(
                icon: Icons.bar_chart,
                title: 'Reports',
                subtitle: 'Track registrations and attendance performance.',
                onTap: () {},
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AdminAction extends StatelessWidget {
  const _AdminAction({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                height: 48,
                width: 48,
                decoration: BoxDecoration(
                  color: AppTheme.yellow.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: AppTheme.navy),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 4),
                    Text(subtitle, style: const TextStyle(color: AppTheme.muted)),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: AppTheme.muted),
            ],
          ),
        ),
      ),
    );
  }
}
