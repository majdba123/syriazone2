class Category {
  final int id;
  final String title;
  final String percent;
  final DateTime createdAt;
  final DateTime updatedAt;
  final List<SubCategory> subCategories;

  Category({
    required this.id,
    required this.title,
    required this.percent,
    required this.createdAt,
    required this.updatedAt,
    required this.subCategories,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      percent: json['percent'] ?? '0',
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
      updatedAt: DateTime.parse(json['updated_at'] ?? DateTime.now().toString()),
      subCategories: (json['sub_category'] as List<dynamic>?)
          ?.map((subCatJson) => SubCategory.fromJson(subCatJson))
          .toList() ?? <SubCategory>[],
    );
  }
}
class SubCategory {
  final int id;
  final int categoryId;
  final String name;
  final DateTime createdAt;
  final DateTime updatedAt;

  SubCategory({
    required this.id,
    required this.categoryId,
    required this.name,
    required this.createdAt,
    required this.updatedAt,
  });

  factory SubCategory.fromJson(Map<String, dynamic> json) {
    return SubCategory(
      id: json['id'] ?? 0,
      categoryId: json['category_id'] ?? 0,
      name: json['name'] ?? '',
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
      updatedAt: DateTime.parse(json['updated_at'] ?? DateTime.now().toString()),
    );
  }
}
class Attribute {
  final int id;
  final int subCategoryId;
  final String name;

  Attribute({
    required this.id,
    required this.subCategoryId,
    required this.name,
  });

  factory Attribute.fromJson(Map<String, dynamic> json) {
    return Attribute(
      id: json['id'],
      subCategoryId: json['sub__categort_id'],
      name: json['name'],
    );
  }
}
