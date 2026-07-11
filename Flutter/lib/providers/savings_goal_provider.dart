import 'package:flutter/material.dart';
import '../models/savings_goal.dart';
import '../services/savings_goal_service.dart';

class SavingsGoalProvider extends ChangeNotifier {
  final SavingsGoalService _service = SavingsGoalService();

  List<SavingsGoal> _goals = [];
  bool _isLoading = false;
  String? _error;

  List<SavingsGoal> get goals => _goals;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchGoals() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _goals = await _service.getSavingsGoals();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addGoal(Map<String, dynamic> data) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _service.createSavingsGoal(data);
      _isLoading = false;
      await fetchGoals();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateGoal(int id, Map<String, dynamic> data) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _service.updateSavingsGoal(id, data);
      _isLoading = false;
      await fetchGoals();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteGoal(int id) async {
    try {
      await _service.deleteSavingsGoal(id);
      _goals.removeWhere((g) => g.id == id);
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
