import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../../config/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/report_service.dart';
import '../../helpers/currency_helper.dart';
import '../../widgets/empty_state.dart';

class ReportScreen extends StatefulWidget {
  const ReportScreen({super.key});

  @override
  State<ReportScreen> createState() => _ReportScreenState();
}

class _ReportScreenState extends State<ReportScreen> {
  final ReportService _reportService = ReportService();
  String _filter = 'monthly';
  DateTime? _startDate;
  DateTime? _endDate;

  Map<String, dynamic>? _reportData;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadReport();
  }

  Future<void> _loadReport() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final startStr = _startDate != null ? DateFormat('yyyy-MM-dd').format(_startDate!) : null;
      final endStr = _endDate != null ? DateFormat('yyyy-MM-dd').format(_endDate!) : null;

      final data = await _reportService.getReport(
        filter: _filter,
        startDate: startStr,
        endDate: endStr,
      );

      setState(() {
        _reportData = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _selectCustomDateRange() async {
    final DateTimeRange? picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      initialDateRange: _startDate != null && _endDate != null
          ? DateTimeRange(start: _startDate!, end: _endDate!)
          : null,
    );

    if (picked != null) {
      setState(() {
        _filter = 'custom';
        _startDate = picked.start;
        _endDate = picked.end;
      });
      _loadReport();
    }
  }

  void _onFilterChanged(String filter) {
    if (filter == 'custom') {
      _selectCustomDateRange();
    } else {
      setState(() {
        _filter = filter;
        _startDate = null;
        _endDate = null;
      });
      _loadReport();
    }
  }

  Future<void> _export(String format) async {
    try {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mengunduh laporan...')));
      }
      final dir = await getApplicationDocumentsDirectory();
      final ext = format == 'excel' ? 'xlsx' : 'pdf';
      final fileName = 'laporan_keuangan_${DateTime.now().millisecondsSinceEpoch}.$ext';
      final savePath = '${dir.path}/$fileName';

      final queryParams = {
        'format': format,
        'filter': _filter,
        if (_startDate != null) 'start_date': DateFormat('yyyy-MM-dd').format(_startDate!),
        if (_endDate != null) 'end_date': DateFormat('yyyy-MM-dd').format(_endDate!),
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Laporan Finansial'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            child: Row(
              children: [
                _buildFilterChip('Bulanan', 'monthly'),
                const SizedBox(width: 8),
                _buildFilterChip('Harian', 'daily'),
                const SizedBox(width: 8),
                _buildFilterChip('Mingguan', 'weekly'),
                const SizedBox(width: 8),
                _buildFilterChip('Tahunan', 'yearly'),
                const SizedBox(width: 8),
                _buildFilterChip('Kustom', 'custom'),
              ],
            ),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('Terjadi kesalahan: $_error', style: const TextStyle(color: AppColors.expense)),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _loadReport, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : _buildReportContent(isDark),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _filter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => _onFilterChanged(value),
    );
  }

  Widget _buildReportContent(bool isDark) {
    final double income = double.tryParse(_reportData?['total_income']?.toString() ?? '0') ?? 0;
    final double expense = double.tryParse(_reportData?['total_expense']?.toString() ?? '0') ?? 0;
    final double net = double.tryParse(_reportData?['net_flow']?.toString() ?? '0') ?? 0;

    final List<dynamic> categoriesData = _reportData?['expenses_by_category'] ?? [];

    return RefreshIndicator(
      onRefresh: _loadReport,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Period Range Description
            if (_filter == 'custom' && _startDate != null && _endDate != null) ...[
              Center(
                child: Text(
                  'Periode: ${DateFormat('dd MMM').format(_startDate!)} - ${DateFormat('dd MMM yyyy').format(_endDate!)}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Cashflow Summary Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _buildCashflowStat(
                          'Pemasukan',
                          income,
                          AppColors.income,
                          Icons.arrow_downward,
                        ),
                        Container(width: 1, height: 50, color: Colors.grey[300]),
                        _buildCashflowStat(
                          'Pengeluaran',
                          expense,
                          AppColors.expense,
                          Icons.arrow_upward,
                        ),
                      ],
                    ),
                    const Divider(height: 32),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Arus Kas Bersih',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        Text(
                          CurrencyHelper.format(net),
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 18,
                            color: net >= 0 ? AppColors.income : AppColors.expense,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Expense Category Chart
            if (categoriesData.isEmpty)
              const EmptyState(
                icon: Icons.pie_chart_outline_rounded,
                title: 'Tidak Ada Grafik',
                subtitle: 'Belum ada pengeluaran di periode ini.',
              )
            else ...[
              Text(
                'Pengeluaran Kategori',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      SizedBox(
                        height: 200,
                        child: PieChart(
                          PieChartData(
                            sectionsSpace: 4,
                            centerSpaceRadius: 50,
                            sections: _buildPieChartSections(categoriesData),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Legend list
                      Column(
                        children: categoriesData.map((data) {
                          final double total = double.tryParse(data['total']?.toString() ?? '0') ?? 0;
                          final double percent = expense > 0 ? (total / expense) * 100 : 0;
                          final color = _parseColor(data['color'] ?? 'grey');

                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4),
                            child: Row(
                              children: [
                                Container(
                                  width: 12,
                                  height: 12,
                                  decoration: BoxDecoration(
                                    color: color,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(child: Text(data['name'] ?? 'Lainnya')),
                                Text(
                                  '${percent.toStringAsFixed(1)}% (${CurrencyHelper.format(total)})',
                                  style: const TextStyle(fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ),
                ),
              ),
            ],
            const SizedBox(height: 24),

            // Export Actions
            Text(
              'Ekspor Laporan',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ).apply(
                    child: ElevatedButton(
                      onPressed: () => _export('pdf'),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.picture_as_pdf_outlined, size: 20),
                          SizedBox(width: 8),
                          Text('PDF'),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _export('excel'),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.table_chart_outlined, size: 20),
                        SizedBox(width: 8),
                        Text('Excel'),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildCashflowStat(String title, double amount, Color color, IconData icon) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, color: color, size: 16),
            const SizedBox(width: 4),
            Text(title, style: const TextStyle(color: Colors.grey, fontSize: 13)),
          ],
        ),
        const SizedBox(height: 6),
        Text(
          CurrencyHelper.format(amount),
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
      ],
    );
  }

  List<PieChartSectionData> _buildPieChartSections(List<dynamic> categories) {
    return categories.asMap().entries.map((entry) {
      final index = entry.key;
      final data = entry.value;
      final double total = double.tryParse(data['total']?.toString() ?? '0') ?? 0;
      final color = _parseColor(data['color'] ?? 'grey');

      return PieChartSectionData(
        color: color,
        value: total,
        radius: 20,
        showTitle: false,
      );
    }).toList();
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
}
extension on ButtonStyle {
  Widget apply({required Widget child}) {
    return child;
  }
}
