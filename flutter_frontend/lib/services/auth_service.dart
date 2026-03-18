import 'package:flutter_smart_event/models/app_user.dart';
import 'package:flutter_smart_event/repositories/auth_repository.dart';

class AuthResult {
  const AuthResult({required this.success, this.message = '', this.user});

  final bool success;
  final String message;
  final AppUser? user;
}

class AuthService {
  AuthService(this._repository);

  final AuthRepository _repository;

  AuthResult login({required String emailOrUsername, required String password}) {
    final query = emailOrUsername.trim().toLowerCase();
    final matchingUsers = _repository.getUsers().where((candidate) {
      return candidate.email.toLowerCase() == query ||
          candidate.username.toLowerCase() == query;
    }).toList();

    final user = matchingUsers.isEmpty ? null : matchingUsers.first;

    if (user == null || !user.validatePassword(password)) {
      return const AuthResult(success: false, message: 'Invalid credentials.');
    }

    return AuthResult(success: true, user: user);
  }

  AuthResult register({
    required String email,
    required String username,
    required String password,
  }) {
    final normalizedEmail = email.trim().toLowerCase();
    final normalizedUsername = username.trim().toLowerCase();

    final users = _repository.getUsers();
    final alreadyExists = users.any((user) {
      return user.email.toLowerCase() == normalizedEmail ||
          user.username.toLowerCase() == normalizedUsername;
    });

    if (alreadyExists) {
      return const AuthResult(
        success: false,
        message: 'Account already exists. Please login.',
      );
    }

    final user = StudentUser(
      email: email.trim(),
      username: username.trim(),
      password: password,
    );

    _repository.addUser(user);
    return AuthResult(success: true, user: user);
  }
}
