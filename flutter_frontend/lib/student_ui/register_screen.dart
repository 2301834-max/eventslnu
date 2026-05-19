import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_smart_event/core/service_locator.dart';
import 'package:flutter_smart_event/theme/app_theme.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _studentIdController = TextEditingController();
  final TextEditingController _lastNameController = TextEditingController();
  final TextEditingController _firstNameController = TextEditingController();
  final TextEditingController _middleInitialController =
      TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();
  bool _loading = false;

  @override
  void dispose() {
    _studentIdController.dispose();
    _lastNameController.dispose();
    _firstNameController.dispose();
    _middleInitialController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // Format full name as "last name, first name middle initial"
    final lastName = _lastNameController.text.trim();
    final firstName = _firstNameController.text.trim();
    final middleInitial = _middleInitialController.text.trim();

    final formattedName = middleInitial.isNotEmpty
        ? '$lastName, $firstName $middleInitial'
        : '$lastName, $firstName';

    setState(() => _loading = true);
    try {
      final email = '${_emailController.text.trim()}@lnu.edu.ph';

      await ServiceLocator.instance.authService.register(
        name: formattedName,
        email: email,
        password: _passwordController.text,
        passwordConfirmation: _confirmPasswordController.text,
        studentId: _studentIdController.text.trim(),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Registration successful. Please login.')),
      );
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      final message = e.toString();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Create Student Account')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 520),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: AppTheme.navy.withValues(alpha: 0.08),
                    ),
                  ),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Icon(
                          Icons.badge_outlined,
                          color: AppTheme.navy,
                          size: 42,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Join the campus events portal',
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.titleLarge,
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Create your student profile to register for school activities.',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: AppTheme.muted),
                        ),
                        const SizedBox(height: 22),
                        TextFormField(
                          controller: _studentIdController,
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                            LengthLimitingTextInputFormatter(7),
                          ],
                          maxLength: 7,
                          decoration: const InputDecoration(
                            labelText: 'Student ID',
                            prefixIcon: Icon(Icons.badge_outlined),
                            helperText: 'Numbers only, maximum 7 digits',
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Student ID is required';
                            }
                            if (!RegExp(r'^\d{1,7}$').hasMatch(value.trim())) {
                              return 'Student ID must be numbers only, up to 7 digits';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _lastNameController,
                          decoration: const InputDecoration(
                            labelText: 'Last Name',
                            prefixIcon: Icon(Icons.person_outline),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Last name is required';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _firstNameController,
                          decoration: const InputDecoration(
                            labelText: 'First Name',
                            prefixIcon: Icon(Icons.person_outline),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'First name is required';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _middleInitialController,
                          maxLength: 1,
                          decoration: const InputDecoration(
                            labelText: 'Middle Initial (Optional)',
                            prefixIcon: Icon(Icons.person_outline),
                            helperText: 'Single letter only',
                          ),
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                            LengthLimitingTextInputFormatter(7),
                          ],
                          maxLength: 7,
                          decoration: const InputDecoration(
                            labelText: 'Email',
                            prefixIcon: Icon(Icons.mail_outline),
                            suffixText: '@lnu.edu.ph',
                            helperText: 'Enter student number only',
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Student email number is required';
                            }
                            if (!RegExp(r'^\d{1,7}$').hasMatch(value.trim())) {
                              return 'Use numbers only, maximum 7 digits';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: true,
                          decoration: const InputDecoration(
                            labelText: 'Password',
                            prefixIcon: Icon(Icons.lock_outline),
                            helperText:
                                'Use at least 8 characters with letters and numbers',
                          ),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Password is required';
                            }
                            if (value.length < 8) {
                              return 'Password must be at least 8 characters';
                            }
                            if (!RegExp(r'[A-Za-z]').hasMatch(value) ||
                                !RegExp(r'\d').hasMatch(value)) {
                              return 'Password must include at least one letter and one number';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _confirmPasswordController,
                          obscureText: true,
                          decoration: const InputDecoration(
                            labelText: 'Confirm password',
                            prefixIcon: Icon(Icons.verified_user_outlined),
                          ),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please confirm your password';
                            }
                            if (value != _passwordController.text) {
                              return 'Passwords do not match';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 18),
                        ElevatedButton.icon(
                          onPressed: _loading ? null : _register,
                          icon: _loading
                              ? const SizedBox(
                                  height: 18,
                                  width: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.white,
                                  ),
                                )
                              : const Icon(Icons.how_to_reg),
                          label: Text(
                            _loading ? 'Creating account...' : 'Create Account',
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextButton(
                          onPressed: _loading
                              ? null
                              : () => Navigator.pop(context),
                          child: const Text('Back to sign in'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
