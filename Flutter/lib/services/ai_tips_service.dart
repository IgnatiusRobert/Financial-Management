import 'api_service.dart';

class AiTipsService {
  final ApiService _api = ApiService();

  Future<List<Map<String, dynamic>>> getTips({bool refresh = false}) async {
    final response = await _api.get('/ai-tips', queryParameters: {
      'refresh': refresh.toString(),
    });
    final data = response.data;
    if (data['success'] == true) {
      final tips = data['data'];
      if (tips is List) {
        return tips.map((e) => Map<String, dynamic>.from(e)).toList();
      }
      return [];
    }
    throw data['message'] ?? 'Gagal memuat tips AI';
  }
}
