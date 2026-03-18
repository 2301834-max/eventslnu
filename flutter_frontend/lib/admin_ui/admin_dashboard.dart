import 'package:flutter/material.dart';
import 'package:flutter_smart_event/admin_ui/create_event.dart';
import 'package:flutter_smart_event/core/service_locator.dart';

class AdminDashboard extends StatefulWidget {
  const AdminDashboard({super.key});

  @override
  State<AdminDashboard> createState() => _AdminDashboardState();
}

class _AdminDashboardState extends State<AdminDashboard> {
  int _totalEvents = 0;

  @override
  void initState() {
    super.initState();
    _refreshEventCount();
  }

  Future<void> _refreshEventCount() async {
    final events = await ServiceLocator.instance.eventService.getUpcomingEvents();
    if (!mounted) return;
    setState(() => _totalEvents = events.length);
  }

  Widget _dashboardItem(IconData icon, String title, {VoidCallback? onTap}) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
      child: ListTile(
        leading: Icon(icon, color: const Color(0xFF06035E)),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
        onTap: onTap,
      ),
    );
  }

  Future<void> _openCreateEvent() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const CreateEvent()),
    );

    if (!mounted) {
      return;
    }

    await _refreshEventCount();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: const Color(0xFF06035E),
        centerTitle: true,
        title: const Text(
          'Smart Event Management',
          style: TextStyle(color: Colors.white, fontSize: 18),
        ),
      ),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 700),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Upcoming events: $_totalEvents',
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.add, color: Colors.white),
                    label: const Text(
                      'Create Event',
                      style: TextStyle(color: Colors.white),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF06035E),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                    ),
                    onPressed: _openCreateEvent,
                  ),
                ),
                const SizedBox(height: 25),
                _dashboardItem(Icons.event, 'Manage Events'),
                _dashboardItem(Icons.qr_code_scanner, 'Scan Attendance'),
                _dashboardItem(Icons.bar_chart, 'Reports'),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
