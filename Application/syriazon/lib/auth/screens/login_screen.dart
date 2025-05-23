import 'package:flutter/material.dart';
import 'package:syriazon/auth/auth_service.dart';
import 'package:syriazon/screens/home_screen.dart';
import 'package:syriazon/auth/screens/forgot_password_screen.dart';
import 'package:syriazon/auth/screens/register_screen.dart';
import 'package:syriazon/auth/widgets/auth_form_field.dart';

class AuthButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final Color? color;

  const AuthButton({
    Key? key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.color,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: isLoading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: color ?? Theme.of(context).primaryColor,
        minimumSize: const Size(double.infinity, 50),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
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
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;

  Future<void> _login() async {
    if (_formKey.currentState!.validate()) {
      setState(() => _isLoading = true);

      try {
        final response = await AuthService.login(
          _emailController.text,
          _passwordController.text,
        );

        if (response != null && mounted) {
          final userType = response['user_type'];
          print(userType);

          if (userType == 'Admin') {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('المسؤولون لا يمكنهم الدخول من هنا'),
              ),
            );
          } else if (userType == "User") {
            print("sss");
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (_) => const HomeScreen()),
            );
          }
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
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const SizedBox(height: 60),
            Text(
              'مرحباً بعودتك!',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                fontFamily: 'Tajawal',
                color: Theme.of(context).primaryColor,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            Text(
              'سجل الدخول لاستئناف رحلتك',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 40),
            Form(
              key: _formKey,
              child: Column(
                children: [
                  AuthFormField(
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
                  const SizedBox(height: 20),
                  AuthFormField(
                    controller: _passwordController,
                    label: 'كلمة المرور',
                    obscureText: _obscurePassword,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'الرجاء إدخال كلمة المرور';
                      }
                      if (value.length < 6) {
                        return 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                      }
                      return null;
                    },
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off
                            : Icons.visibility,
                        color: Colors.grey,
                      ),
                      onPressed: () {
                        setState(() => _obscurePassword = !_obscurePassword);
                      },
                    ),
                  ),
                  const SizedBox(height: 10),
                  Align(
                    alignment: Alignment.centerLeft,
                    child: TextButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const ForgotPasswordScreen(),
                          ),
                        );
                      },
                      child: Text(
                        'نسيت كلمة المرور؟',
                        style: TextStyle(
                          color: Theme.of(context).primaryColor,
                          fontFamily: 'Tajawal',
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 30),
                  AuthButton(
                    text: 'تسجيل الدخول',
                    onPressed: _login,
                    isLoading: _isLoading,
                    color: Colors.blue[800],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 30),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'ليس لديك حساب؟',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontFamily: 'Tajawal',
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const RegisterScreen(),
                      ),
                    );
                  },
                  child: Text(
                    'إنشاء حساب',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).primaryColor,
                      fontFamily: 'Tajawal',
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
