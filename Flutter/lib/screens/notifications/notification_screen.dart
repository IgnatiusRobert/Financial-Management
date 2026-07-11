import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/app_theme.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/loading_widget.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (mounted) {
        Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
      }
    });
  }

  Future<void> _refresh() async {
    await Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
  }

  Future<void> _markRead(String id) async {
    await Provider.of<NotificationProvider>(context, listen: false).markAsRead(id);
  }

  Future<void> _markAllRead() async {
    final success = await Provider.of<NotificationProvider>(context, listen: false).markAllAsRead();
    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Semua notifikasi ditandai dibaca'),
          backgroundColor: AppColors.income,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = Provider.of<NotificationProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi'),
        actions: [
          if (provider.unreadCount > 0)
            TextButton(
              onPressed: _markAllRead,
              child: const Text('Tandai Semua Dibaca'),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: provider.isLoading && provider.notifications.isEmpty
          ? const LoadingWidget()
          : RefreshIndicator(
              onRefresh: _refresh,
              child: provider.notifications.isEmpty
                  ? const EmptyState(
                      icon: Icons.notifications_none_rounded,
                      title: 'Tidak Ada Notifikasi',
                      subtitle: 'Kotak masuk Anda bersih! Belum ada pemberitahuan keuangan.',
                    )
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      itemCount: provider.notifications.length,
                      itemBuilder: (context, index) {
                        final notif = provider.notifications[index];
                        final timeStr = notif.createdAt != null
                            ? DateFormat('dd MMM yyyy, HH:mm').format(DateTime.parse(notif.createdAt!))
                            : '';

                        return Card(
                          color: notif.isUnread
                              ? (isDark ? AppColors.darkCard : Colors.purple[50]?.withOpacity(0.5))
                              : (isDark ? AppColors.darkSurface : AppColors.lightSurface),
                          margin: const EdgeInsets.only(bottom: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                            side: notif.isUnread
                                ? BorderSide(
                                    color: isDark ? AppColors.primaryLight : AppColors.primary,
                                    width: 1,
                                  )
                                : BorderSide.none,
                          ),
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: notif.isUnread && notif.id != null
                                ? () => _markRead(notif.id!)
                                : null,
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _buildTypeIcon(notif.type ?? ''),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                notif.title ?? 'Pemberitahuan',
                                                style: TextStyle(
                                                  fontWeight:
                                                      notif.isUnread ? FontWeight.bold : FontWeight.w600,
                                                  fontSize: 15,
                                                ),
                                              ),
                                            ),
                                            if (notif.isUnread)
                                              Container(
                                                width: 8,
                                                height: 8,
                                                decoration: const BoxDecoration(
                                                  color: AppColors.primary,
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          notif.message ?? '',
                                          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                                color: notif.isUnread
                                                    ? (isDark ? Colors.white : AppColors.lightText)
                                                    : Colors.grey,
                                              ),
                                        ),
                                        const SizedBox(height: 8),
                                        Text(
                                          timeStr,
                                          style: TextStyle(color: Colors.grey[500], fontSize: 11),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }

  Widget _buildTypeIcon(String type) {
    IconData icon;
    Color color;
    Color bgColor;

    if (type.contains('BudgetUsageAlert') || type.toLowerCase().contains('budget')) {
      icon = Icons.warning_amber_rounded;
      color = AppColors.warning;
      bgColor = AppColors.warningLight;
    } else if (type.contains('SavingsGoalReached') || type.toLowerCase().contains('goal')) {
      icon = Icons.emoji_events_outlined;
      color = AppColors.income;
      bgColor = AppColors.incomeLight;
    } else {
      icon = Icons.info_outline_rounded;
      color = AppColors.info;
      bgColor = AppColors.infoLight;
    }

    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: bgColor,
        shape: BoxShape.circle,
      ),
      child: Icon(icon, color: color, size: 24),
    );
  }
}
