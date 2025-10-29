import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // 💡 Базовый URL — только для эмулятора Android
  static const String baseUrl = "http://10.0.2.2/project3/crudphp-di25/api.php";
  static const String apiKey = "12345";

  // 📥 Получение всех записей
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

  // ➕ Добавление новой записи
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

  // 📸 Получение URL фотографии
  static String getImageUrl(String filename) {
    if (filename.isEmpty) return '';
    return "http://10.0.2.2/project3/crudphp-di25/uploads/$filename";
  }
}
