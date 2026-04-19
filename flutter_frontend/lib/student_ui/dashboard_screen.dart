import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_user.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/events_list_screen.dart';
import 'package:flutter_smart_event/student_ui/login_screen.dart';
import 'package:flutter_smart_event/student_ui/profile_screen.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _loading = true;
  String? _error;
  ApiUser? _profile;
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
      final results = await Future.wait([
        ServiceLocator.instance.authService.getMe(),
        ServiceLocator.instance.eventService.listEvents(),
      ]);

      if (!mounted) return;
      setState(() {
        _profile = results[0] as ApiUser;
        _events = results[1] as List<ApiEvent>;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _logout() async {
    await ServiceLocator.instance.authService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final openEvents = _events.where((event) => event.isRegistrationOpen).length;
    final activeEvents = _events.where((event) {
      final status = event.status.toLowerCase();
      return status == 'published' || status == 'ongoing';
    }).length;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Portal'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _loading ? null : _load,
            icon: const Icon(Icons.refresh_rounded),
          ),
          IconButton(
            tooltip: 'Logout',
            onPressed: _logout,
            icon: const Icon(Icons.logout_rounded),
          ),
        ],
      ),
      body: StudentPageBackground(
        child: RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              StudentHeaderCard(
                kicker: _profile == null ? 'Student Portal' : timeGreeting(),
                title: _profile == null
                    ? 'Secure campus event access for LNU students.'
                    : '${timeGreeting()}, ${_profile!.name.split(' ').first}.',
                subtitle: _profile == null
                    ? 'Use your institutional account to browse school events, manage registrations, and open your event QR only when it is ready.'
                    : 'Your profile, registrations, and event passes are now tied to your LNU email and student ID for a more secure event flow.',
                trailing: _profile == null
                    ? null
                    : Wrap(
                        spacing: 10,
                        runSpacing: 10,
                        children: [
                          _HeroBadge(
                            icon: Icons.badge_outlined,
                            label: _profile!.studentId.isEmpty ? 'No Student ID' : _profile!.studentId,
                          ),
                          _HeroBadge(
                            icon: Icons.verified_user_outlined,
                            label: _profile!.isInstitutional ? 'LNU Verified' : 'Email Check Needed',
                          ),
                        ],
                      ),
              ),
              const SizedBox(height: 24),
              if (_error != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 18),
                  child: StudentSurfaceCard(
                    child: Text(
                      _error!,
                      style: const TextStyle(
                        color: StudentPalette.danger,
                        height: 1.5,
                      ),
                    ),
                  ),
                ),
              GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.2,
                children: [
                  _MetricCard(
                    label: 'Available Events',
                    value: _loading ? '...' : '${_events.length}',
                    accent: const Color(0xFFE5EEFF),
                    icon: Icons.calendar_month_rounded,
                  ),
                  _MetricCard(
                    label: 'Open for Registration',
                    value: _loading ? '...' : '$openEvents',
                    accent: const Color(0xFFFFF3D6),
                    icon: Icons.app_registration_rounded,
                  ),
                  _MetricCard(
                    label: 'Active School Events',
                    value: _loading ? '...' : '$activeEvents',
                    accent: const Color(0xFFE6F7ED),
                    icon: Icons.school_rounded,
                  ),
                ],
              ),
              const SizedBox(height: 24),
              const Text(
                'Quick Actions',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: StudentPalette.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              _DashboardTile(
                icon: Icons.event_available_rounded,
                title: 'Browse School Events',
                subtitle: 'Explore posters, schedules, venues, and register using your verified student details.',
                color: const Color(0xFFE0ECFF),
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const EventsListScreen()),
                  );
                },
              ),
              const SizedBox(height: 14),
              _DashboardTile(
                icon: Icons.person_rounded,
                title: 'My Profile',
                subtitle: 'Review your institutional email, student ID, and account details fetched from the secure backend.',
                color: const Color(0xFFFFF1D8),
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const ProfileScreen()),
                  );
                },
              ),
              const SizedBox(height: 22),
              StudentSurfaceCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text(
                      'QR Access Policy',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: StudentPalette.textPrimary,
                      ),
                    ),
                    SizedBox(height: 10),
                    Text(
                      'QR scanner shortcuts were removed from the dashboard. Students now open their event QR only from a registered event page after the backend confirms access.',
                      style: TextStyle(
                        color: StudentPalette.textSecondary,
                        height: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DashboardTile extends StatelessWidget {
  const _DashboardTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(28),
      onTap: onTap,
      child: StudentSurfaceCard(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 56,
              width: 56,
              decoration: BoxDecoration(
                color: color,
                borderRadius: BorderRadius.circular(18),
              ),
              child: Icon(icon, color: StudentPalette.primary),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                      color: StudentPalette.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      color: StudentPalette.textSecondary,
                      height: 1.45,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            const Icon(Icons.arrow_forward_ios_rounded, size: 18),
          ],
        ),
      ),
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({
    required this.label,
    required this.value,
    required this.accent,
    required this.icon,
  });

  final String label;
  final String value;
  final Color accent;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return StudentSurfaceCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 42,
            width: 42,
            decoration: BoxDecoration(
              color: accent,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: StudentPalette.primary),
          ),
          const SizedBox(height: 18),
          Text(
            value,
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w800,
              color: StudentPalette.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              color: StudentPalette.textSecondary,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroBadge extends StatelessWidget {
  const _HeroBadge({
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
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
