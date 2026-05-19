import 'dart:typed_data';

import 'package:flutter_smart_event/api/api_client.dart';
import 'package:flutter_smart_event/api/models/api_event.dart';

class ApiEventService {
  ApiEventService(this._api);

  final ApiClient _api;

  Future<List<ApiEvent>> listEvents() async {
    final json = await _api.getJson('/api/events');
    final data = json['data'];
    if (data is! List) return const [];
    final events = data
        .whereType<Map<String, dynamic>>()
        .map(ApiEvent.fromJson)
        .toList();

    // Student app should not show admin-only states.
    events.removeWhere((e) {
      final s = e.status.toLowerCase();
      return s == 'draft' || s == 'cancelled';
    });

    return events;
  }

  /// Backwards-compatible helper used by existing admin UI.
  /// Filters for upcoming events based on startDate >= now (when present).
  Future<List<ApiEvent>> getUpcomingEvents() async {
    final events = await listEvents();
    final now = DateTime.now();
    final upcoming = events.where((e) {
      if (e.startDate == null) return true;
      return !e.startDate!.isBefore(now);
    }).toList();
    upcoming.sort((a, b) {
      final ad = a.startDate ?? DateTime.fromMillisecondsSinceEpoch(0);
      final bd = b.startDate ?? DateTime.fromMillisecondsSinceEpoch(0);
      return ad.compareTo(bd);
    });
    return upcoming;
  }

  /// Create a new event in Laravel.
  Future<ApiEvent> createEvent({
    required String title,
    required DateTime date,
    required String venue,
    required String description,
    required int capacity,
    Uint8List? posterBytes,
    String? posterFileName,
  }) async {
    final start = DateTime(date.year, date.month, date.day, 9);
    final end = DateTime(date.year, date.month, date.day, 17);

    final fields = {
      'title': title,
      'organization': 'LNU Smart Events',
      'description': description,
      'start_date': start.toIso8601String(),
      'end_date': end.toIso8601String(),
      'location': venue,
      'max_participants': capacity.toString(),
    };

    final json = posterBytes == null || posterFileName == null
        ? await _api.postJson('/api/events', body: fields)
        : await _api.postMultipart(
            '/api/events',
            fields: fields,
            fileField: 'event_image',
            fileBytes: posterBytes,
            fileName: posterFileName,
          );

    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw ApiException('Unexpected create event response.');
    }
    return ApiEvent.fromJson(data);
  }
}
