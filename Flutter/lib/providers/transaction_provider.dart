import 'package:flutter/material.dart';
import '../models/transaction.dart';
import '../services/transaction_service.dart';

class TransactionProvider extends ChangeNotifier {
  final TransactionService _service = TransactionService();

  List<Transaction> _transactions = [];
  bool _isLoading = false;
  int _currentPage = 1;
  int _lastPage = 1;
  bool _hasMore = true;
  String? _error;
  String? _currentType;
  String? _searchQuery;

  List<Transaction> get transactions => _transactions;
  bool get isLoading => _isLoading;
  int get currentPage => _currentPage;
  bool get hasMore => _hasMore;
  String? get error => _error;
  String? get currentType => _currentType;

  Future<void> fetchTransactions({String? type, String? search, bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
      _transactions = [];
    }

    _currentType = type;
    _searchQuery = search;
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await _service.getTransactions(
        page: _currentPage,
        type: type,
        search: search,
      );
      final newTransactions = result['transactions'] as List<Transaction>;
      _lastPage = result['last_page'] as int;
      _currentPage = result['current_page'] as int;
      _hasMore = _currentPage < _lastPage;

      if (refresh || _currentPage == 1) {
        _transactions = newTransactions;
      } else {
        _transactions.addAll(newTransactions);
      }

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMore() async {
    if (!_hasMore || _isLoading) return;
    _currentPage++;
    await fetchTransactions(type: _currentType, search: _searchQuery);
  }

  Future<bool> addTransaction(Map<String, dynamic> data) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _service.createTransaction(data);
      _isLoading = false;
      await fetchTransactions(type: _currentType, search: _searchQuery, refresh: true);
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateTransaction(int id, Map<String, dynamic> data) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _service.updateTransaction(id, data);
      _isLoading = false;
      await fetchTransactions(type: _currentType, search: _searchQuery, refresh: true);
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteTransaction(int id) async {
    try {
      await _service.deleteTransaction(id);
      _transactions.removeWhere((t) => t.id == id);
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
