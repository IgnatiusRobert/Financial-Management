import '../models/savings_goal.dart';
import 'api_service.dart';

class SavingsGoalService {
  final ApiService _api = ApiService();

  Future<List<SavingsGoal>> getSavingsGoals() async {
    final response = await _api.get('/savings-goals');
    final data = response.data;
    if (data['success'] == true) {
      final goalsData = data['data'];
      if (goalsData is List) {
        return goalsData.map((e) => SavingsGoal.fromJson(e)).toList();
      }
      if (goalsData is Map && goalsData.containsKey('data')) {
        return (goalsData['data'] as List).map((e) => SavingsGoal.fromJson(e)).toList();
      }
      return [];
    }
    throw data['message'] ?? 'Gagal memuat target tabungan';
  }

  Future<SavingsGoal> createSavingsGoal(Map<String, dynamic> goalData) async {
    final response = await _api.post('/savings-goals', data: goalData);
    final data = response.data;
    if (data['success'] == true) {
      return SavingsGoal.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal membuat target tabungan';
  }

  Future<SavingsGoal> updateSavingsGoal(int id, Map<String, dynamic> goalData) async {
    final response = await _api.put('/savings-goals/$id', data: goalData);
    final data = response.data;
    if (data['success'] == true) {
      return SavingsGoal.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memperbarui target tabungan';
  }

  Future<void> deleteSavingsGoal(int id) async {
    final response = await _api.delete('/savings-goals/$id');
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal menghapus target tabungan';
    }
  }
}
