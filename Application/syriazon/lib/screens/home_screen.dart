import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:syriazon/model/product_model.dart';
import 'package:syriazon/model/category_model.dart';
import 'package:syriazon/screens/profile_screen.dart';
import 'package:syriazon/services/product_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Product> _products = [];
  List<Category> _categories = [];
  List<SubCategory> _subCategories = [];
  bool _isLoading = false;
  int? _currentCategory;
  int? _currentSubCategory;
  String? _userName;
  bool _isLoggedIn = false;
  int? _currentVendor;
  String _searchQuery = '';
  double _minPrice = 0;
  double _maxPrice = 1000;
  int _currentPage = 1;
  int _totalPages = 1;

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();

    _loadInitialData();
  }

  Future<void> _checkLoginStatus() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _isLoggedIn = prefs.getString('auth_token') != null;
      _userName = prefs.getString('user_name');
    });
  }

  Future<void> _loadInitialData() async {
    await _loadCategories();
    await _loadProducts();
  }

  Future<void> _loadCategories() async {
    setState(() => _isLoading = true);
    try {
      final categories = await ProductService.getAllCategories();
      final subCategories = await ProductService.getAllSubCategories();

      print('Loaded ${categories.length} categories');
      print('Loaded ${subCategories.length} subcategories');

      setState(() {
        _categories = categories;
        _subCategories = subCategories;
      });
    } catch (e) {
      print('Error loading categories in UI: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to load categories: ${e.toString()}'),
          duration: const Duration(seconds: 5),
        ),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadProducts({int page = 1}) async {
    setState(() {
      _isLoading = true;
      _currentPage = page;
    });

    try {
      final response = await ProductService.getFilteredProducts(
        categoryId: _currentCategory,
        subCategoryId: _currentSubCategory,
        vendorId: _currentVendor,
        name: _searchQuery.isNotEmpty ? _searchQuery : null,
        minPrice: _minPrice,
        maxPrice: _maxPrice,
        page: page,
      );

      if (response['success']) {
        setState(() {
          _products =
              (response['products'] as List)
                  .map((p) => Product.fromJson(p))
                  .toList();
          _totalPages = response['pagination']['total_pages'] ?? 1;
        });
      } else {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(response['message'])));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading products: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _logout() async {
    final success = await ProductService.logout();
    if (success) {
      setState(() {
        _isLoggedIn = false;
        _userName = null;
      });
      // Navigator.pushReplacement(
      //   context,
      //   MaterialPageRoute(builder: (context) => const LoginScreen()),
      // );
    } else {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Failed to logout')));
    }
  }

  void _resetFilters() {
    setState(() {
      _currentCategory = null;
      _currentSubCategory = null;
      _currentVendor = null;
      _searchQuery = '';
      _minPrice = 0;
      _maxPrice = 1000;
    });
    _loadProducts();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('SyriaZone'),
        actions: [
          if (_isLoggedIn)
            IconButton(icon: const Icon(Icons.logout), onPressed: _logout),
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => _showSearchDialog(),
          ),
          IconButton(
            icon: const Icon(Icons.filter_alt),
            onPressed: () => _showFilterDialog(),
          ),
        ],
      ),
      body: _buildMainContent(),
      floatingActionButton: FloatingActionButton(
        onPressed: _resetFilters,
        child: const Icon(Icons.refresh),
        tooltip: 'Reset filters',
      ),
      bottomNavigationBar: _buildBottomNavigationBar(),
    );
  }

  Widget _buildBottomNavigationBar() {
    return BottomNavigationBar(
      currentIndex: 0, // يمكنك تغيير هذا حسب الصفحة الحالية
      type: BottomNavigationBarType.fixed,
      selectedItemColor: Colors.blue,
      unselectedItemColor: Colors.grey,
      items: const [
        BottomNavigationBarItem(icon: Icon(Icons.home), label: 'الرئيسية'),
        BottomNavigationBarItem(icon: Icon(Icons.person), label: 'البروفايل'),
        BottomNavigationBarItem(
          icon: Icon(Icons.shopping_bag),
          label: 'الطلبات',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.support_agent),
          label: 'الدعم',
        ),
      ],
      onTap: (index) {
        // يمكنك إضافة التنقل بين الصفحات هنا
        switch (index) {
          case 1:
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const ProfileScreen()),
            );
            // Navigator.push(context, MaterialPageRoute(builder: (_) => ProfileScreen()));
            break;
          case 2:
            // Navigator.push(context, MaterialPageRoute(builder: (_) => OrdersScreen()));
            break;
          case 3:
            // Navigator.push(context, MaterialPageRoute(builder: (_) => SupportScreen()));
            break;
        }
      },
    );
  }

  Widget _buildMainContent() {
    if (_isLoading && _products.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_products.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('No products available'),
            TextButton(
              onPressed: _resetFilters,
              child: const Text('Reset filters'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        _buildFilterChips(),
        Expanded(
          child: ListView.builder(
            itemCount: _products.length + 1,
            itemBuilder: (context, index) {
              if (index == _products.length) {
                return _buildPaginationControls();
              }
              return ProductCardDetailed(
                product: _products[index],
                isLoggedIn: _isLoggedIn,
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildFilterChips() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Row(
          children: [
            if (_currentCategory != null)
              _buildFilterChip(
                'الفئة: ${_categories.firstWhere((c) => c.id == _currentCategory).title}',
                onDeleted: () {
                  setState(() {
                    _currentCategory = null;
                    _currentSubCategory = null;
                  });
                  _loadProducts();
                },
              ),
            if (_currentSubCategory != null)
              _buildFilterChip(
                'التصنيف الفرعي: ${_subCategories.firstWhere((sc) => sc.id == _currentSubCategory).name}',
                onDeleted: () {
                  setState(() => _currentSubCategory = null);
                  _loadProducts();
                },
              ),

            if (_searchQuery.isNotEmpty)
              _buildFilterChip(
                'بحث: $_searchQuery',
                onDeleted: () {
                  setState(() => _searchQuery = '');
                  _loadProducts();
                },
              ),
            if (_minPrice > 0 || _maxPrice < 1000)
              _buildFilterChip(
                'السعر: ${_minPrice.toStringAsFixed(0)}-${_maxPrice.toStringAsFixed(0)} ل.س',
                onDeleted: () {
                  setState(() {
                    _minPrice = 0;
                    _maxPrice = 1000;
                  });
                  _loadProducts();
                },
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, {VoidCallback? onDeleted}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4.0),
      child: Chip(
        label: Text(label),
        deleteIcon: const Icon(Icons.close, size: 16),
        onDeleted: onDeleted,
      ),
    );
  }

  Widget _buildPaginationControls() {
    if (_totalPages <= 1) return const SizedBox();

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed:
                _currentPage > 1
                    ? () => _loadProducts(page: _currentPage - 1)
                    : null,
          ),
          Text('الصفحة $_currentPage من $_totalPages'),
          IconButton(
            icon: const Icon(Icons.arrow_forward),
            onPressed:
                _currentPage < _totalPages
                    ? () => _loadProducts(page: _currentPage + 1)
                    : null,
          ),
        ],
      ),
    );
  }

  void _showSearchDialog() {
    showDialog(
      context: context,
      builder:
          (context) => AlertDialog(
            title: const Text('بحث عن منتجات'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  decoration: const InputDecoration(
                    labelText: 'اسم المنتج',
                    prefixIcon: Icon(Icons.search),
                  ),
                  onChanged: (value) => _searchQuery = value,
                ),
                const SizedBox(height: 16),
                RangeSlider(
                  values: RangeValues(_minPrice, _maxPrice),
                  min: 0,
                  max: 1000,
                  divisions: 10,
                  labels: RangeLabels(
                    _minPrice.toStringAsFixed(0),
                    _maxPrice.toStringAsFixed(0),
                  ),
                  onChanged: (values) {
                    setState(() {
                      _minPrice = values.start;
                      _maxPrice = values.end;
                    });
                  },
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('${_minPrice.toStringAsFixed(0)} ل.س'),
                    Text('${_maxPrice.toStringAsFixed(0)} ل.س'),
                  ],
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('إلغاء'),
              ),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  _loadProducts();
                },
                child: const Text('بحث'),
              ),
            ],
          ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder:
          (context) => AlertDialog(
            title: const Text('تصفية المنتجات'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [_buildCategoryFilter(), _buildSubCategoryFilter()],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('إلغاء'),
              ),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  _loadProducts();
                },
                child: const Text('تطبيق الفلاتر'),
              ),
            ],
          ),
    );
  }

  Widget _buildCategoryFilter() {
    return ExpansionTile(
      title: const Text('الفئات'),
      children:
          _categories.map((category) {
            return RadioListTile<int>(
              title: Text(category.title),
              value: category.id,
              groupValue: _currentCategory,
              onChanged: (value) {
                setState(() {
                  _currentCategory = value;
                  _currentSubCategory = null;
                });
              },
            );
          }).toList(),
    );
  }

  Widget _buildSubCategoryFilter() {
    final subCats =
        _currentCategory != null
            ? _subCategories
                .where((sc) => sc.categoryId == _currentCategory)
                .toList()
            : [];

    return ExpansionTile(
      title: const Text('التصنيفات الفرعية'),
      initiallyExpanded: true,
      children:
          subCats.isNotEmpty
              ? subCats.map((subCategory) {
                return RadioListTile<int>(
                  title: Text(subCategory.name),
                  value: subCategory.id,
                  groupValue: _currentSubCategory,
                  onChanged: (value) {
                    setState(() => _currentSubCategory = value);
                  },
                );
              }).toList()
              : [const ListTile(title: Text('اختر فئة أولاً'))],
    );
  }
}

