import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_user.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/events_list_screen.dart';
import 'package:flutter_smart_event/student_ui/login_screen.dart';
import 'package:flutter_smart_event/student_ui/qr_register_scanner_screen.dart';
import 'package:flutter_smart_event/student_ui/registration_history_screen.dart';
import 'package:flutter_smart_event/student_ui/student_profile_screen.dart';
import 'package:flutter_smart_event/theme/app_theme.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _loading = true;
  String? _error;
  ApiUser? _user;
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
      final user = await ServiceLocator.instance.authService.getMe();
      final events = await ServiceLocator.instance.eventService.listEvents();
      if (!mounted) return;
      setState(() {
        _user = user;
        _events = _sortEvents(events);
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<ApiEvent> _sortEvents(List<ApiEvent> events) {
    final copy = [...events];
    copy.sort((a, b) {
      final ad = a.startDate ?? DateTime.fromMillisecondsSinceEpoch(0);
      final bd = b.startDate ?? DateTime.fromMillisecondsSinceEpoch(0);
      return ad.compareTo(bd);
    });
    return copy;
  }

  int get _upcomingCount {
    final now = DateTime.now();
    return _events.where((event) {
      final start = event.startDate;
      return start == null || !start.isBefore(now);
    }).length;
  }

  int get _todayCount {
    final now = DateTime.now();
    return _events.where((event) {
      final start = event.startDate?.toLocal();
      if (start == null) return false;
      return start.year == now.year &&
          start.month == now.month &&
          start.day == now.day;
    }).length;
  }

  String _firstName() {
    final name = _user?.name.trim();
    if (name == null || name.isEmpty) return 'student';
    final commaParts = name.split(',');
    if (commaParts.length > 1 && commaParts[1].trim().isNotEmpty) {
      return commaParts[1].trim().split(RegExp(r'\s+')).first;
    }
    return name.split(RegExp(r'\s+')).first;
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

  void _openEvents() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const EventsListScreen()),
    ).then((_) => _load());
  }

  void _openProfile() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const StudentProfileScreen()),
    ).then((_) => _load());
  }

  void _openQrScanner() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const QRRegisterScannerScreen()),
    ).then((_) => _load());
  }

  void _openHistory() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const RegistrationHistoryScreen()),
    ).then((_) => _load());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Portal'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _loading ? null : _load,
            icon: const Icon(Icons.refresh),
          ),
          IconButton(
            tooltip: 'Logout',
            onPressed: _logout,
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _load,
          child: _loading
              ? const _LoadingDashboard()
              : _error != null
              ? _StateDashboard(
                  icon: Icons.cloud_off,
                  title: 'Dashboard unavailable',
                  message: _error!,
                  onRetry: _load,
                )
              : ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(16),
                  children: [
                    _WelcomePanel(
                      name: _firstName(),
                      studentId: _user?.studentId,
                    ),
                    const SizedBox(height: 14),
                    _StatsGrid(
                      totalEvents: _events.length,
                      upcomingEvents: _upcomingCount,
                      todayEvents: _todayCount,
                    ),
                    const SizedBox(height: 18),
                    Text(
                      'Quick Actions',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 10),
                    _ActionGrid(
                      onBrowseEvents: _openEvents,
                      onScanQr: _openQrScanner,
                      onProfile: _openProfile,
                      onHistory: _openHistory,
                    ),
                    const SizedBox(height: 18),
                    const _CampusNotice(),
                  ],
                ),
        ),
      ),
    );
  }
}

class _WelcomePanel extends StatelessWidget {
  const _WelcomePanel({required this.name, required this.studentId});

  final String name;
  final String? studentId;

