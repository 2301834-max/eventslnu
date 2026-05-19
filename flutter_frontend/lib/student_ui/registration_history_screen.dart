import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';
import 'package:flutter_smart_event/api/models/api_registration.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/event_detail_screen.dart';
import 'package:flutter_smart_event/theme/app_theme.dart';

class RegistrationHistoryScreen extends StatefulWidget {
  const RegistrationHistoryScreen({super.key});

  @override
  State<RegistrationHistoryScreen> createState() =>
      _RegistrationHistoryScreenState();
}

class _RegistrationHistoryScreenState extends State<RegistrationHistoryScreen> {
  bool _loading = true;
  String? _error;
  List<ApiRegistration> _registrations = const [];

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
      final registrations = await ServiceLocator.instance.registrationService
          .listMyRegistrations();
      if (!mounted) return;
      setState(() => _registrations = registrations);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _dateLabel(DateTime? value) {
    if (value == null) return 'Date unavailable';
    final local = value.toLocal();
    final month = local.month.toString().padLeft(2, '0');
    final day = local.day.toString().padLeft(2, '0');
    final hour = local.hour.toString().padLeft(2, '0');
    final minute = local.minute.toString().padLeft(2, '0');
    return '${local.year}-$month-$day $hour:$minute';
  }

  void _openEvent(ApiEvent event) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => EventDetailScreen(event: event)),
    ).then((_) => _load());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Registration History')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const _HistoryLoading()
            : _error != null
            ? _HistoryState(
                icon: Icons.cloud_off,
                title: 'History unavailable',
                message: _error!,
                actionLabel: 'Retry',
                onAction: _load,
              )
            : _registrations.isEmpty
            ? const _HistoryState(
                icon: Icons.history,
                title: 'No registered events yet',
                message:
                    'Your event registrations will appear here after you register.',
              )
            : ListView.separated(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                itemCount: _registrations.length + 1,
                separatorBuilder: (context, index) =>
                    const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return _HistoryHeader(count: _registrations.length);
                  }

                  final registration = _registrations[index - 1];
                  return _HistoryCard(
                    registration: registration,
                    dateLabel: _dateLabel(registration.event?.startDate),
                    proofDateLabel: _dateLabel,
                    registeredLabel: _dateLabel(registration.createdAt),
                    onTap: registration.event == null
                        ? null
                        : () => _openEvent(registration.event!),
                  );
                },
              ),
      ),
    );
  }
}

class _HistoryHeader extends StatelessWidget {
  const _HistoryHeader({required this.count});

  final int count;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppTheme.navy,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            height: 48,
            width: 48,
            decoration: BoxDecoration(
              color: AppTheme.yellow.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.history, color: AppTheme.yellow, size: 28),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$count registered ${count == 1 ? 'event' : 'events'}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Use this page as a transparent record of event attendance.',
                  style: TextStyle(color: Color(0xFFD8DFEA), height: 1.35),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HistoryCard extends StatelessWidget {
  const _HistoryCard({
    required this.registration,
    required this.dateLabel,
    required this.proofDateLabel,
    required this.registeredLabel,
    required this.onTap,
  });

  final ApiRegistration registration;
  final String dateLabel;
  final String Function(DateTime? value) proofDateLabel;
  final String registeredLabel;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final event = registration.event;
    final title = event?.title.trim().isNotEmpty == true
        ? event!.title
        : 'Event #${registration.eventId}';
    final location = event?.location.trim() ?? '';

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Text(
                      title,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ),
                  const SizedBox(width: 12),
                  _StatusPill(status: registration.status),
                ],
              ),
              const SizedBox(height: 12),
              _AttendanceProof(
                registration: registration,
                dateLabel: proofDateLabel,
              ),
              const SizedBox(height: 12),
              _MetaRow(icon: Icons.schedule, label: dateLabel),
              if (location.isNotEmpty) ...[
                const SizedBox(height: 8),
                _MetaRow(icon: Icons.location_on_outlined, label: location),
              ],
              const SizedBox(height: 8),
              _MetaRow(
                icon: Icons.assignment_turned_in_outlined,
                label: 'Registered $registeredLabel',
              ),
              if (registration.registrationNumber?.isNotEmpty == true) ...[
                const SizedBox(height: 8),
                _MetaRow(
                  icon: Icons.confirmation_number_outlined,
                  label: registration.registrationNumber!,
                ),
              ],
              if (onTap != null) ...[
                const SizedBox(height: 12),
                const Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      'View event',
                      style: TextStyle(
                        color: AppTheme.navy,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    SizedBox(width: 4),
                    Icon(Icons.chevron_right, color: AppTheme.navy),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _AttendanceProof extends StatelessWidget {
  const _AttendanceProof({required this.registration, required this.dateLabel});

  final ApiRegistration registration;
  final String Function(DateTime? value) dateLabel;

  @override
  Widget build(BuildContext context) {
    final attendance = registration.attendance;
    final hasProof = attendance?.checkedInAt != null;
    final color = hasProof ? const Color(0xFF136F63) : const Color(0xFF9A5B00);
    final title = hasProof ? 'Attended' : 'No attendance record yet';
    final message = hasProof
        ? 'Checked in ${dateLabel(attendance!.checkedInAt)}'
        : 'This event is registered, but no check-in has been recorded.';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.18)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                hasProof ? Icons.verified_outlined : Icons.info_outline,
                color: color,
                size: 20,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(color: color, fontWeight: FontWeight.w800),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(message, style: const TextStyle(color: AppTheme.ink)),
          if (hasProof && attendance?.location?.trim().isNotEmpty == true) ...[
            const SizedBox(height: 6),
            Text(
              'Location: ${attendance!.location}',
              style: const TextStyle(color: AppTheme.muted),
            ),
          ],
          if (hasProof && attendance?.checkedOutAt != null) ...[
            const SizedBox(height: 6),
            Text(
              'Checked out ${dateLabel(attendance!.checkedOutAt)}',
              style: const TextStyle(color: AppTheme.muted),
            ),
          ],
        ],
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    final color = switch (normalized) {
      'approved' => const Color(0xFF136F63),
      'rejected' => const Color(0xFFB42318),
      'cancelled' => const Color(0xFF697386),
      _ => const Color(0xFF9A5B00),
    };
    final label = normalized.isEmpty
        ? 'Pending'
        : '${normalized[0].toUpperCase()}${normalized.substring(1)}';

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _MetaRow extends StatelessWidget {
  const _MetaRow({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: AppTheme.muted),
        const SizedBox(width: 8),
        Expanded(
          child: Text(label, style: const TextStyle(color: AppTheme.muted)),
        ),
      ],
    );
  }
}

class _HistoryLoading extends StatelessWidget {
  const _HistoryLoading();

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

class _HistoryState extends StatelessWidget {
  const _HistoryState({
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(24),
      children: [
        const SizedBox(height: 120),
        Icon(icon, size: 56, color: AppTheme.muted),
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
          style: const TextStyle(color: AppTheme.muted, height: 1.4),
        ),
        if (actionLabel != null && onAction != null) ...[
          const SizedBox(height: 20),
          Center(
            child: SizedBox(
              width: 160,
              child: OutlinedButton.icon(
                onPressed: onAction,
                icon: const Icon(Icons.refresh),
                label: Text(actionLabel!),
              ),
            ),
          ),
        ],
      ],
    );
  }
}
