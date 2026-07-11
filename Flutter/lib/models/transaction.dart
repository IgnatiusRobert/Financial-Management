import 'category.dart';

class Transaction {
  final int? id;
  final int? userId;
  final double? amount;
  final String? type;
  final int? categoryId;
  final String? description;
  final String? date;
  final String? status;
  final Category? category;
  final String? createdAt;
  final String? updatedAt;

  Transaction({
    this.id,
    this.userId,
    this.amount,
    this.type,
    this.categoryId,
    this.description,
    this.date,
    this.status,
    this.category,
    this.createdAt,
    this.updatedAt,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: json['id'],
      userId: json['user_id'],
      amount: json['amount'] != null ? double.tryParse(json['amount'].toString()) : null,
      type: json['type'],
      categoryId: json['category_id'],
      description: json['description'],
      date: json['date'],
      status: json['status'],
      category: json['category'] != null ? Category.fromJson(json['category']) : null,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'amount': amount,
      'type': type,
      'category_id': categoryId,
      'description': description,
      'date': date,
    };
  }

  bool get isIncome => type == 'income';
  bool get isExpense => type == 'expense';
}
