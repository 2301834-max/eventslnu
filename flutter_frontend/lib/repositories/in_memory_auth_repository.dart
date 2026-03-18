import 'package:flutter_smart_event/models/app_user.dart';
import 'package:flutter_smart_event/repositories/auth_repository.dart';

class InMemoryAuthRepository implements AuthRepository {
  final List<AppUser> _users = [
    AdminUser(
      email: 'admin@lnu.edu',
      username: 'admin',
      password: 'admin123',
    ),
  ];

  @override
  List<AppUser> getUsers() => List.unmodifiable(_users);

  @override
  void addUser(AppUser user) {
    _users.add(user);
  }
}
