import 'package:flutter_smart_event/models/event.dart';
import 'package:flutter_smart_event/repositories/event_repository.dart';

class EventService {
  EventService(this._repository);

  final EventRepository _repository;

  List<Event> getUpcomingEvents() {
    final now = DateTime.now();
    final events = _repository
        .getAllEvents()
        .where((event) => !event.date.isBefore(now))
        .toList();
    events.sort((a, b) => a.date.compareTo(b.date));
    return events;
  }

  void createEvent({
    required String title,
    required DateTime date,
    required String venue,
    required String description,
    required int capacity,
  }) {
    final event = Event(
      title: title,
      date: date,
      venue: venue,
      description: description,
      capacity: capacity,
    );
    _repository.addEvent(event);
  }
}
