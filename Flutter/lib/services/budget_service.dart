import '../models/budget.dart';
import 'api_service.dart';

class BudgetService {
  final ApiService _api = ApiService();

  Future<List<Budget>> getBudgets() async {
    final response = await _api.get('/budgets');
    final data = response.data;
    if (data['success'] == true) {
      final budgetsData = data['data'];
      if (budgetsData is List) {
        return budgetsData.map((e) => Budget.fromJson(e)).toList();
      }
      if (budgetsData is Map && budgetsData.containsKey('data')) {
        return (budgetsData['data'] as List).map((e) => Budget.fromJson(e)).toList();
      }
      return [];
    }
    throw data['message'] ?? 'Gagal memuat budget';
  }

  Future<Budget> createBudget(Map<String, dynamic> budgetData) async {
    final response = await _api.post('/budgets', data: budgetData);
    final data = response.data;
    if (data['success'] == true) {
      return Budget.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal membuat budget';
  }

  Future<Budget> updateBudget(int id, Map<String, dynamic> budgetData) async {
    final response = await _api.put('/budgets/$id', data: budgetData);
    final data = response.data;
    if (data['success'] == true) {
      return Budget.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memperbarui budget';
  }

  Future<void> deleteBudget(int id) async {
    final response = await _api.delete('/budgets/$id');
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal menghapus budget';
    }
  }
}
