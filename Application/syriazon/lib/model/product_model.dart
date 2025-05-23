class Product {
  final int id;
  final String name;
  final String? description;
  final double originalPrice;
  final double finalPrice;
  final String subcategory;
  final int subcategoryId;
  final String stockStatus;
  final String productStatus;
  final List<ProductImage> images;
  final List<ProductAttribute> attributes;
  final DiscountInfo discountInfo;

  Product({
    required this.id,
    required this.name,
    this.description,
    required this.originalPrice,
    required this.finalPrice,
    required this.subcategory,
    required this.subcategoryId,
    required this.stockStatus,
    required this.productStatus,
    required this.images,
    required this.attributes,
    required this.discountInfo,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    try {
      return Product(
        id: json['id'] ?? 0,
        name: json['name'] ?? '',
        description: json['description'],
        originalPrice:
            double.tryParse(json['original_price']?.toString() ?? '0') ?? 0.0,
        finalPrice:
            double.tryParse(json['final_price']?.toString() ?? '0') ?? 0.0,
        subcategory: json['subcategory'] ?? '',
        subcategoryId: json['subcategory_id'] ?? 0,
        stockStatus: json['stock'] ?? 'unknown',
        productStatus: json['status'] ?? 'unknown',
        images:
            (json['images'] as List<dynamic>?)
                ?.map((image) => ProductImage.fromJson(image))
                .toList() ??
            [],
        attributes:
            (json['attributes'] as List<dynamic>?)
                ?.map((attr) => ProductAttribute.fromJson(attr))
                .toList() ??
            [],
        discountInfo:
            json['discount_info'] != null
                ? DiscountInfo.fromJson(json['discount_info'])
                : DiscountInfo(isActive: false),
      );
    } catch (e, stackTrace) {
      print('Error parsing Product: $e');
      print('Stack trace: $stackTrace');
      print('Problematic JSON: $json');
      rethrow;
    }
  }
}

class ProductImage {
  final int id;
  final String url;

  ProductImage({required this.id, required this.url});

  factory ProductImage.fromJson(Map<String, dynamic> json) {
    return ProductImage(id: json['id'] ?? 0, url: json['url'] ?? '');
  }
}

class ProductAttribute {
  final int attributeId;
  final int productAttributeId;
  final String name;
  final String value;

  ProductAttribute({
    required this.attributeId,
    required this.productAttributeId,
    required this.name,
    required this.value,
  });

  factory ProductAttribute.fromJson(Map<String, dynamic> json) {
    return ProductAttribute(
      attributeId: json['attribute_id'] ?? 0,
      productAttributeId: json['product_attributes_id'] ?? 0,
      name: json['name_attributes'] ?? '',
      value: json['value_attributes'] ?? '',
    );
  }
}

class DiscountInfo {
  final bool isActive;

  DiscountInfo({required this.isActive});

  factory DiscountInfo.fromJson(Map<String, dynamic> json) {
    return DiscountInfo(isActive: json['is_discount_active'] ?? false);
  }
}
