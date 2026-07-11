import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/app_theme.dart';
import '../../models/savings_goal.dart';
import '../../providers/savings_goal_provider.dart';
import '../../providers/dashboard_provider.dart';

class SavingsFormScreen extends StatefulWidget {
  const SavingsFormScreen({super.key});

  @override
  State<SavingsFormScreen> createState() => _SavingsFormScreenState();
}

class _SavingsFormScreenState extends State<SavingsFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _targetController = TextEditingController();
  final _currentController = TextEditingController();
  
  DateTime? _targetDate;
  SavingsGoal? _editingGoal;
  bool _isInit = true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_isInit) {
      final args = ModalRoute.of(context)?.settings.arguments;
      if (args is SavingsGoal) {
        _editingGoal = args;
        _nameController.text = args.name ?? '';
        _targetController.text = args.targetAmount?.toInt().toString() ?? '';
        _currentController.text = args.currentAmount?.toInt().toString() ?? '';
        if (args.targetDate != null) {
          _targetDate = DateTime.parse(args.targetDate!);
        }
      }
      _isInit = false;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _targetController.dispose();
    _currentController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _targetDate ?? DateTime.now().add(const Duration(days: 30)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 3650)),
    );
    if (picked != null && picked != _targetDate) {
      setState(() {
        _targetDate = picked;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final double? targetAmount = double.tryParse(_targetController.text.trim());
    final double currentAmount = double.tryParse(_currentController.text.trim()) ?? 0;

    if (targetAmount == null || targetAmount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Target nominal harus lebih besar dari 0'),
          backgroundColor: AppColors.expense,
        ),
      );
      return;
    }

    final data = {
      'name': _nameController.text.trim(),
      'target_amount': targetAmount,
      'current_amount': currentAmount,
      'target_date': _targetDate != null ? DateFormat('yyyy-MM-dd').format(_targetDate!) : null,
    };

    final goalProvider = Provider.of<SavingsGoalProvider>(context, listen: false);
    final dbProvider = Provider.of<DashboardProvider>(context, listen: false);
    bool success;

    if (_editingGoal != null) {
      success = await goalProvider.updateGoal(_editingGoal!.id!, data);
    } else {
      success = await goalProvider.addGoal(data);
    }

    if (!mounted) return;

    if (success) {
      dbProvider.fetchDashboardData();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_editingGoal != null
              ? 'Anggaran berhasil diperbarui'
              : 'Anggaran berhasil ditambahkan'),
          backgroundColor: AppColors.income,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(goalProvider.error ?? 'Gagal menyimpan anggaran'),
          backgroundColor: AppColors.expense,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = Provider.of<SavingsGoalProvider>(context).isLoading;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(_editingGoal != null ? 'Edit Anggaran' : 'Tambah Anggaran'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Name Input
              TextFormField(
                controller: _nameController,
                keyboardType: TextInputType.text,
                enabled: !isLoading,
                decoration: const InputDecoration(
                  labelText: 'Keterangan',
                  hintText: 'Contoh: Dana Darurat',
                  prefixIcon: Icon(Icons.star_border_rounded),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Nama target tidak boleh kosong';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Target Amount Input
              TextFormField(
                controller: _targetController,
                keyboardType: TextInputType.number,
                enabled: !isLoading,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                decoration: const InputDecoration(
                  labelText: 'Target (Rp)',
                  hintText: '0',
                  prefixIcon: Icon(Icons.flag_outlined),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Target nominal tidak boleh kosong';
                  }
                  if (double.tryParse(value.trim()) == null) {
                    return 'Harus berupa angka';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Current Amount Input
              TextFormField(
                controller: _currentController,
                keyboardType: TextInputType.number,
                enabled: !isLoading,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                decoration: const InputDecoration(
                  labelText: 'Terkumpul (Rp)',
                  hintText: '0',
                  prefixIcon: Icon(Icons.savings_outlined),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Kolom ini tidak boleh kosong (isi 0 jika baru mulai)';
                  }
                  if (double.tryParse(value.trim()) == null) {
                    return 'Harus berupa angka';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Target Date Picker Trigger
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
                            _targetDate != null
                                ? DateFormat('dd MMMM yyyy').format(_targetDate!)
                                : 'Pilih Tenggat Waktu (Opsional)',
                            style: TextStyle(
                              color: _targetDate != null
                                  ? (isDark ? AppColors.darkText : AppColors.lightText)
                                  : Colors.grey,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                      if (_targetDate != null && !isLoading)
                        IconButton(
                          icon: const Icon(Icons.clear, size: 20, color: Colors.grey),
                          onPressed: () {
                            setState(() {
                              _targetDate = null;
                            });
                          },
                        )
                      else
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
                        _editingGoal != null ? 'Simpan Perubahan' : 'Buat Target',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
              if (_editingGoal != null) ...[
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
                            final sgProvider = Provider.of<SavingsGoalProvider>(context, listen: false);
                            final success = await sgProvider.deleteGoal(_editingGoal!.id!);
                            if (mounted) {
                              if (success) {
                                Provider.of<DashboardProvider>(context, listen: false).fetchDashboardData();
                                Navigator.pop(context);
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(sgProvider.error ?? 'Gagal menghapus anggaran'))
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
