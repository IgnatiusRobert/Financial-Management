import 'transaction.dart';
import 'savings_goal.dart';

class DashboardData {
  final double? saldoSaatIni;
  final double? pemasukan;
  final double? pengeluaran;
  final double? tabungan;
  final double? healthScore;
  final List<TopExpense> topExpenses;
  final List<Transaction> recentTransactions;
  final List<SavingsGoal> savingsGoals;
  final double? budgetUtilization;

  DashboardData({
    this.saldoSaatIni,
    this.pemasukan,
    this.pengeluaran,
    this.tabungan,
    this.healthScore,
    required this.topExpenses,
    required this.recentTransactions,
    required this.savingsGoals,
    this.budgetUtilization,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    return DashboardData(
      saldoSaatIni: json['saldo_saat_ini'] != null
          ? double.tryParse(json['saldo_saat_ini'].toString())
          : 0,
      pemasukan: json['pemasukan'] != null
          ? double.tryParse(json['pemasukan'].toString())
          : 0,
      pengeluaran: json['pengeluaran'] != null
          ? double.tryParse(json['pengeluaran'].toString())
          : 0,
      tabungan: json['tabungan'] != null
          ? double.tryParse(json['tabungan'].toString())
          : 0,
      healthScore: json['health_score'] != null
          ? double.tryParse(json['health_score'].toString())
          : 0,
      topExpenses: json['top_expenses'] != null
          ? (json['top_expenses'] as List).map((e) => TopExpense.fromJson(e)).toList()
          : [],
      recentTransactions: json['recent_transactions'] != null
          ? (json['recent_transactions'] as List).map((e) => Transaction.fromJson(e)).toList()
          : [],
      savingsGoals: json['savings_goals'] != null
          ? (json['savings_goals'] as List).map((e) => SavingsGoal.fromJson(e)).toList()
          : [],
      budgetUtilization: json['budget_utilization'] != null
          ? double.tryParse(json['budget_utilization'].toString())
          : 0,
    );
  }
}

class TopExpense {
  final int? categoryId;
  final String? categoryName;
  final String? categoryColor;
  final double? total;

  TopExpense({this.categoryId, this.categoryName, this.categoryColor, this.total});

  factory TopExpense.fromJson(Map<String, dynamic> json) {
    return TopExpense(
      categoryId: json['category_id'] is int ? json['category_id'] : int.tryParse(json['category_id']?.toString() ?? ''),
      categoryName: json['category_name'],
      categoryColor: json['category_color'],
      total: json['total'] != null ? double.tryParse(json['total'].toString()) : 0,
    );
  }
}
