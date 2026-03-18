import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_smart_event/main.dart';

void main() {
  testWidgets('renders login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const MyApp());

    expect(find.text('Student Login'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
