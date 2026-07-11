import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/app_theme.dart';
import '../config/app_routes.dart';
import '../providers/notification_provider.dart';
import 'dashboard/dashboard_screen.dart';
import 'transactions/transaction_list_screen.dart';
import 'budgets/budget_list_screen.dart';
import 'savings/savings_list_screen.dart';
import 'profile/profile_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    const DashboardScreen(),
    const TransactionListScreen(),
    const SavingsListScreen(),
    const ProfileScreen(),
  ];

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final unreadNotifications = Provider.of<NotificationProvider>(context).unreadCount;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: IndexedStack(
        index: _selectedIndex,
        children: _screens,
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.pushNamed(context, AppRoutes.transactionForm);
        },
        child: const Icon(Icons.add, size: 28),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: 8.0,
        clipBehavior: Clip.antiAlias,
        child: SizedBox(
          height: 60,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Left side tabs
              Expanded(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    IconButton(
                      icon: Icon(
                        Icons.dashboard_outlined,
                        color: _selectedIndex == 0
                            ? (isDark ? AppColors.primaryLight : AppColors.primary)
                            : Colors.grey,
                      ),
                      onPressed: () => _onItemTapped(0),
                      tooltip: 'Dashboard',
                    ),
                    IconButton(
                      icon: Icon(
                        Icons.receipt_long_outlined,
                        color: _selectedIndex == 1
                            ? (isDark ? AppColors.primaryLight : AppColors.primary)
                            : Colors.grey,
                      ),
                      onPressed: () => _onItemTapped(1),
                      tooltip: 'Transaksi',
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 48), // Spacer for center FAB
              // Right side tabs
              Expanded(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    IconButton(
                      icon: Icon(
                        Icons.pie_chart_outline,
                        color: _selectedIndex == 2
                            ? (isDark ? AppColors.primaryLight : AppColors.primary)
                            : Colors.grey,
                      ),
                      onPressed: () => _onItemTapped(2),
                      tooltip: 'Anggaran',
                    ),
                    IconButton(
                      icon: Icon(
                        Icons.person_outline,
                        color: _selectedIndex == 3
                            ? (isDark ? AppColors.primaryLight : AppColors.primary)
                            : Colors.grey,
                      ),
                      onPressed: () => _onItemTapped(3),
                      tooltip: 'Profil',
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
