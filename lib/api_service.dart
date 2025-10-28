import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // ⚙️ URL сервера (замени IP на свой, если отличается)
  static const String baseUrl = "http://10.0.2.2/project3/crudphp-di25/api.php";

  static const String apiKey = "12345"; // ключ из твоего PHP api.php

  // 📥 Чтение данных (GET)
  static Future<List<dynamic>> fetchConcessions() async {
    final response = await http.get(Uri.parse("$baseUrl?key=$apiKey"));

    if (response.statusCode == 200) {
      final Map<String, dynamic> jsonData = json.decode(response.body);
      if (jsonData['success'] == true) {
        return jsonData['data'];
      } else {
        throw Exception("Ошибка API: ${jsonData['error']}");
      }
    } else {
      throw Exception("Ошибка загрузки (${response.statusCode})");
    }
  }

  // ➕ Добавление данных (POST)
  static Future<bool> addConcession(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse(baseUrl),
      headers: {
        "Content-Type": "application/json",
        "X-API-Key": apiKey,
      },
      body: json.encode(data),
    );

    final Map<String, dynamic> jsonResp = json.decode(response.body);
    return jsonResp['success'] == true;
  }
}
