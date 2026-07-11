import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/app_theme.dart';
import '../../models/transaction.dart';
import '../../models/category.dart';
import '../../services/category_service.dart';
import '../../providers/transaction_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/notification_provider.dart';

class TransactionFormScreen extends StatefulWidget {
  const TransactionFormScreen({super.key});

  @override
  State<TransactionFormScreen> createState() => _TransactionFormScreenState();
}

class _TransactionFormScreenState extends State<TransactionFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _descriptionController = TextEditingController();
  
  String _type = 'expense'; // 'income' or 'expense'
  DateTime _selectedDate = DateTime.now();
  int? _selectedCategoryId;
  
  List<Category> _categories = [];
  bool _isLoadingCategories = true;
  Transaction? _editingTransaction;
  bool _isInit = true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_isInit) {
      final args = ModalRoute.of(context)?.settings.arguments;
      if (args is Transaction) {
        _editingTransaction = args;
        _type = args.type ?? 'expense';
        _amountController.text = args.amount?.toInt().toString() ?? '';
        _descriptionController.text = args.description ?? '';
        _selectedDate = DateTime.parse(args.date ?? DateTime.now().toIso8601String());
        _selectedCategoryId = args.categoryId;
      }
      _loadCategories();
      _isInit = false;
    }
  }

  Future<void> _loadCategories() async {
    try {
      final categories = await CategoryService().getCategories();
      setState(() {
        _categories = categories;
        _isLoadingCategories = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingCategories = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memuat kategori: $e'),
            backgroundColor: AppColors.expense,
          ),
        );
      }
    }
  }

  @override
  void dispose() {
    _amountController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCategoryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih kategori'),
          backgroundColor: AppColors.expense,
        ),
      );
      return;
    }

    final double? amount = double.tryParse(_amountController.text.trim());
    if (amount == null || amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jumlah uang harus lebih besar dari 0'),
          backgroundColor: AppColors.expense,
        ),
      );
      return;
    }

    final data = {
      'amount': amount,
      'type': _type,
      'category_id': _selectedCategoryId,
      'description': _descriptionController.text.trim(),
      'date': DateFormat('yyyy-MM-dd').format(_selectedDate),
    };

    final txProvider = Provider.of<TransactionProvider>(context, listen: false);
    final dbProvider = Provider.of<DashboardProvider>(context, listen: false);
    bool success;

    if (_editingTransaction != null) {
      success = await txProvider.updateTransaction(_editingTransaction!.id!, data);
    } else {
      success = await txProvider.addTransaction(data);
    }

    if (!mounted) return;

    if (success) {
      await dbProvider.fetchDashboardData(); // Refresh dashboard cache as well
      
      if (!mounted) return;
      Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_editingTransaction != null
              ? 'Transaksi berhasil diperbarui'
              : 'Transaksi berhasil ditambahkan'),
          backgroundColor: AppColors.income,
        ),
      );

      final utilization = dbProvider.dashboardData?.budgetUtilization ?? 0.0;
      if (_type == 'expense' && utilization >= 80) {
        final isOver = utilization >= 100;
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (ctx) => AlertDialog(
            title: Row(
              children: [
                Icon(isOver ? Icons.error_outline : Icons.warning_amber_rounded, 
                     color: isOver ? Colors.red : Colors.orange),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    isOver ? 'Budget Terlampaui!' : 'Budget Hampir Habis',
                    style: const TextStyle(fontSize: 18),
                  ),
                ),
              ],
            ),
            content: Text(isOver 
                ? 'Pengeluaran bulan ini sudah melebihi batas budget Anda.'
                : 'Pengeluaran bulan ini sudah mencapai ${utilization.toInt()}% dari budget.'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context);
                },
                child: const Text('Tutup'),
              ),
            ],
          ),
        );
      } else {
        Navigator.pop(context);
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(txProvider.error ?? 'Gagal menyimpan transaksi'),
          backgroundColor: AppColors.expense,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = Provider.of<TransactionProvider>(context).isLoading;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(_editingTransaction != null ? 'Edit Transaksi' : 'Tambah Transaksi'),
      ),
      body: _isLoadingCategories
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Toggle Type
                    Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () {
                              setState(() {
                                _type = 'expense';
                              });
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              decoration: BoxDecoration(
                                color: _type == 'expense'
                                    ? AppColors.expense.withOpacity(0.15)
                                    : (isDark ? AppColors.darkCard : Colors.grey[100]),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: _type == 'expense' ? AppColors.expense : Colors.transparent,
                                  width: 2,
                                ),
                              ),
                              child: Center(
                                child: Text(
                                  'Pengeluaran',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: _type == 'expense' ? AppColors.expense : Colors.grey,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: GestureDetector(
                            onTap: () {
                              setState(() {
                                _type = 'income';
                              });
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              decoration: BoxDecoration(
                                color: _type == 'income'
                                    ? AppColors.income.withOpacity(0.15)
                                    : (isDark ? AppColors.darkCard : Colors.grey[100]),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: _type == 'income' ? AppColors.income : Colors.transparent,
                                  width: 2,
                                ),
                              ),
                              child: Center(
                                child: Text(
                                  'Pemasukan',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: _type == 'income' ? AppColors.income : Colors.grey,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Amount Input
                    TextFormField(
                      controller: _amountController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      enabled: !isLoading,
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                      decoration: const InputDecoration(
                        labelText: 'Jumlah Uang (Rp)',
                        hintText: '0',
                        prefixIcon: Icon(Icons.money),
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Jumlah uang tidak boleh kosong';
                        }
                        if (double.tryParse(value.trim()) == null) {
                          return 'Harus berupa angka';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 20),

                    // Category Selector
                    DropdownButtonFormField<int>(
                      value: _selectedCategoryId,
                      hint: const Text('Pilih Kategori'),
                      items: _categories
                          .where((cat) => cat.id != null)
                          .map((Category cat) {
                        return DropdownMenuItem<int>(
                          value: cat.id!,
                          child: Text(cat.name ?? ''),
                        );
                      }).toList(),
                      onChanged: isLoading
                          ? null
                          : (value) {
                              setState(() {
                                _selectedCategoryId = value;
                              });
                            },
                      decoration: const InputDecoration(
                        labelText: 'Kategori',
                        prefixIcon: Icon(Icons.category_outlined),
                      ),
                      validator: (value) => value == null ? 'Kategori harus dipilih' : null,
                    ),
                    const SizedBox(height: 20),

                    // Description Input
                    TextFormField(
                      controller: _descriptionController,
                      keyboardType: TextInputType.text,
                      enabled: !isLoading,
                      decoration: const InputDecoration(
                        labelText: 'Deskripsi',
                        hintText: 'Belanja bulanan, gaji, dll',
                        prefixIcon: Icon(Icons.description_outlined),
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Deskripsi tidak boleh kosong';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 20),

                    // Date Picker Trigger
                    GestureDetector(
                      onTap: isLoading ? null : _selectDate,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.darkSurface : AppColors.lightBackground,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: isDark ? AppColors.darkDivider : AppColors.lightDivider,
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.calendar_today_outlined, color: Colors.grey),
                                const SizedBox(width: 12),
                                Text(
                                  DateFormat('dd MMMM yyyy').format(_selectedDate),
                                  style: TextStyle(
                                    color: isDark ? AppColors.darkText : AppColors.lightText,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                            const Icon(Icons.arrow_drop_down, color: Colors.grey),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 40),

                    // Save Button
                    ElevatedButton(
                      onPressed: isLoading ? null : _submit,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 18),
                      ),
                      child: isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                              ),
                            )
                          : Text(
                              _editingTransaction != null ? 'Simpan Perubahan' : 'Simpan Transaksi',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                    ),
                    if (_editingTransaction != null) ...[
                      const SizedBox(height: 16),
                      OutlinedButton(
                        onPressed: isLoading
                            ? null
                            : () async {
                                final confirm = await showDialog<bool>(
                                  context: context,
                                  builder: (ctx) => AlertDialog(
                                    title: const Text('Hapus Transaksi?'),
                                    content: const Text('Apakah Anda yakin ingin menghapus transaksi ini?'),
                                    actions: [
                                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                                      TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                    ],
                                  ),
                                );
                                if (confirm == true) {
                                  final txProvider = Provider.of<TransactionProvider>(context, listen: false);
                                  final success = await txProvider.deleteTransaction(_editingTransaction!.id!);
                                  if (mounted) {
                                    if (success) {
                                      Provider.of<DashboardProvider>(context, listen: false).fetchDashboardData();
                                      Navigator.pop(context);
                                    } else {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text(txProvider.error ?? 'Gagal menghapus transaksi'))
                                      );
                                    }
                                  }
                                }
                              },
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 18),
                          foregroundColor: Colors.red,
                          side: const BorderSide(color: Colors.red),
                        ),
                        child: const Text('Hapus Transaksi', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ],
                ),
              ),
            ),
    );
  }
}
