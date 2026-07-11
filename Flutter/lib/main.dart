import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'config/app_theme.dart';
import 'config/app_routes.dart';
import 'providers/auth_provider.dart';
import 'providers/transaction_provider.dart';
import 'providers/budget_provider.dart';
import 'providers/savings_goal_provider.dart';
import 'providers/dashboard_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/theme_provider.dart';
import 'services/api_service.dart';

// Import Screens
import 'screens/splash_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/main_screen.dart';
import 'screens/transactions/transaction_list_screen.dart';
import 'screens/transactions/transaction_form_screen.dart';
import 'screens/budgets/budget_list_screen.dart';
import 'screens/budgets/budget_form_screen.dart';
import 'screens/savings/savings_list_screen.dart';
import 'screens/savings/savings_form_screen.dart';
import 'screens/reports/report_screen.dart';
import 'screens/notifications/notification_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final GlobalKey<NavigatorState> _navigatorKey = GlobalKey<NavigatorState>();

  @override
  void initState() {
    super.initState();
    // Setup global unauthorized interceptor callback (auto logout on 401)
    ApiService().onUnauthorized = () {
      if (_navigatorKey.currentState != null) {
        _navigatorKey.currentState!.pushNamedAndRemoveUntil(
          AppRoutes.login,
          (route) => false,
        );
      }
    };
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => TransactionProvider()),
        ChangeNotifierProvider(create: (_) => BudgetProvider()),
        ChangeNotifierProvider(create: (_) => SavingsGoalProvider()),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: Consumer<ThemeProvider>(
        builder: (context, themeProvider, child) {
          return MaterialApp(
            title: 'Manajemen Keuangan',
            debugShowCheckedModeBanner: false,
            navigatorKey: _navigatorKey,
            themeMode: themeProvider.isDarkMode ? ThemeMode.dark : ThemeMode.light,
            theme: AppTheme.lightTheme(),
            darkTheme: AppTheme.darkTheme(),
            initialRoute: AppRoutes.splash,
            routes: {
              AppRoutes.splash: (context) => const SplashScreen(),
              AppRoutes.login: (context) => const LoginScreen(),
              AppRoutes.register: (context) => const RegisterScreen(),
              AppRoutes.main: (context) => const MainScreen(),
              AppRoutes.transactions: (context) => const TransactionListScreen(),
              AppRoutes.transactionForm: (context) => const TransactionFormScreen(),
              AppRoutes.budgets: (context) => const BudgetListScreen(),
              AppRoutes.budgetForm: (context) => const BudgetFormScreen(),
              AppRoutes.savings: (context) => const SavingsListScreen(),
              AppRoutes.savingsForm: (context) => const SavingsFormScreen(),
              AppRoutes.reports: (context) => const ReportScreen(),
              AppRoutes.notifications: (context) => const NotificationScreen(),
            },
          );
        },
      ),
    );
  }
}
