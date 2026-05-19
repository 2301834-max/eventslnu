enum UserRole { student, admin }

abstract class AppUser {
  AppUser({
    required this.email,
    required this.username,
    required String password,
    this.studentId,
  }) : _password = password;

  final String email;
  final String username;
  final String? studentId;
  final String _password;

  UserRole get role;

  bool validatePassword(String password) => _password == password;
}

class StudentUser extends AppUser {
  StudentUser({
    required super.email,
    required super.username,
    required super.password,
    super.studentId,
  });

  @override
  UserRole get role => UserRole.student;
}

class AdminUser extends AppUser {
  AdminUser({
    required super.email,
    required super.username,
    required super.password,
    super.studentId,
  });

  @override
  UserRole get role => UserRole.admin;
}
