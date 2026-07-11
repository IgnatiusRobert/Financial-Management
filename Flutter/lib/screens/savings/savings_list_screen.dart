import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import '../../config/app_theme.dart';
import '../../config/app_routes.dart';
import '../../providers/savings_goal_provider.dart';
import '../../widgets/savings_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/loading_widget.dart';

class SavingsListScreen extends StatefulWidget {
  const SavingsListScreen({super.key});

  @override
  State<SavingsListScreen> createState() => _SavingsListScreenState();
}

class _SavingsListScreenState extends State<SavingsListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<SavingsGoalProvider>(context, listen: false).fetchGoals();
      }
    });
  }

  Future<void> _refresh() async {
    await Provider.of<SavingsGoalProvider>(context, listen: false).fetchGoals();
  }

  Future<void> _deleteGoal(int id) async {
    final success = await Provider.of<SavingsGoalProvider>(context, listen: false).deleteGoal(id);
    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Anggaran berhasil dihapus'),
          backgroundColor: AppColors.income,
        ),
      );
    } else {
      final error = Provider.of<SavingsGoalProvider>(context, listen: false).error;
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
    final goalProvider = Provider.of<SavingsGoalProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Anggaran'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              Navigator.pushNamed(context, AppRoutes.savingsForm);
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: goalProvider.isLoading && goalProvider.goals.isEmpty
          ? const LoadingWidget()
          : RefreshIndicator(
              onRefresh: _refresh,
              child: goalProvider.goals.isEmpty
                  ? const EmptyState(
                      icon: Icons.savings_rounded,
                      title: 'Belum Ada Anggaran',
                      subtitle: 'Lacak pencapaian target anggaran Anda (Dana Darurat, Pendidikan, dll).',
                    )
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(20),
                      itemCount: goalProvider.goals.length,
                      itemBuilder: (context, index) {
                        final goal = goalProvider.goals[index];
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 16),
                          child: Slidable(
                            key: ValueKey(goal.id),
                            endActionPane: ActionPane(
                              motion: const ScrollMotion(),
                              extentRatio: 0.25,
                              children: [
                                SlidableAction(
                                  onPressed: (_) => _deleteGoal(goal.id!),
                                  backgroundColor: AppColors.expense,
                                  foregroundColor: Colors.white,
                                  icon: Icons.delete_outline,
                                  label: 'Hapus',
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ],
                            ),
                            child: SavingsCard(
                              goal: goal,
                              onTap: () {
                                Navigator.pushNamed(
                                  context,
                                  AppRoutes.savingsForm,
                                  arguments: goal,
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
          Navigator.pushNamed(context, AppRoutes.savingsForm);
        },
        heroTag: 'add_savings_btn',
        child: const Icon(Icons.add),
      ),
    );
  }
}
