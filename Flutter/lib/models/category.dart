class Category {
  final int? id;
  final String? name;
  final String? icon;
  final String? color;
  final String? type;

  Category({
    this.id,
    this.name,
    this.icon,
    this.color,
    this.type,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      icon: json['icon'],
      color: json['color'],
      type: json['type'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'icon': icon,
      'color': color,
      'type': type,
    };
  }
}
