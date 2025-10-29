import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String apiKey = '12345';
  static const String baseUrl = 'http://10.0.2.2/ton_projet/api.php';

  static Future<List<dynamic>> fetchConcessions() async {
    final uri = Uri.parse('$baseUrl?key=$apiKey');
    final response = await http.get(uri);

    if (response.statusCode == 200) {
      final Map<String, dynamic> jsonData = jsonDecode(response.body);
      if (jsonData['success'] == true) {
        return jsonData['data'];
      } else {
        throw Exception('Erreur API: ${jsonData['error']}');
      }
    } else {
      throw Exception('Erreur serveur: ${response.statusCode}');
    }
  }

  static Future<bool> addConcession(Map<String, dynamic> data) async {
    final uri = Uri.parse('$baseUrl');
    final response = await http.post(
      uri,
      headers: {'Content-Type': 'application/json', 'X-API-Key': apiKey},
      body: jsonEncode(data),
    );

    final jsonResp = jsonDecode(response.body);
    return jsonResp['success'] == true;
  }
}
