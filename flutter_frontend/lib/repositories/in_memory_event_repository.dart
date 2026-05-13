import 'package:flutter_smart_event/models/event.dart';
import 'package:flutter_smart_event/repositories/event_repository.dart';

class InMemoryEventRepository implements EventRepository {
  final List<Event> _events = [
    Event(
      title: 'Campus Tech Talk',
      date: DateTime.now().add(const Duration(days: 2)),
      venue: 'Main Auditorium',
      description: 'Latest trends in software engineering.',
      capacity: 300,
    ),
    Event(
      title: 'Career Fair',
      date: DateTime.now().add(const Duration(days: 7)),
      venue: 'College Gym',
      description: 'Meet recruiters and industry professionals.',
      capacity: 500,
    ),
  ];

  @override
  List<Event> getAllEvents() => List.unmodifiable(_events);

  @override
  void addEvent(Event event) {
    _events.add(event);
  }
}
