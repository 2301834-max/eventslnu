import 'package:flutter/material.dart';
import 'package:flutter_smart_event/student_ui/login_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'LNU Smart Events',
      home: const LoginScreen(),
    );
  }
}
