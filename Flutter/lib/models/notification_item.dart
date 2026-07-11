class NotificationItem {
  final String? id;
  final String? type;
  final String? title;
  final String? message;
  final Map<String, dynamic>? data;
  final String? readAt;
  final String? createdAt;

  NotificationItem({
    this.id,
    this.type,
    this.title,
    this.message,
    this.data,
    this.readAt,
    this.createdAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) {
    return NotificationItem(
      id: json['id']?.toString(),
      type: json['type'],
      title: json['title'],
      message: json['message'],
      data: json['data'] is Map ? Map<String, dynamic>.from(json['data']) : null,
      readAt: json['read_at'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'title': title,
      'message': message,
      'data': data,
    };
  }

  bool get isRead => readAt != null;
  bool get isUnread => readAt == null;
}
