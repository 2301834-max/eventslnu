class Event {
  Event({
    required this.title,
    required this.date,
    required this.venue,
    required this.description,
    required this.capacity,
  });

  final String title;
  final DateTime date;
  final String venue;
  final String description;
  final int capacity;
}
