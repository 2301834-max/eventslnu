class ApiUser {
  ApiUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.studentId,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String? studentId;

  bool get isAdmin => role.toLowerCase() == 'admin';

  factory ApiUser.fromJson(Map<String, dynamic> json) {
    return ApiUser(
      id: (json['id'] as num).toInt(),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      role: (json['role'] ?? '').toString(),
      studentId: _optionalString(
        json['student_id'] ?? json['studentId'] ?? json['student_number'],
      ),
    );
  }

  static String? _optionalString(Object? value) {
    final text = value?.toString().trim();
    return text == null || text.isEmpty ? null : text;
  }
}