  @override
  Widget build(BuildContext context) {
    final id = studentId?.trim();

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppTheme.navy,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 46,
            width: 46,
            decoration: BoxDecoration(
              color: AppTheme.yellow.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.school, color: AppTheme.yellow, size: 28),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome, $name',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  id == null || id.isEmpty
                      ? 'Complete your profile before registering for activities.'
                      : 'Student ID $id',
                  style: const TextStyle(
                    color: Color(0xFFD8DFEA),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatsGrid extends StatelessWidget {
  const _StatsGrid({
    required this.totalEvents,
    required this.upcomingEvents,
    required this.todayEvents,
  });

  final int totalEvents;
  final int upcomingEvents;
  final int todayEvents;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final compact = constraints.maxWidth < 460;
        final cards = [
          _StatCard(
            icon: Icons.event_available,
            label: 'Available',
            value: totalEvents.toString(),
            color: AppTheme.navy,
          ),
          _StatCard(
            icon: Icons.upcoming,
            label: 'Upcoming',
            value: upcomingEvents.toString(),
            color: const Color(0xFF136F63),
          ),
          _StatCard(
            icon: Icons.today,
            label: 'Today',
            value: todayEvents.toString(),
            color: const Color(0xFF9A5B00),
          ),
        ];

        if (compact) {
          return Column(
            children: [
              for (var i = 0; i < cards.length; i++) ...[
                if (i > 0) const SizedBox(height: 10),
                cards[i],
              ],
            ],
          );
        }

        return Row(
          children: [
            for (var i = 0; i < cards.length; i++) ...[
              if (i > 0) const SizedBox(width: 10),
              Expanded(child: cards[i]),
            ],
          ],
        );
      },
    );
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  final IconData icon;
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              height: 42,
              width: 42,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    value,
                    style: const TextStyle(
                      color: AppTheme.ink,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(label, style: const TextStyle(color: AppTheme.muted)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionGrid extends StatelessWidget {
  const _ActionGrid({
    required this.onBrowseEvents,
    required this.onScanQr,
    required this.onProfile,
    required this.onHistory,
  });

  final VoidCallback onBrowseEvents;
  final VoidCallback onScanQr;
  final VoidCallback onProfile;
  final VoidCallback onHistory;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _DashboardTile(
          icon: Icons.event_available,
          title: 'Browse campus events',
          subtitle: 'View schedules, venues, and registration status.',
          accent: AppTheme.navy,
          onTap: onBrowseEvents,
        ),
        const SizedBox(height: 10),
        _DashboardTile(
          icon: Icons.qr_code_scanner,
          title: 'Scan event QR',
          subtitle: 'Register quickly from an event QR code.',
          accent: const Color(0xFF136F63),
          onTap: onScanQr,
        ),
        const SizedBox(height: 10),
        _DashboardTile(
          icon: Icons.badge_outlined,
          title: 'View student profile',
          subtitle: 'Check your name, email, and student ID.',
          accent: const Color(0xFF9A5B00),
          onTap: onProfile,
        ),
        const SizedBox(height: 10),
        _DashboardTile(
          icon: Icons.history,
          title: 'History',
          subtitle: 'Review registered events and attendance proof.',
          accent: const Color(0xFF4F46E5),
          onTap: onHistory,
        ),
      ],
    );
  }
}

class _DashboardTile extends StatelessWidget {
  const _DashboardTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.accent,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final Color accent;
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
                height: 52,
                width: 52,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: accent, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        color: AppTheme.muted,
                        height: 1.3,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              const Icon(Icons.chevron_right, color: AppTheme.muted),
            ],
          ),
        ),
      ),
    );
  }
}

class _CampusNotice extends StatelessWidget {
  const _CampusNotice();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.yellow.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppTheme.yellow.withValues(alpha: 0.4)),
      ),
      child: const Row(
        children: [
          Icon(Icons.info_outline, color: AppTheme.navy),
          SizedBox(width: 10),
          Expanded(
            child: Text(
              'Bring your school ID and keep your phone ready during event check-in.',
              style: TextStyle(
                color: AppTheme.ink,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _LoadingDashboard extends StatelessWidget {
  const _LoadingDashboard();

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: const [
        SizedBox(height: 180),
        Center(child: CircularProgressIndicator()),
      ],
    );
  }
}

class _StateDashboard extends StatelessWidget {
  const _StateDashboard({
    required this.icon,
    required this.title,
    required this.message,
    required this.onRetry,
  });

  final IconData icon;
  final String title;
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(20),
      children: [
        const SizedBox(height: 80),
        Icon(icon, size: 54, color: AppTheme.muted),
        const SizedBox(height: 14),
        Text(
          title,
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.titleLarge,
        ),
        const SizedBox(height: 8),
        Text(
          message,
          textAlign: TextAlign.center,
          style: const TextStyle(color: AppTheme.muted),
        ),
        const SizedBox(height: 20),
        Center(
          child: SizedBox(
            width: 160,
            child: OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ),
        ),
      ],
    );
  }
}
