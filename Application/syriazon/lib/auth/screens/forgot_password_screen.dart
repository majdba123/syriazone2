import 'package:flutter/material.dart';
import 'package:syriazon/auth/widgets/auth_form_field.dart';
import 'package:syriazon/auth/widgets/auth_button.dart';
import 'package:syriazon/auth/auth_service.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({Key? key}) : super(key: key);

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _emailController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = false;

  Future<void> _resetPassword() async {
    if (_formKey.currentState!.validate()) {
      setState(() => _isLoading = true);

      try {
        final success = await AuthService.forgotPassword(_emailController.text);

        if (mounted) {
          if (success) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text(
                  'تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني',
                ),
              ),
            );
            Navigator.pop(context);
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('البريد الإلكتروني غير مسجل')),
            );
          }
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(SnackBar(content: Text('خطأ: ${e.toString()}')));
        }
      } finally {
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('استعادة كلمة المرور'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 40),
            Text(
              'نسيت كلمة المرور؟',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Theme.of(context).primaryColor,
                fontFamily: 'Tajawal',
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'أدخل بريدك الإلكتروني لاستعادة كلمة المرور',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey[600],
                fontFamily: 'Tajawal',
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 40),
            Form(
              key: _formKey,
              child: AuthFormField(
                controller: _emailController,
                label: 'البريد الإلكتروني',
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'الرجاء إدخال البريد الإلكتروني';
                  }
                  if (!value.contains('@')) {
                    return 'البريد الإلكتروني غير صالح';
                  }
                  return null;
                },
              ),
            ),
            const SizedBox(height: 30),
            SimpleAuthButton(
              text: 'إرسال رابط التعيين',
              onPressed: _resetPassword,
              isLoading: _isLoading,
            ),
          ],
        ),
      ),
    );
  }
}
