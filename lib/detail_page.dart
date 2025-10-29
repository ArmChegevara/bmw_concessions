import 'package:flutter/material.dart';
import 'api_service.dart';

class DetailPage extends StatefulWidget {
  final int id;
  const DetailPage({super.key, required this.id});

  @override
  State<DetailPage> createState() => _DetailPageState();
}

class _DetailPageState extends State<DetailPage> {
  Map<String, dynamic>? data;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final result = await ApiService.getConcessionById(widget.id);
    setState(() {
      data = result;
      loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (data == null) {
      return const Scaffold(
        body: Center(child: Text('❌ Concession introuvable')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(data!['nom'] ?? 'Détails'),
        backgroundColor: const Color(0xFF003052),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView(
          children: [
            if (data!['photo'] != null && data!['photo'] != '')
              Image.network(ApiService.getImageUrl(data!['photo'])),
            const SizedBox(height: 12),
            Text(
              "Nom: ${data!['nom']}",
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text("Description: ${data!['description']}"),
            Text("Prix: ${data!['prix']} €"),
            Text("Contact: ${data!['contact_name']}"),
            Text("Email: ${data!['contact_email']}"),
            Text("Latitude: ${data!['latitude']}"),
            Text("Longitude: ${data!['longitude']}"),
          ],
        ),
      ),
    );
  }
}
