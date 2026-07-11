import 'package:dio/dio.dart' as dio;
import '../models/user.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _api = ApiService();

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _api.post('/login', data: {
      'email': email,
      'password': password,
    });
    final data = response.data;
    if (data['success'] == true) {
      final token = data['data']['token'];
      await _api.setToken(token);
      return data['data'];
    }
    throw data['message'] ?? 'Login gagal';
  }

  Future<Map<String, dynamic>> register(
    String name,
    String email,
    String password,
    String passwordConfirmation,
  ) async {
    final response = await _api.post('/register', data: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    final data = response.data;
    if (data['success'] == true) {
      if (data['data'] != null && data['data']['token'] != null) {
        await _api.setToken(data['data']['token']);
      }
      return data['data'] ?? {};
    }
    throw data['message'] ?? 'Registrasi gagal';
  }

  Future<void> logout() async {
    try {
      await _api.post('/logout');
    } catch (_) {}
    await _api.deleteToken();
  }

  Future<User> getUser() async {
    final response = await _api.get('/user');
    final data = response.data;
    if (data['success'] == true) {
      return User.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memuat profil';
  }

  Future<User> updateProfile(String name, String email, String? phone, {String? avatarPath}) async {
    Map<String, dynamic> dataMap = {
      'name': name,
      'email': email,
      'phone': phone ?? '',
      '_method': 'PUT',
    };

    if (avatarPath != null) {
      dataMap['avatar'] = await dio.MultipartFile.fromFile(avatarPath);
    }

    final formData = dio.FormData.fromMap(dataMap);

    final response = await _api.post('/user', data: formData);
    final data = response.data;
    if (data['success'] == true) {
      return User.fromJson(data['data']);
    }
    throw data['message'] ?? 'Gagal memperbarui profil';
  }

  Future<void> updatePassword(
    String currentPassword,
    String password,
    String passwordConfirmation,
  ) async {
    final response = await _api.put('/user/password', data: {
      'current_password': currentPassword,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    final data = response.data;
    if (data['success'] != true) {
      throw data['message'] ?? 'Gagal memperbarui password';
    }
  }

  Future<bool> isLoggedIn() async {
    final token = await _api.getToken();
    return token != null && token.isNotEmpty;
  }
}
