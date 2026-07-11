import '../models/dashboard_data.dart';
import 'api_service.dart';

class DashboardService {
  final ApiService _api = ApiService();

  Future<DashboardData> getDashboard({String filter = 'monthly', String? startDate, String? endDate}) async {
    final response = await _api.get('/dashboard', queryParameters: {
      'filter': filter,
      if (startDate != null) 'start_date': startDate,
      if (endDate != null) 'end_date': endDate,
    });
    final data = response.data;
    if (data['success'] == true) {
      return DashboardData.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memuat dashboard';
  }
}
