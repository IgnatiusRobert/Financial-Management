import '../models/transaction.dart';
import 'api_service.dart';

class TransactionService {
  final ApiService _api = ApiService();

  Future<Map<String, dynamic>> getTransactions({
    int page = 1,
    String? type,
    String? search,
  }) async {
    final Map<String, dynamic> params = {'page': page};
    if (type != null && type.isNotEmpty) params['type'] = type;
    if (search != null && search.isNotEmpty) params['search'] = search;

    final response = await _api.get('/transactions', queryParameters: params);
    final data = response.data;
    if (data['success'] == true) {
      final transactionsData = data['data'];
      List<Transaction> transactions = [];

      if (transactionsData is Map && transactionsData.containsKey('data')) {
        transactions = (transactionsData['data'] as List)
            .map((e) => Transaction.fromJson(e))
            .toList();
      } else if (transactionsData is List) {
        transactions = transactionsData.map((e) => Transaction.fromJson(e)).toList();
      }

      final lastPage = transactionsData is Map ? (transactionsData['last_page'] ?? 1) : 1;
      final currentPage = transactionsData is Map ? (transactionsData['current_page'] ?? 1) : 1;

      return {
        'transactions': transactions,
        'current_page': currentPage,
        'last_page': lastPage,
      };
    }
    throw data['message'] ?? 'Gagal memuat transaksi';
  }

  Future<Transaction> createTransaction(Map<String, dynamic> transactionData) async {
    final response = await _api.post('/transactions', data: transactionData);
    final data = response.data;
    if (data['success'] == true) {
      return Transaction.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal membuat transaksi';
  }

  Future<Transaction> updateTransaction(int id, Map<String, dynamic> transactionData) async {
    final response = await _api.put('/transactions/$id', data: transactionData);
    final data = response.data;
    if (data['success'] == true) {
      return Transaction.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memperbarui transaksi';
  }

  Future<void> deleteTransaction(int id) async {
    final response = await _api.delete('/transactions/$id');
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal menghapus transaksi';
    }
  }
}
