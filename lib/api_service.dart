import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // 💡 Базовый URL — только для эмулятора Android
  static const String baseUrl =
      "http://10.151.128.79/project3/crudphp-di25/api.php";
  static const String apiKey = "12345";

  // 📥 Получение всех записей
  static Future<List<dynamic>> fetchConcessions() async {
    final response = await http.get(Uri.parse("$baseUrl?key=$apiKey"));

    if (response.statusCode == 200) {
      final Map<String, dynamic> jsonData = json.decode(response.body);
      if (jsonData['success'] == true) {
        return jsonData['data'];
      } else {
        throw Exception("Erreur API: ${jsonData['error']}");
      }
    } else {
      throw Exception("Erreur загрузки (${response.statusCode})");
    }
  }

  static Future<List<dynamic>> getAllConcessions() async {
    final url = Uri.parse("$baseUrl?key=$apiKey");
    final response = await http.get(url);
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return data['data'];
      }
    }
    return [];
  }

  static Future<Map<String, dynamic>?> getConcessionById(int id) async {
    final url = Uri.parse("$baseUrl?id=$id&key=$apiKey");
    final response = await http.get(url);
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return data['data'];
      }
    }
    return null;
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
    return "http://10.151.128.79/project3/crudphp-di25/uploads/$filename";
  }
}
