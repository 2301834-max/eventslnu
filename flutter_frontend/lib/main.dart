import 'package:flutter/material.dart';
import 'package:flutter_smart_event/student_ui/login_screen.dart';
import 'package:flutter_smart_event/student_ui/student_ui_helpers.dart';

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
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: StudentPalette.background,
        colorScheme: const ColorScheme.light(
          primary: StudentPalette.primary,
          secondary: StudentPalette.secondary,
          surface: Colors.white,
          error: StudentPalette.danger,
        ),
        textTheme: ThemeData.light().textTheme.apply(
              bodyColor: StudentPalette.textPrimary,
              displayColor: StudentPalette.textPrimary,
            ),
        appBarTheme: const AppBarTheme(
          elevation: 0,
          centerTitle: false,
          backgroundColor: Colors.transparent,
          foregroundColor: StudentPalette.textPrimary,
          titleTextStyle: TextStyle(
            color: StudentPalette.textPrimary,
            fontSize: 22,
            fontWeight: FontWeight.w700,
          ),
        ),
        inputDecorationTheme: studentInputTheme(),
        filledButtonTheme: FilledButtonThemeData(
          style: FilledButton.styleFrom(
            backgroundColor: StudentPalette.primary,
            foregroundColor: Colors.white,
            minimumSize: const Size.fromHeight(54),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            foregroundColor: StudentPalette.primary,
            minimumSize: const Size.fromHeight(52),
            side: const BorderSide(color: Color(0xFFC7D8F6)),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
          ),
        ),
        cardTheme: CardThemeData(
          color: Colors.white,
          elevation: 0,
          margin: EdgeInsets.zero,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
            side: const BorderSide(color: Color(0xFFE2E8F0)),
          ),
        ),
        snackBarTheme: SnackBarThemeData(
          behavior: SnackBarBehavior.floating,
          backgroundColor: StudentPalette.textPrimary,
          contentTextStyle: const TextStyle(color: Colors.white),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        ),
        pageTransitionsTheme: const PageTransitionsTheme(
          builders: {
            TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
            TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
          },
        ),
      ),
      home: const LoginScreen(),
    );
  }
}
