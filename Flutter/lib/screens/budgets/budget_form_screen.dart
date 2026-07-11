import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../models/budget.dart';
import '../../models/category.dart';
import '../../services/category_service.dart';
import '../../providers/budget_provider.dart';
import '../../providers/dashboard_provider.dart';

class BudgetFormScreen extends StatefulWidget {
  const BudgetFormScreen({super.key});

  @override
  State<BudgetFormScreen> createState() => _BudgetFormScreenState();
}

class _BudgetFormScreenState extends State<BudgetFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  
  String _period = 'monthly';
  int? _selectedCategoryId;
  
  List<Category> _categories = [];
  bool _isLoadingCategories = true;
  Budget? _editingBudget;
  bool _isInit = true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_isInit) {
      final args = ModalRoute.of(context)?.settings.arguments;
      if (args is Budget) {
        _editingBudget = args;
        _amountController.text = args.amount?.toInt().toString() ?? '';
        _selectedCategoryId = args.categoryId;
        _period = args.period ?? 'monthly';
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
    super.dispose();
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
          content: Text('Anggaran harus lebih besar dari 0'),
          backgroundColor: AppColors.expense,
        ),
      );
      return;
    }

    final data = {
      'amount': amount,
      'period': _period,
      'category_id': _selectedCategoryId,
    };

    final budgetProvider = Provider.of<BudgetProvider>(context, listen: false);
    final dbProvider = Provider.of<DashboardProvider>(context, listen: false);
    bool success;

    if (_editingBudget != null) {
      success = await budgetProvider.updateBudget(_editingBudget!.id!, data);
    } else {
      success = await budgetProvider.addBudget(data);
    }

    if (!mounted) return;

    if (success) {
      dbProvider.fetchDashboardData();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_editingBudget != null
              ? 'Anggaran berhasil diperbarui'
              : 'Anggaran berhasil ditambahkan'),
          backgroundColor: AppColors.income,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(budgetProvider.error ?? 'Gagal menyimpan anggaran'),
          backgroundColor: AppColors.expense,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = Provider.of<BudgetProvider>(context).isLoading;

    return Scaffold(
      appBar: AppBar(
        title: Text(_editingBudget != null ? 'Edit Anggaran' : 'Tambah Anggaran'),
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
                    // Amount Input
                    TextFormField(
                      controller: _amountController,
                      keyboardType: TextInputType.number,
                      enabled: !isLoading,
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                      decoration: const InputDecoration(
                        labelText: 'Jumlah Anggaran Bulanan (Rp)',
                        hintText: '0',
                        prefixIcon: Icon(Icons.wallet_outlined),
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Jumlah anggaran tidak boleh kosong';
                        }
                        if (double.tryParse(value.trim()) == null) {
                          return 'Harus berupa angka';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 24),

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
                    const SizedBox(height: 24),

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
                              _editingBudget != null ? 'Simpan Perubahan' : 'Buat Anggaran',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                    ),
                    if (_editingBudget != null) ...[
                      const SizedBox(height: 16),
                      OutlinedButton(
                        onPressed: isLoading
                            ? null
                            : () async {
                                final confirm = await showDialog<bool>(
                                  context: context,
                                  builder: (ctx) => AlertDialog(
                                    title: const Text('Hapus Anggaran?'),
                                    content: const Text('Apakah Anda yakin ingin menghapus anggaran ini?'),
                                    actions: [
                                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                                      TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                    ],
                                  ),
                                );
                                if (confirm == true) {
                                  final bgProvider = Provider.of<BudgetProvider>(context, listen: false);
                                  final success = await bgProvider.deleteBudget(_editingBudget!.id!);
                                  if (mounted) {
                                    if (success) {
                                      Provider.of<DashboardProvider>(context, listen: false).fetchDashboardData();
                                      Navigator.pop(context);
                                    } else {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text(bgProvider.error ?? 'Gagal menghapus anggaran'))
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
                        child: const Text('Hapus Anggaran', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ],
                ),
              ),
            ),
    );
  }
}
