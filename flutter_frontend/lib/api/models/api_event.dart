import 'package:flutter_smart_event/api/api_config.dart';

class ApiEvent {
  ApiEvent({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.status,
    required this.startDate,
    required this.endDate,
    required this.eventImageUrl,
  });

  final int id;
  final String title;
  final String description;
  final String location;
  final String status;
  final DateTime? startDate;
  final DateTime? endDate;
  final String eventImageUrl;

  factory ApiEvent.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic v) =>
        v == null ? null : DateTime.tryParse(v.toString());

    return ApiEvent(
      id: (json['id'] as num).toInt(),
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      location: (json['location'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      startDate: parseDate(json['start_date']),
      endDate: parseDate(json['end_date']),
      eventImageUrl: _normalizeImageUrl(json['event_image_url']),
    );
  }

  static String _normalizeImageUrl(dynamic value) {
    final raw = (value ?? '').toString().trim();
    if (raw.isEmpty) return '';
    if (raw.contains('event-placeholder.svg')) return '';

    final apiUri = Uri.parse(ApiConfig.baseUrl);
    final uri = Uri.tryParse(raw);
    if (uri == null) return raw;

    if (!uri.hasScheme) {
      return apiUri.resolve(raw.startsWith('/') ? raw : '/$raw').toString();
    }

    final shouldUseApiHost =
        uri.host == 'localhost' ||
        uri.host == '127.0.0.1' ||
        uri.host == apiUri.host && !uri.hasPort && apiUri.hasPort;

    if (!shouldUseApiHost) return raw;

    return uri
        .replace(
          scheme: apiUri.scheme,
          host: apiUri.host,
          port: apiUri.hasPort ? apiUri.port : null,
        )
        .toString();
  }
}
