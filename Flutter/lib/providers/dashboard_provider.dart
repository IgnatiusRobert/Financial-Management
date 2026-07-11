import 'package:flutter/material.dart';
import '../models/dashboard_data.dart';
import '../services/dashboard_service.dart';
import '../services/ai_tips_service.dart';

class DashboardProvider extends ChangeNotifier {
  final DashboardService _service = DashboardService();
  final AiTipsService _tipsService = AiTipsService();

  DashboardData? _dashboardData;
  bool _isLoading = false;
  String? _error;

  // AI Tips
  List<Map<String, dynamic>> _aiTips = [];
  bool _isTipsLoading = false;

  // Filter state
  String _currentFilter = 'monthly';
  String? _startDate;
  String? _endDate;

  DashboardData? get dashboardData => _dashboardData;
  bool get isLoading => _isLoading;
  String? get error => _error;
  List<Map<String, dynamic>> get aiTips => _aiTips;
  bool get isTipsLoading => _isTipsLoading;
  String get currentFilter => _currentFilter;
  String? get startDate => _startDate;
  String? get endDate => _endDate;

  void setFilter(String filter, {String? start, String? end}) {
    _currentFilter = filter;
    _startDate = start;
    _endDate = end;
    fetchDashboardData();
  }

  Future<void> fetchDashboardData({bool refreshTips = false}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _dashboardData = await _service.getDashboard(
        filter: _currentFilter,
        startDate: _startDate,
        endDate: _endDate,
      );
      _isTipsLoading = true;
      notifyListeners();

      try {
        _aiTips = await _tipsService.getTips(refresh: refreshTips);
      } catch (e) {
        // If AI tips fail, we don't fail the whole dashboard
        debugPrint('Failed to load AI tips: $e');
      }

      _isTipsLoading = false;
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      _isTipsLoading = false;
      notifyListeners();
    }
  }

  Future<void> refresh() async {
    await fetchDashboardData(refreshTips: true);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
