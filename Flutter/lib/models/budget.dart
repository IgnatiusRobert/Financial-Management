import 'category.dart';

class Budget {
  final int? id;
  final int? userId;
  final double? amount;
  final String? period;
  final int? categoryId;
  final Category? category;
  final double? spent;
  final double? percentage;
  final String? startDate;
  final String? endDate;
  final String? createdAt;

  Budget({
    this.id,
    this.userId,
    this.amount,
    this.period,
    this.categoryId,
    this.category,
    this.spent,
    this.percentage,
    this.startDate,
    this.endDate,
    this.createdAt,
  });

  factory Budget.fromJson(Map<String, dynamic> json) {
    return Budget(
      id: json['id'],
      userId: json['user_id'],
      amount: json['amount'] != null ? double.tryParse(json['amount'].toString()) : null,
      period: json['period'],
      categoryId: json['category_id'],
      category: json['category'] != null ? Category.fromJson(json['category']) : null,
      spent: json['spent'] != null ? double.tryParse(json['spent'].toString()) : 0,
      percentage: json['percentage'] != null ? double.tryParse(json['percentage'].toString()) : 0,
      startDate: json['start_date'],
      endDate: json['end_date'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'amount': amount,
      'period': period,
      'category_id': categoryId,
    };
  }

  double get spentPercentage {
    if (amount == null || amount == 0) return 0;
    return ((spent ?? 0) / amount!) * 100;
  }

  double get remaining {
    return (amount ?? 0) - (spent ?? 0);
  }
}
