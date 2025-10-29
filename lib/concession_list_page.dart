import 'package:flutter/material.dart';
import 'api_service.dart';
import 'detail_page.dart';
import 'add_concession_page.dart';

class ConcessionListPage extends StatefulWidget {
  const ConcessionListPage({super.key});

  @override
  State<ConcessionListPage> createState() => _ConcessionListPageState();
}

class _ConcessionListPageState extends State<ConcessionListPage> {
  List<dynamic> concessions = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final data = await ApiService.getAllConcessions();
    setState(() {
      concessions = data;
      loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Liste des Concessions'),
        backgroundColor: const Color(0xFF003052),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: concessions.length,
              itemBuilder: (context, index) {
                final item = concessions[index];
                return Card(
                  margin:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  child: ListTile(
                    title: Text(item['nom'] ?? 'Sans nom'),
                    subtitle: Text("Prix: ${item['prix']} €"),
                    onTap: () {
                      print("✅ Cliquez sur ${item['id']}");
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) =>
                              DetailPage(id: int.parse(item['id'].toString())),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: const Color(0xFFC70039),
        child: const Icon(Icons.add),
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const AddConcessionPage()),
          );
        },
      ),
    );
  }
}
