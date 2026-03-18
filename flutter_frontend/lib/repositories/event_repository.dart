import 'package:flutter_smart_event/models/event.dart';

abstract class EventRepository {
  List<Event> getAllEvents();
  void addEvent(Event event);
}
