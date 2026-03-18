import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/token_store.dart';
import 'package:flutter_smart_event/services/api_auth_service.dart';
import 'package:flutter_smart_event/services/api_event_service.dart';
import 'package:flutter_smart_event/services/api_registration_service.dart';
import 'package:flutter_smart_event/services/api_attendance_service.dart';

class ServiceLocator {
  ServiceLocator._();

  static final ServiceLocator instance = ServiceLocator._();

  late final TokenStore tokenStore = TokenStore(const FlutterSecureStorage());
  late final ApiClient apiClient = ApiClient(tokenStore: tokenStore);

  late final ApiAuthService authService = ApiAuthService(apiClient, tokenStore);
  late final ApiEventService eventService = ApiEventService(apiClient);
  late final ApiRegistrationService registrationService =
      ApiRegistrationService(apiClient);
  late final ApiAttendanceService attendanceService =
      ApiAttendanceService(apiClient);
}
