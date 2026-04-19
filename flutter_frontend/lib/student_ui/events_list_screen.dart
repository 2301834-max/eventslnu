import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/event_detail_screen.dart';
import 'package:flutter_smart_event/student_ui/profile_screen.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';

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
        title: const Text('School Events'),
        actions: [
          IconButton(
            onPressed: _loading ? null : _load,
            icon: const Icon(Icons.refresh_rounded),
          ),
          IconButton(
            tooltip: 'Profile',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ProfileScreen()),
              );
            },
            icon: const Icon(Icons.person_outline_rounded),
          ),
        ],
      ),
      body: StudentPageBackground(
        child: RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              const StudentHeaderCard(
                kicker: 'Campus Calendar',
                title: 'Explore upcoming and active LNU events.',
                subtitle:
                    'Each event now includes poster support, cleaner cards, and secure registration rules tied to your institutional account.',
              ),
              const SizedBox(height: 24),
              AnimatedSwitcher(
                duration: const Duration(milliseconds: 250),
                child: _loading
                    ? const Padding(
                        key: ValueKey('loading'),
                        padding: EdgeInsets.only(top: 40),
                        child: Center(child: CircularProgressIndicator()),
                      )
                    : _error != null
                        ? _StateCard(
                            key: const ValueKey('error'),
                            title: 'Could not load events',
                            subtitle: _error!,
                            accent: StudentPalette.danger,
                          )
                        : _events.isEmpty
                            ? const _StateCard(
                                key: ValueKey('empty'),
                                title: 'No events available',
                                subtitle: 'There are no school events to show right now. Try refreshing again later.',
                                accent: StudentPalette.warning,
                              )
                            : Column(
                                key: const ValueKey('list'),
                                children: _events
                                    .map(
                                      (event) => Padding(
                                        padding: const EdgeInsets.only(bottom: 16),
                                        child: _EventCard(event: event),
                                      ),
                                    )
                                    .toList(),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _EventCard extends StatelessWidget {
  const _EventCard({required this.event});

  final ApiEvent event;

  @override
  Widget build(BuildContext context) {
    final status = formatStatusLabel(event.status);

    return InkWell(
      borderRadius: BorderRadius.circular(28),
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => EventDetailScreen(event: event)),
        );
      },
      child: StudentSurfaceCard(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            EventPoster(
              title: event.title,
              imageUrl: event.eventImageUrl,
              height: 180,
            ),
            const SizedBox(height: 16),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    event.title,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: StudentPalette.textPrimary,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: statusFillColor(event.status),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    status,
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: statusTextColor(event.status),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              formatDateRange(event.startDate, event.endDate),
              style: const TextStyle(
                fontWeight: FontWeight.w600,
                color: StudentPalette.textPrimary,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              event.location.isEmpty ? 'Location to be announced' : event.location,
              style: const TextStyle(color: StudentPalette.textSecondary),
            ),
            if (event.description.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                event.description,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  height: 1.5,
                  color: StudentPalette.textSecondary,
                ),
              ),
            ],
            const SizedBox(height: 16),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: event.isRegistrationOpen
                        ? const Color(0xFFE6F7ED)
                        : const Color(0xFFFFF4E5),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    event.isRegistrationOpen ? 'Registration Open' : 'Registration Closed',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: event.isRegistrationOpen
                          ? StudentPalette.success
                          : StudentPalette.warning,
                    ),
                  ),
                ),
                const Spacer(),
                const Text(
                  'Open details',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    color: StudentPalette.primary,
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(
                  Icons.arrow_forward_rounded,
                  color: StudentPalette.primary,
                  size: 18,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _StateCard extends StatelessWidget {
  const _StateCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.accent,
  });

  final String title;
  final String subtitle;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return StudentSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: accent,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            subtitle,
            style: const TextStyle(
              color: StudentPalette.textSecondary,
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }
}
