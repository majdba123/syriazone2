import 'package:flutter/material.dart';
import 'package:syriazon/auth/auth_service.dart';
import 'package:syriazon/screens/home_screen.dart';

class AuthButton extends StatelessWidget {
  final String text;
  final String email;
  final String password;
  final bool isLoading;
  final Function(bool) onLoadingChange;

  const AuthButton({
    Key? key,
    required this.text,
    required this.email,
    required this.password,
    required this.isLoading,
    required this.onLoadingChange,
  }) : super(key: key);

  Future<void> _handleAuth(BuildContext context) async {
    onLoadingChange(true);

    try {
      final success = await AuthService.login(email, password);
    print("Response from login: $success"); // ✅ طباعة البيانات في الـ Output
      if (success != null && success['success'] == true && context.mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const HomeScreen()),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('بيانات الدخول غير صحيحة')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('حدث خطأ: ${e.toString()}')));
    } finally {
      if (context.mounted) {
        onLoadingChange(false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: isLoading ? null : () => _handleAuth(context),
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.blue[800],
        minimumSize: const Size(double.infinity, 50),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ), // ✅ إغلاق `RoundedRectangleBorder`
      ), // ✅ إغلاق `ElevatedButton.styleFrom`
      child:
          isLoading
              ? const CircularProgressIndicator(color: Colors.white)
              : Text(
                text,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
    ); // ✅ إغلاق `ElevatedButton`
  }
}

class SimpleAuthButton extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isLoading;
  final Color? backgroundColor;

  const SimpleAuthButton({
    Key? key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.backgroundColor,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: isLoading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: backgroundColor ?? Colors.blue[800],
        minimumSize: const Size(double.infinity, 50),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ), // ✅ إغلاق `RoundedRectangleBorder`
      ), // ✅ إغلاق `ElevatedButton.styleFrom`
      child:
          isLoading
              ? const CircularProgressIndicator(color: Colors.white)
              : Text(
                text,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
    ); // ✅ إغلاق `ElevatedButton`
  }
}
