class SavingsGoal {
  final int? id;
  final int? userId;
  final String? name;
  final double? targetAmount;
  final double? currentAmount;
  final String? targetDate;
  final double? progressPercentage;
  final String? createdAt;

  SavingsGoal({
    this.id,
    this.userId,
    this.name,
    this.targetAmount,
    this.currentAmount,
    this.targetDate,
    this.progressPercentage,
    this.createdAt,
  });

  factory SavingsGoal.fromJson(Map<String, dynamic> json) {
    return SavingsGoal(
      id: json['id'],
      userId: json['user_id'],
      name: json['name'],
      targetAmount: json['target_amount'] != null ? double.tryParse(json['target_amount'].toString()) : null,
      currentAmount: json['current_amount'] != null ? double.tryParse(json['current_amount'].toString()) : 0,
      targetDate: json['target_date'],
      progressPercentage: json['progress_percentage'] != null
          ? double.tryParse(json['progress_percentage'].toString())
          : null,
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'target_amount': targetAmount,
      'current_amount': currentAmount,
      'target_date': targetDate,
    };
  }

  double get progress {
    if (targetAmount == null || targetAmount == 0) return 0;
    final p = ((currentAmount ?? 0) / targetAmount!) * 100;
    return p > 100 ? 100 : p;
  }

  double get remaining {
    return (targetAmount ?? 0) - (currentAmount ?? 0);
  }
}
