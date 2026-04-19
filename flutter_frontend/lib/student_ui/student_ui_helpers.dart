import 'package:flutter/material.dart';

class StudentPalette {
  static const Color primary = Color(0xFF0F3D91);
  static const Color secondary = Color(0xFF1D8BF1);
  static const Color accent = Color(0xFFF4B400);
  static const Color background = Color(0xFFF2F6FC);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF132238);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color success = Color(0xFF15803D);
  static const Color warning = Color(0xFFB45309);
  static const Color danger = Color(0xFFBE123C);

  static LinearGradient get heroGradient => const LinearGradient(
        colors: [Color(0xFF0B1F4D), Color(0xFF0F3D91), Color(0xFF1D8BF1)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );

  static LinearGradient get pageGradient => const LinearGradient(
        colors: [Color(0xFFF7FAFF), Color(0xFFE9F0FB), Color(0xFFF8FBFF)],
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
      );
}

InputDecoration studentInputDecoration({
  required String label,
  String? hint,
  IconData? icon,
  String? helper,
  Widget? suffixIcon,
}) {
  return InputDecoration(
    labelText: label,
    hintText: hint,
    helperText: helper,
    prefixIcon: icon == null ? null : Icon(icon),
    suffixIcon: suffixIcon,
  );
}

InputDecorationTheme studentInputTheme() {
  const borderColor = Color(0xFFD7E0EC);
  return InputDecorationTheme(
    filled: true,
    fillColor: Colors.white,
    contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
    helperStyle: const TextStyle(color: StudentPalette.textSecondary),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(22),
      borderSide: const BorderSide(color: borderColor),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(22),
      borderSide: const BorderSide(color: borderColor),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(22),
      borderSide: const BorderSide(color: StudentPalette.primary, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(22),
      borderSide: const BorderSide(color: StudentPalette.danger),
    ),
    focusedErrorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(22),
      borderSide: const BorderSide(color: StudentPalette.danger, width: 1.5),
    ),
  );
}

String formatEventDateTime(DateTime? value) {
  if (value == null) return 'TBA';

  final local = value.toLocal();
  const months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ];
  final month = months[local.month - 1];
  final day = local.day.toString().padLeft(2, '0');
  final year = local.year;
  final hour = local.hour == 0
      ? 12
      : local.hour > 12
          ? local.hour - 12
          : local.hour;
  final minute = local.minute.toString().padLeft(2, '0');
  final suffix = local.hour >= 12 ? 'PM' : 'AM';

  return '$month $day, $year $hour:$minute $suffix';
}

String formatCompactDateTime(DateTime? value) {
  if (value == null) return 'TBA';
  final local = value.toLocal();
  const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  return '${weekdays[local.weekday - 1]}, ${formatEventDateTime(local)}';
}

String formatStatusLabel(String value) {
  if (value.isEmpty) return 'Unknown';
  return value[0].toUpperCase() + value.substring(1).toLowerCase();
}

Color statusTextColor(String status) {
  switch (status.toLowerCase()) {
    case 'approved':
    case 'ongoing':
    case 'published':
      return StudentPalette.success;
    case 'pending':
    case 'completed':
      return StudentPalette.warning;
    case 'rejected':
    case 'cancelled':
      return StudentPalette.danger;
    default:
      return StudentPalette.primary;
  }
}

Color statusFillColor(String status) {
  switch (status.toLowerCase()) {
    case 'approved':
    case 'ongoing':
    case 'published':
      return const Color(0xFFDCFCE7);
    case 'pending':
    case 'completed':
      return const Color(0xFFFEF3C7);
    case 'rejected':
    case 'cancelled':
      return const Color(0xFFFFE4E6);
    default:
      return const Color(0xFFDBEAFE);
  }
}

BoxDecoration glassCardDecoration() {
  return BoxDecoration(
    color: Colors.white.withValues(alpha: 0.18),
    borderRadius: BorderRadius.circular(28),
    border: Border.all(color: Colors.white.withValues(alpha: 0.22)),
  );
}

String normalizeInstitutionalEmail(String value) => value.trim().toLowerCase();

String normalizeStudentId(String value) => value.trim().toUpperCase();

String? validateInstitutionalEmail(String? value) {
  final email = (value ?? '').trim();
  if (email.isEmpty) {
    return 'Institutional email is required';
  }

  final emailPattern = RegExp(r'^[A-Z0-9._%+-]+@lnu\.edu\.ph$', caseSensitive: false);
  if (!emailPattern.hasMatch(email)) {
    return 'Use your @lnu.edu.ph email only';
  }

  return null;
}

String? validateStudentId(String? value) {
  final studentId = (value ?? '').trim();
  if (studentId.isEmpty) {
    return 'Student ID is required';
  }

  final studentIdPattern = RegExp(r'^[A-Z0-9-]+$', caseSensitive: false);
  if (!studentIdPattern.hasMatch(studentId)) {
    return 'Use letters, numbers, and hyphens only';
  }

  return null;
}

String formatDateRange(DateTime? start, DateTime? end) {
  if (start == null && end == null) return 'Schedule to be announced';
  if (start != null && end == null) return formatEventDateTime(start);
  if (start == null && end != null) return formatEventDateTime(end);
  return '${formatEventDateTime(start)} - ${formatEventDateTime(end)}';
}

String timeGreeting() {
  final hour = DateTime.now().hour;
  if (hour < 12) return 'Good morning';
  if (hour < 18) return 'Good afternoon';
  return 'Good evening';
}
