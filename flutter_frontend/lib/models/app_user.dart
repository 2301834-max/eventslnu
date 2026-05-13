enum UserRole { student, admin }

abstract class AppUser {
  AppUser({required this.email, required this.username, required String password})
      : _password = password;

  final String email;
  final String username;
  final String _password;

  UserRole get role;

  bool validatePassword(String password) => _password == password;
}

class StudentUser extends AppUser {
  StudentUser({
    required super.email,
    required super.username,
    required super.password,
  });

  @override
  UserRole get role => UserRole.student;
}

class AdminUser extends AppUser {
  AdminUser({
    required super.email,
    required super.username,
    required super.password,
  });

  @override
  UserRole get role => UserRole.admin;
}
