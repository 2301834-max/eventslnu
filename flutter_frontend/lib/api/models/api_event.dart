class ApiEvent {
  ApiEvent({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.status,
    required this.startDate,
    required this.endDate,
  });

  final int id;
  final String title;
  final String description;
  final String location;
  final String status;
  final DateTime? startDate;
  final DateTime? endDate;

  factory ApiEvent.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic v) => v == null ? null : DateTime.tryParse(v.toString());

    return ApiEvent(
      id: (json['id'] as num).toInt(),
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      location: (json['location'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      startDate: parseDate(json['start_date']),
      endDate: parseDate(json['end_date']),
    );
  }
}

