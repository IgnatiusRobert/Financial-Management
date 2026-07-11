class User {
  final int? id;
  final String? name;
  final String? email;
  final String? phone;
  final String? avatar;
  final String? role;
  final Map<String, dynamic>? preferences;
  final String? createdAt;

  User({
    this.id,
    this.name,
    this.email,
    this.phone,
    this.avatar,
    this.role,
    this.preferences,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      avatar: json['avatar'],
      role: json['role'],
      preferences: json['preferences'] is Map ? Map<String, dynamic>.from(json['preferences']) : null,
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'avatar': avatar,
      'role': role,
      'preferences': preferences,
    };
  }

  String get initials {
    if (name == null || name!.isEmpty) return '?';
    final parts = name!.split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name![0].toUpperCase();
  }

  String? get avatarUrl {
    if (avatar == null || avatar!.isEmpty) return null;
    if (avatar!.startsWith('http')) return avatar;
    // Extract base domain from AppConfig.baseUrl if needed, or hardcode relative path
    return 'http://10.83.36.95:8080/storage/$avatar';
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? avatar,
    String? role,
    Map<String, dynamic>? preferences,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatar: avatar ?? this.avatar,
      role: role ?? this.role,
      preferences: preferences ?? this.preferences,
      createdAt: createdAt,
    );
  }
}
