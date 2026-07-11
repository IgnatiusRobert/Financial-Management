import 'package:flutter/material.dart';
import 'package:percent_indicator/circular_percent_indicator.dart';
import '../config/app_theme.dart';

class HealthScoreWidget extends StatelessWidget {
  final double score;
  final double radius;

  const HealthScoreWidget({
    super.key,
    required this.score,
    this.radius = 55,
  });

  Color _getScoreColor() {
    if (score >= 70) return AppColors.income;
    if (score >= 40) return AppColors.warning;
    return AppColors.expense;
  }

  String _getScoreLabel() {
    if (score >= 70) return 'Sangat Baik';
    if (score >= 40) return 'Cukup Baik';
    return 'Perlu Perhatian';
  }

  IconData _getScoreIcon() {
    if (score >= 70) return Icons.sentiment_very_satisfied;
    if (score >= 40) return Icons.sentiment_satisfied;
    return Icons.sentiment_dissatisfied;
  }

  @override
  Widget build(BuildContext context) {
    final color = _getScoreColor();
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Theme.of(context).cardTheme.color,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          CircularPercentIndicator(
            radius: radius,
            lineWidth: 8,
            percent: (score / 100).clamp(0.0, 1.0),
            center: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(_getScoreIcon(), color: color, size: 24),
                const SizedBox(height: 2),
                Text(
                  '${score.toStringAsFixed(0)}',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ],
            ),
            progressColor: color,
            backgroundColor: color.withValues(alpha: 0.12),
            circularStrokeCap: CircularStrokeCap.round,
            animation: true,
            animationDuration: 1200,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Skor Keuangan',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    _getScoreLabel(),
                    style: TextStyle(
                      color: color,
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'Berdasarkan analisis pemasukan, pengeluaran, dan tabungan Anda.',
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
