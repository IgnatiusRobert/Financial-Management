import '../models/category.dart';
import 'api_service.dart';

class CategoryService {
  final ApiService _api = ApiService();

  Future<List<Category>> getCategories() async {
    final response = await _api.get('/categories');
    final data = response.data;
    if (data['success'] == true) {
      final catData = data['data'];
      if (catData is List) {
        return catData.map((e) => Category.fromJson(e)).toList();
      }
      return [];
    }
    throw data['message'] ?? 'Gagal memuat kategori';
  }
}
