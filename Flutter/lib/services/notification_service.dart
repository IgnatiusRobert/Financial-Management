import '../models/notification_item.dart';
import 'api_service.dart';

class NotificationService {
  final ApiService _api = ApiService();

  Future<List<NotificationItem>> getNotifications() async {
    final response = await _api.get('/notifications');
    final data = response.data;
    if (data['success'] == true) {
      final notifData = data['data'];
      if (notifData is List) {
        return notifData.map((e) => NotificationItem.fromJson(e)).toList();
      }
      if (notifData is Map && notifData.containsKey('data')) {
        return (notifData['data'] as List).map((e) => NotificationItem.fromJson(e)).toList();
      }
      return [];
    }
    throw data['message'] ?? 'Gagal memuat notifikasi';
  }

  Future<void> markAsRead(String id) async {
    final response = await _api.post('/notifications/$id/read');
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal menandai notifikasi';
    }
  }

  Future<void> markAllAsRead() async {
    final response = await _api.post('/notifications/read-all');
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal menandai semua notifikasi';
    }
  }
}
