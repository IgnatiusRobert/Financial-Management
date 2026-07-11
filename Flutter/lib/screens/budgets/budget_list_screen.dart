import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import '../../config/app_theme.dart';
import '../../config/app_routes.dart';
import '../../providers/budget_provider.dart';
import '../../widgets/budget_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/loading_widget.dart';

class BudgetListScreen extends StatefulWidget {
  const BudgetListScreen({super.key});

  @override
  State<BudgetListScreen> createState() => _BudgetListScreenState();
}

class _BudgetListScreenState extends State<BudgetListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<BudgetProvider>(context, listen: false).fetchBudgets();
      }
    });
  }

  Future<void> _refresh() async {
    await Provider.of<BudgetProvider>(context, listen: false).fetchBudgets();
  }

  Future<void> _deleteBudget(int id) async {
    final success = await Provider.of<BudgetProvider>(context, listen: false).deleteBudget(id);
    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Anggaran berhasil dihapus'),
          backgroundColor: AppColors.income,
        ),
      );
    } else {
      final error = Provider.of<BudgetProvider>(context, listen: false).error;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error ?? 'Gagal menghapus anggaran'),
          backgroundColor: AppColors.expense,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final budgetProvider = Provider.of<BudgetProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Batas Pengeluaran Kategori'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              Navigator.pushNamed(context, AppRoutes.budgetForm);
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: budgetProvider.isLoading && budgetProvider.budgets.isEmpty
          ? const LoadingWidget()
          : RefreshIndicator(
              onRefresh: _refresh,
              child: budgetProvider.budgets.isEmpty
                  ? const EmptyState(
                      icon: Icons.wallet_rounded,
                      title: 'Belum Ada Batasan',
                      subtitle: 'Buat batas pengeluaran untuk membatasi pengeluaran per kategori.',
                    )
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(20),
                      itemCount: budgetProvider.budgets.length,
                      itemBuilder: (context, index) {
                        final budget = budgetProvider.budgets[index];
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Slidable(
                            key: ValueKey(budget.id),
                            endActionPane: ActionPane(
                              motion: const ScrollMotion(),
                              extentRatio: 0.25,
                              children: [
                                SlidableAction(
                                  onPressed: (_) => _deleteBudget(budget.id!),
                                  backgroundColor: AppColors.expense,
                                  foregroundColor: Colors.white,
                                  icon: Icons.delete_outline,
                                  label: 'Hapus',
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ],
                            ),
                            child: BudgetCard(
                              budget: budget,
                              onTap: () {
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.budgetForm,
                                  arguments: budget,
                                );
                              },
                            ),
                          ),
                        );
                      },
                    ),
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.pushNamed(context, AppRoutes.budgetForm);
        },
        heroTag: 'add_budget_btn',
        child: const Icon(Icons.add),
      ),
    );
  }
}