class ProductCardDetailed extends StatefulWidget {
  final Product product;
  final bool isLoggedIn;

  const ProductCardDetailed({
    Key? key,
    required this.product,
    required this.isLoggedIn,
  }) : super(key: key);

  @override
  State<ProductCardDetailed> createState() => _ProductCardDetailedState();
}

class _ProductCardDetailedState extends State<ProductCardDetailed> {
  int? _userRating;
  String? _userComment;
  bool _showCommentField = false;
  final _commentController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.all(12),
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // قسم الصور
          Stack(
            children: [
              Container(
                height: 220,
                color: Colors.grey[100],
                child:
                    widget.product.images.isNotEmpty
                        ? PageView.builder(
                          itemCount: widget.product.images.length,
                          itemBuilder:
                              (ctx, index) => Image.network(
                                widget.product.images[index].url,
                                fit: BoxFit.contain,
                                width: double.infinity,
                              ),
                        )
                        : Center(
                          child: Icon(
                            Icons.image_not_supported,
                            size: 80,
                            color: Colors.grey[400],
                          ),
                        ),
              ),

              // علامة الخصم
              if (widget.product.discountInfo.isActive)
                Positioned(
                  top: 12,
                  right: 12,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.red[600],
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: const Text(
                      'خصم مميز',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ),
                ),
            ],
          ),

