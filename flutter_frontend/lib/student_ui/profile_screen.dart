import 'package:flutter/material.dart';
import 'package:flutter_smart_event/api/models/api_user.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';
import 'package:flutter_smart_event/student_ui/student_widgets.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _loading = true;
  String? _error;
  ApiUser? _profile;

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
      final profile = await ServiceLocator.instance.authService.getMe();
      if (!mounted) return;
      setState(() => _profile = profile);
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
        title: const Text('My Profile'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _loading ? null : _load,
            icon: const Icon(Icons.refresh_rounded),
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
                kicker: 'Student Profile',
                title: _profile == null ? 'Your verified school account' : _profile!.name,
                subtitle: 'Profile details are loaded securely from the backend after login.',
                trailing: _profile == null
                    ? null
                    : Container(
                        height: 68,
                        width: 68,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.16),
                          borderRadius: BorderRadius.circular(22),
                        ),
                        alignment: Alignment.center,
                        child: Text(
                          _profile!.initials,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
              ),
              const SizedBox(height: 24),
              if (_loading)
                const Center(child: CircularProgressIndicator())
              else if (_error != null)
                StudentSurfaceCard(
                  child: Text(
                    _error!,
                    style: const TextStyle(
                      color: StudentPalette.danger,
                      height: 1.5,
                    ),
                  ),
                )
              else if (_profile != null)
                Column(
                  children: [
                    StudentSurfaceCard(
                      child: Column(
                        children: [
                          StudentLabelValue(
                            label: 'Full Name',
                            value: _profile!.name,
                            icon: Icons.person_outline_rounded,
                          ),
                          const SizedBox(height: 12),
                          StudentLabelValue(
                            label: 'Institutional Email',
                            value: _profile!.email,
                            icon: Icons.alternate_email_rounded,
                          ),
                          const SizedBox(height: 12),
                          StudentLabelValue(
                            label: 'Student ID',
                            value: _profile!.studentId.isEmpty ? 'Not available' : _profile!.studentId,
                            icon: Icons.badge_outlined,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    StudentSurfaceCard(
                      child: Row(
                        children: [
                          Expanded(
                            child: _ProfileMetric(
                              label: 'Account Type',
                              value: formatStatusLabel(_profile!.role),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _ProfileMetric(
                              label: 'Email Status',
                              value: _profile!.isInstitutional ? 'Verified' : 'Invalid',
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ProfileMetric extends StatelessWidget {
  const _ProfileMetric({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: StudentPalette.textSecondary,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            value,
            style: const TextStyle(
              color: StudentPalette.textPrimary,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}
