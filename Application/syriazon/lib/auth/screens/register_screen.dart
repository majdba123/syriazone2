import 'package:flutter/material.dart';
import '../widgets/auth_form_field.dart';
import 'package:syriazon/auth/auth_service.dart';
import 'package:syriazon/screens/home_screen.dart';

import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({Key? key}) : super(key: key);

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  Future<void> _register() async {
    if (_formKey.currentState!.validate()) {
      if (_passwordController.text != _confirmPasswordController.text) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('كلمة المرور غير متطابقة')),
        );
        return;
      }

      setState(() => _isLoading = true);

      // TODO: استبدل هذا بالاتصال الفعلي بالخلفية
      try {
        final response = await AuthService.register(
          _emailController.text,
          _passwordController.text,
          _nameController.text,
        );

        if (response != null && mounted) {
          final userType = response['user_type'];

          if (userType == 'Admin') {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('المسؤولون لا يمكنهم الدخول من هنا'),
              ),
            );
          } else if (userType == "User") {
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
      setState(() => _isLoading = false);

      // TODO: معالجة نتيجة التسجيل
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
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
            Container(
              padding: EdgeInsets.symmetric(vertical: 8, horizontal: 16),
              decoration: BoxDecoration(
                border: Border(
                  bottom: BorderSide(color: Colors.blue[300]!, width: 3),
                ),
              ),
              child: Text(
                'إنشاء حساب جديد',
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue[900],
                  fontFamily: 'Tajawal',
                ),
                textAlign: TextAlign.center,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'املأ التفاصيل لبدء رحلتك معنا',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey[600],
                fontFamily: 'Tajawal',
                letterSpacing: 0.5,
                height: 1.5,
                shadows: [
                  Shadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 2,
                    offset: const Offset(1, 1),
                  ),
                ],
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 40),
            Form(
              key: _formKey,
              child: Column(
                children: [
                  AuthFormField(
                    controller: _nameController,
                    label: 'الاسم الكامل',
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'الرجاء إدخال الاسم الكامل';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),
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
                  const SizedBox(height: 20),
                  AuthFormField(
                    controller: _confirmPasswordController,
                    label: 'تأكيد كلمة المرور',
                    obscureText: _obscureConfirmPassword,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'الرجاء تأكيد كلمة المرور';
                      }
                      return null;
                    },
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirmPassword
                            ? Icons.visibility_off
                            : Icons.visibility,
                        color: Colors.grey,
                      ),
                      onPressed: () {
                        setState(
                          () =>
                              _obscureConfirmPassword =
                                  !_obscureConfirmPassword,
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 30),
                  AuthButton(
                    text: 'إنشاء حساب',
                    onPressed: () async {
                      if (_formKey.currentState!.validate()) {
                        if (_passwordController.text !=
                            _confirmPasswordController.text) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('كلمة المرور غير متطابقة'),
                            ),
                          );
                          return;
                        }

                        setState(() => _isLoading = true);

                        final result = await AuthService.register(
                          _nameController.text,
                          _emailController.text,
                          _passwordController.text,
                        );

                        if (mounted) {
                          setState(() => _isLoading = false);

                          if (result?['success'] == true) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(result?['message'])),
                            );
                            Navigator.pushReplacement(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const LoginScreen(),
                              ),
                            );
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(result?['message'])),
                            );
                          }
                        }
                      }
                    },
                    isLoading: _isLoading,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 30),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                RichText(
                  text: TextSpan(
                    text: 'لديك ',
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontFamily: 'Tajawal',
                      fontSize: 14,
                    ),
                    children: [
                      TextSpan(
                        text: 'حساب بالفعل',
                        style: TextStyle(
                          color: Colors.grey[800],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const TextSpan(text: '؟'),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const LoginScreen(),
                      ),
                    );
                  },
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.login, color: Colors.blue[800], size: 20),
                      const SizedBox(width: 8),
                      Text(
                        'تسجيل الدخول',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.blue[800],
                          fontFamily: 'Tajawal',
                          fontSize: 18,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
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
