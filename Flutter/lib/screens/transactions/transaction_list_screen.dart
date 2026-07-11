import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import '../../config/app_theme.dart';
import '../../config/app_routes.dart';
import '../../providers/transaction_provider.dart';
import '../../widgets/transaction_tile.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/loading_widget.dart';

class TransactionListScreen extends StatefulWidget {
  const TransactionListScreen({super.key});

  @override
  State<TransactionListScreen> createState() => _TransactionListScreenState();
}

class _TransactionListScreenState extends State<TransactionListScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  String _selectedType = ''; // '' for all, 'income', 'expense'

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<TransactionProvider>(context, listen: false).fetchTransactions(refresh: true);
      }
    });

    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      final txProvider = Provider.of<TransactionProvider>(context, listen: false);
      if (txProvider.hasMore && !txProvider.isLoading) {
        txProvider.loadMore();
      }
    }
  }

  Future<void> _refresh() async {
    await Provider.of<TransactionProvider>(context, listen: false).fetchTransactions(
      refresh: true,
      type: _selectedType,
      search: _searchController.text.trim(),
    );
  }

  void _onTypeFilterChanged(String type) {
    setState(() {
      _selectedType = type;
    });
    Provider.of<TransactionProvider>(context, listen: false).fetchTransactions(
      refresh: true,
      type: type,
      search: _searchController.text.trim(),
    );
  }

  void _onSearch() {
    Provider.of<TransactionProvider>(context, listen: false).fetchTransactions(
      refresh: true,
      type: _selectedType,
      search: _searchController.text.trim(),
    );
  }

  Future<void> _deleteTransaction(int id) async {
    final success = await Provider.of<TransactionProvider>(context, listen: false).deleteTransaction(id);
    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Transaksi berhasil dihapus'),
          backgroundColor: AppColors.income,
        ),
      );
    } else {
      final error = Provider.of<TransactionProvider>(context, listen: false).error;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error ?? 'Gagal menghapus transaksi'),
          backgroundColor: AppColors.expense,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final txProvider = Provider.of<TransactionProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Riwayat Transaksi'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(110),
          child: Column(
            children: [
              // Search Bar
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: TextField(
                  controller: _searchController,
                  onSubmitted: (_) => _onSearch(),
                  decoration: InputDecoration(
                    hintText: 'Cari deskripsi transaksi...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                              _onSearch();
                            },
                          )
                        : null,
                    contentPadding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              // Filter Chips
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Row(
                  children: [
                    FilterChip(
                      label: const Text('Semua'),
                      selected: _selectedType == '',
                      onSelected: (_) => _onTypeFilterChanged(''),
                    ),
                    const SizedBox(width: 8),
                    FilterChip(
                      label: const Text('Pemasukan'),
                      selected: _selectedType == 'income',
                      onSelected: (_) => _onTypeFilterChanged('income'),
                    ),
                    const SizedBox(width: 8),
                    FilterChip(
                      label: const Text('Pengeluaran'),
                      selected: _selectedType == 'expense',
                      onSelected: (_) => _onTypeFilterChanged('expense'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
      body: txProvider.isLoading && txProvider.transactions.isEmpty
          ? const LoadingWidget()
          : RefreshIndicator(
              onRefresh: _refresh,
              child: txProvider.transactions.isEmpty
                  ? const EmptyState(
                      icon: Icons.receipt_long_rounded,
                      title: 'Tidak Ada Transaksi',
                      subtitle: 'Silakan tambahkan transaksi untuk memulai pencatatan.',
                    )
                  : ListView.builder(
                      controller: _scrollController,
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      itemCount: txProvider.transactions.length + (txProvider.hasMore ? 1 : 0),
                      itemBuilder: (context, index) {
                        if (index == txProvider.transactions.length) {
                          return const Padding(
                            padding: EdgeInsets.symmetric(vertical: 16),
                            child: Center(child: CircularProgressIndicator()),
                          );
                        }

                        final tx = txProvider.transactions[index];

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Slidable(
                            key: ValueKey(tx.id),
                            endActionPane: ActionPane(
                              motion: const ScrollMotion(),
                              extentRatio: 0.25,
                              children: [
                                SlidableAction(
                                  onPressed: (_) => _deleteTransaction(tx.id!),
                                  backgroundColor: AppColors.expense,
                                  foregroundColor: Colors.white,
                                  icon: Icons.delete_outline,
                                  label: 'Hapus',
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ],
                            ),
                            child: TransactionTile(
                              transaction: tx,
                              onTap: () {
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.transactionForm,
                                  arguments: tx,
                                );
                              },
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
