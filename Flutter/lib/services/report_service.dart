import 'api_service.dart';
import '../config/app_config.dart';

class ReportService {
  final ApiService _api = ApiService();

  Future<Map<String, dynamic>> getReport({
    String filter = 'monthly',
    String? startDate,
    String? endDate,
  }) async {
    final Map<String, dynamic> params = {'filter': filter};
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;

    final response = await _api.get('/reports', queryParameters: params);
    final data = response.data;
    if (data['success'] == true) {
      return data['data'];
    }
    throw data['message'] ?? 'Gagal memuat laporan';
  }

  String getExportUrl({String format = 'pdf', String filter = 'monthly'}) {
    return '${AppConfig.baseUrl}/reports/export?format=$format&filter=$filter';
  }
}