          // قسم المعلومات الأساسية
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // العنوان والفئة
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        widget.product.name,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          height: 1.3,
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.blue[50],
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        widget.product.subcategory,
                        style: TextStyle(
                          color: Colors.blue[800],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),

                // السعر
                Row(
                  children: [
                    Text(
                      '${widget.product.finalPrice.toStringAsFixed(2)} ل.س',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.blue,
                      ),
                    ),
                    const SizedBox(width: 10),
                    if (widget.product.discountInfo.isActive)
                      Text(
                        '${widget.product.originalPrice.toStringAsFixed(2)} ل.س',
                        style: const TextStyle(
                          fontSize: 15,
                          decoration: TextDecoration.lineThrough,
                          color: Colors.grey,
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 12),

                // حالة التوفر
                Row(
                  children: [
                    Icon(
                      widget.product.stockStatus == 'full'
                          ? Icons.check_circle
                          : Icons.error_outline,
                      color:
                          widget.product.stockStatus == 'full'
                              ? Colors.green
                              : Colors.orange,
                      size: 20,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      widget.product.stockStatus == 'full'
                          ? 'متوفر في المخزن'
                          : 'كمية محدودة',
                      style: TextStyle(
                        color:
                            widget.product.stockStatus == 'full'
                                ? Colors.green[800]
                                : Colors.orange[800],
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // الوصف
                if (widget.product.description?.isNotEmpty ?? false)
                  Padding(
                    padding: const EdgeInsets.only(top: 12, bottom: 12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'الوصف:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                            color: Colors.blue,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.grey[50],
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            widget.product.description!,
                            style: TextStyle(
                              color: Colors.grey[800],
                              height: 1.5,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.shopping_cart),
                      label: const Text('إضافة إلى السلة'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              'تمت إضافة ${widget.product.name} إلى السلة',
                            ),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                // جميع المواصفات
                if (widget.product.attributes.isNotEmpty)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'المواصفات الفنية:',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 8),
                      ...widget.product.attributes.map(
                        (attr) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              SizedBox(
                                width: 120,
                                child: Text(
                                  attr.name,
                                  style: TextStyle(
                                    color: Colors.grey[700],
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ),
                              const Text(
                                ': ',
                                style: TextStyle(fontWeight: FontWeight.bold),
                              ),
                              Expanded(
                                child: Text(
                                  attr.value,
                                  style: TextStyle(color: Colors.grey[800]),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
              ],
            ),
          ),

          // قسم التقييمات
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              border: Border(
                top: BorderSide(color: Colors.grey[200]!, width: 1),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'تقييم المنتج:',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                ),
                const SizedBox(height: 12),
                Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(5, (index) {
                      return IconButton(
                        icon: Icon(
                          index < (_userRating ?? 0)
                              ? Icons.star_rounded
                              : Icons.star_outline_rounded,
                          color: Colors.amber[600],
                          size: 32,
                        ),
                        onPressed: () {
                          if (!widget.isLoggedIn) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'يجب تسجيل الدخول لتتمكن من التقييم',
                                ),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                            return;
                          }
                          setState(() {
                            _userRating = index + 1;
                            _showCommentField = true;
                          });
                        },
                      );
                    }),
                  ),
                ),
                if (_showCommentField) ...[
                  const SizedBox(height: 16),
                  TextField(
                    controller: _commentController,
                    decoration: InputDecoration(
                      labelText: 'تعليقك (اختياري)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      filled: true,
                      fillColor: Colors.white,
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.blue[600],
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: () async {
                            if (_userRating == null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    'الرجاء اختيار تقييم قبل الإرسال',
                                  ),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                              return;
                            }

                            final response = await ProductService.rateProduct(
                              productId: widget.product.id,
                              rating: _userRating!,
                              comment:
                                  _commentController.text.isNotEmpty
                                      ? _commentController.text
                                      : null,
                            );

                            if (response['success']) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    'شكراً لك! تم تسجيل تقييمك بنجاح',
                                  ),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                              setState(() {
                                _showCommentField = false;
                              });
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(response['message']),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          },
                          child: const Text(
                            'تأكيد التقييم',
                            style: TextStyle(color: Colors.white, fontSize: 16),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: () {
                            setState(() {
                              _showCommentField = false;
                              _userRating = null;
                              _commentController.clear();
                            });
                          },
                          child: const Text(
                            'إلغاء',
                            style: TextStyle(fontSize: 16),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }
}
