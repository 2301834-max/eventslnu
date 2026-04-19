class ApiUser {
  ApiUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.studentId,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String studentId;

  bool get isAdmin => role.toLowerCase() == 'admin';
  bool get isInstitutional => email.toLowerCase().endsWith('@lnu.edu.ph');
  String get initials {
    final parts = name.trim().split(RegExp(r'\s+')).where((part) => part.isNotEmpty).toList();
    if (parts.isEmpty) return 'LNU';
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return (parts.first.substring(0, 1) + parts.last.substring(0, 1)).toUpperCase();
  }

  factory ApiUser.fromJson(Map<String, dynamic> json) {
    return ApiUser(
      id: (json['id'] as num).toInt(),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      role: (json['role'] ?? '').toString(),
      studentId: (json['student_id'] ?? '').toString(),
    );
  }
}
