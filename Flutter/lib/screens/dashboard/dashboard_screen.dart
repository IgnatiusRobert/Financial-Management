import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../config/app_routes.dart';
import '../../providers/auth_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/health_score_widget.dart';
import '../../widgets/transaction_tile.dart';
import '../../widgets/savings_card.dart';
import '../../widgets/loading_widget.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../helpers/currency_helper.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<DashboardProvider>(context, listen: false).fetchDashboardData();
        Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
      }
    });
  }

  Future<void> _refreshData() async {
    await Provider.of<DashboardProvider>(context, listen: false).fetchDashboardData(refreshTips: true);
    await Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
  }

  Future<void> _selectCustomDateRange(DashboardProvider provider) async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (picked != null) {
      final start = DateFormat('yyyy-MM-dd').format(picked.start);
      final end = DateFormat('yyyy-MM-dd').format(picked.end);
      provider.setFilter('custom', start: start, end: end);
    }
  }

  void _showDownloadReportBottomSheet(DashboardProvider provider) {
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Padding(
                padding: EdgeInsets.all(16.0),
                child: Text('Pilih Format Unduh', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
              ListTile(
                leading: const Icon(Icons.picture_as_pdf, color: Colors.red),
                title: const Text('Unduh PDF'),
                onTap: () {
                  Navigator.pop(context);
                  _downloadReport(provider, 'pdf');
                },
              ),
              ListTile(
                leading: const Icon(Icons.table_view, color: Colors.green),
                title: const Text('Unduh Excel'),
                onTap: () {
                  Navigator.pop(context);
                  _downloadReport(provider, 'excel');
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _downloadReport(DashboardProvider provider, String format) async {
    try {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mengunduh laporan...')));
      final dir = await getApplicationDocumentsDirectory();
      final ext = format == 'excel' ? 'xlsx' : 'pdf';
      final fileName = 'laporan_keuangan_${DateTime.now().millisecondsSinceEpoch}.$ext';
      final savePath = '${dir.path}/$fileName';

      final queryParams = {
        'format': format,
        'filter': provider.currentFilter,
        if (provider.startDate != null) 'start_date': provider.startDate,
        if (provider.endDate != null) 'end_date': provider.endDate,
      };

      await ApiService().dio.download('/reports/export', savePath, queryParameters: queryParams);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Berhasil mengunduh ke $savePath')));
        OpenFilex.open(savePath);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal mengunduh laporan')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final dashboardProvider = Provider.of<DashboardProvider>(context);
    final notificationProvider = Provider.of<NotificationProvider>(context);

    final user = authProvider.user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            CircleAvatar(
              backgroundColor: isDark ? AppColors.primaryLight.withOpacity(0.2) : AppColors.primary.withOpacity(0.1),
              child: Text(
                user?.initials ?? 'U',
                style: TextStyle(
                  color: isDark ? AppColors.primaryLight : AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Halo,',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                ),
                Text(
                  user?.name ?? 'Pengguna',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_none_outlined),
                onPressed: () {
                  Navigator.pushNamed(context, AppRoutes.notifications);
                },
              ),
              if (notificationProvider.unreadCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: AppColors.expense,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      '${notificationProvider.unreadCount}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.download_rounded),
            onPressed: () {
              _showDownloadReportBottomSheet(dashboardProvider);
            },
          ),
          IconButton(
            icon: const Icon(Icons.bar_chart_outlined),
            onPressed: () {
              Navigator.pushNamed(context, AppRoutes.reports);
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: dashboardProvider.isLoading
          ? const LoadingWidget()
          : RefreshIndicator(
              onRefresh: _refreshData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Period Filter
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip(dashboardProvider, 'Hari Ini', 'daily'),
                          const SizedBox(width: 8),
                          _buildFilterChip(dashboardProvider, 'Minggu Ini', 'weekly'),
                          const SizedBox(width: 8),
                          _buildFilterChip(dashboardProvider, 'Bulan Ini', 'monthly'),
                          const SizedBox(width: 8),
                          _buildFilterChip(dashboardProvider, 'Tahun Ini', 'yearly'),
                          const SizedBox(width: 8),
                          ActionChip(
                            label: Text(dashboardProvider.currentFilter == 'custom'
                                ? '${dashboardProvider.startDate} - ${dashboardProvider.endDate}'
                                : 'Rentang Tanggal'),
                            onPressed: () => _selectCustomDateRange(dashboardProvider),
                            backgroundColor: dashboardProvider.currentFilter == 'custom'
                                ? AppColors.primary.withOpacity(0.1)
                                : null,
                            side: dashboardProvider.currentFilter == 'custom'
                                ? const BorderSide(color: AppColors.primary)
                                : null,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Stat Cards Grid
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      childAspectRatio: 1.15,
                      children: [
                        StatCard(
                          label: 'Saldo Saat Ini',
                          value: CurrencyHelper.format(dashboardProvider.dashboardData?.saldoSaatIni ?? 0),
                          gradient: AppColors.balanceGradient,
                          icon: Icons.account_balance_wallet_rounded,
                        ),
                        StatCard(
                          label: 'Pemasukan',
                          value: CurrencyHelper.format(dashboardProvider.dashboardData?.pemasukan ?? 0),
                          gradient: AppColors.incomeGradient,
                          icon: Icons.trending_up_rounded,
                        ),
                        StatCard(
                          label: 'Pengeluaran',
                          value: CurrencyHelper.format(dashboardProvider.dashboardData?.pengeluaran ?? 0),
                          gradient: AppColors.expenseGradient,
                          icon: Icons.trending_down_rounded,
                        ),
                        StatCard(
                          label: 'Anggaran Terkumpul',
                          value: CurrencyHelper.format(dashboardProvider.dashboardData?.tabungan ?? 0),
                          gradient: AppColors.savingsGradient,
                          icon: Icons.savings_rounded,
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Health Score
                    HealthScoreWidget(
                      score: (dashboardProvider.dashboardData?.healthScore ?? 50.0).toDouble(),
                    ),
                    const SizedBox(height: 24),

                    // ✅ Budget Utilization widget dihapus dari sini

                    // AI Tips Section
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Rekomendasi AI Keuangan',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.refresh, size: 20),
                          onPressed: () => dashboardProvider.fetchDashboardData(refreshTips: true),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    _buildAiTipsList(dashboardProvider, isDark),
                    const SizedBox(height: 24),

                    // Savings Goals
                    if (dashboardProvider.dashboardData?.savingsGoals.isNotEmpty ?? false) ...[
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Target Anggaran',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                          ),
                          GestureDetector(
                            onTap: () {
                              Navigator.pushNamed(context, AppRoutes.savings);
                            },
                            child: Text(
                              'Lihat Semua',
                              style: TextStyle(
                                color: isDark ? AppColors.primaryLight : AppColors.primary,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 140,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: dashboardProvider.dashboardData?.savingsGoals.length ?? 0,
                          itemBuilder: (context, index) {
                            final goal = dashboardProvider.dashboardData!.savingsGoals[index];
                            return Container(
                              width: 280,
                              margin: const EdgeInsets.only(right: 16),
                              child: SavingsCard(goal: goal),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],

                    // Top Expenses
                    if (dashboardProvider.dashboardData?.topExpenses.isNotEmpty ?? false) ...[
                      Text(
                        'Pengeluaran Terbesar Kategori',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 12),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            children: dashboardProvider.dashboardData!.topExpenses.map((expense) {
                              final double spent = expense.total ?? 0;
                              final double totalExpense = dashboardProvider.dashboardData?.pengeluaran ?? 1;
                              final double percentage = totalExpense > 0 ? (spent / totalExpense) : 0;
                              final String catName = expense.categoryName ?? 'Lainnya';
                              final String catColor = expense.categoryColor ?? 'grey';

                              return Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                child: Column(
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          catName,
                                          style: const TextStyle(fontWeight: FontWeight.w600),
                                        ),
                                        Text(
                                          CurrencyHelper.format(spent),
                                          style: const TextStyle(fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    LinearProgressIndicator(
                                      value: percentage,
                                      backgroundColor: isDark ? Colors.grey[800] : Colors.grey[200],
                                      valueColor: AlwaysStoppedAnimation<Color>(_parseColor(catColor)),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],

                    // Recent Transactions
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Transaksi Terakhir',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                        GestureDetector(
                          onTap: () {
                            Navigator.pushNamed(context, AppRoutes.transactions);
                          },
                          child: Text(
                            'Lihat Semua',
                            style: TextStyle(
                              color: isDark ? AppColors.primaryLight : AppColors.primary,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    if (dashboardProvider.dashboardData?.recentTransactions.isEmpty ?? true)
                      const Card(
                        child: Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(
                            child: Text('Belum ada transaksi bulan ini.'),
                          ),
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: dashboardProvider.dashboardData?.recentTransactions.length ?? 0,
                        itemBuilder: (context, index) {
                          final tx = dashboardProvider.dashboardData!.recentTransactions[index];
                          return TransactionTile(
                            transaction: tx,
                            onTap: () {
                              Navigator.pushNamed(
                                context,
                                AppRoutes.transactionForm,
                                arguments: tx,
                              );
                            },
                          );
                        },
                      ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
    );
  }

  String _getHealthDescription(int score) {
    if (score >= 80) {
      return 'Luar biasa! Keuangan Anda sangat sehat. Pertahankan rasio menabung Anda.';
    } else if (score >= 60) {
      return 'Kondisi cukup stabil. Cari cara menekan pengeluaran kecil agar tabungan bertambah.';
    } else if (score >= 40) {
      return 'Hati-hati. Pengeluaran Anda hampir setara pemasukan. Evaluasi anggaran bulanan.';
    } else {
      return 'Kritis! Segera batasi pengeluaran non-prioritas dan buat budget darurat.';
    }
  }

  Color _parseColor(String colorStr) {
    switch (colorStr.toLowerCase()) {
      case 'red':
        return Colors.red;
      case 'green':
        return Colors.green;
      case 'blue':
        return Colors.blue;
      case 'yellow':
        return Colors.amber;
      case 'indigo':
        return Colors.indigo;
      case 'purple':
        return Colors.purple;
      case 'orange':
        return Colors.orange;
      case 'pink':
        return Colors.pink;
      case 'teal':
        return Colors.teal;
      default:
        return Colors.grey;
    }
  }

  Widget _buildAiTipsList(DashboardProvider provider, bool isDark) {
    final tips = provider.aiTips;
    if (tips.isEmpty) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Center(
            child: Text('Tidak ada rekomendasi saat ini. Tarik ke bawah untuk memuat ulang.'),
          ),
        ),
      );
    }

    return SizedBox(
      height: 120,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: tips.length,
        itemBuilder: (context, index) {
          final tip = tips[index];
          return Container(
            width: 280,
            margin: const EdgeInsets.only(right: 16),
            child: Card(
              color: isDark ? AppColors.darkCard : Colors.purple[50],
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.lightbulb_outline_rounded,
                          color: isDark ? AppColors.primaryLight : AppColors.primary,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            tip['title'] ?? 'Tips Finansial',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      tip['text'] ?? '',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            fontSize: 12,
                          ),
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildFilterChip(DashboardProvider provider, String label, String filterValue) {
    final isSelected = provider.currentFilter == filterValue;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          provider.setFilter(filterValue);
        }
      },
      selectedColor: AppColors.primary.withOpacity(0.2),
      side: isSelected ? const BorderSide(color: AppColors.primary) : null,
    );
  }
}