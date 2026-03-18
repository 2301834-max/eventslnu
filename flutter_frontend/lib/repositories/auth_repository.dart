import 'package:flutter_smart_event/models/app_user.dart';

abstract class AuthRepository {
  List<AppUser> getUsers();
  void addUser(AppUser user);
}
