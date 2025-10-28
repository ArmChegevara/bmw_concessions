import 'package:flutter/material.dart';
import 'api_service.dart';
import 'add_concession_page.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BMW Concessions',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0F71BA),
          brightness: Brightness.light,
        ),
        useMaterial3: true,
      ),
      home: const ConcessionListPage(),
    );
  }
}

class ConcessionListPage extends StatefulWidget {
  const ConcessionListPage({super.key});
  @override
  State<ConcessionListPage> createState() => _ConcessionListPageState();
}

class _ConcessionListPageState extends State<ConcessionListPage> {
  late Future<List<dynamic>> _concessions;

  @override
  void initState() {
    super.initState();
    _concessions = ApiService.fetchConcessions();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('BMW Concessions'),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF003052), Color(0xFF0F71BA), Color(0xFFC70039)],
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ),
          ),
        ),
      ),
      body: FutureBuilder<List<dynamic>>(
        future: _concessions,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Erreur : ${snapshot.error}'));
          } else if (snapshot.hasData && snapshot.data!.isNotEmpty) {
            final list = snapshot.data!;
            return ListView.builder(
              itemCount: list.length,
              itemBuilder: (context, index) {
                final item = list[index];
                final imageUrl = item['photo'] != null &&
                        item['photo'].isNotEmpty
                    ? "http://10.0.2.2/project3/crudphp-di25/uploads/${item['photo']}"
                    : null;

                return Card(
                  margin:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  elevation: 3,
                  child: ListTile(
                    leading: imageUrl != null
                        ? Image.network(
                            imageUrl,
                            width: 60,
                            height: 60,
                            fit: BoxFit.cover,
                          )
                        : const Icon(Icons.directions_car,
                            size: 40, color: Colors.grey),
                    title: Text(item['nom'] ?? ''),
                    subtitle: Text(item['ville'] ?? ''),
                    trailing: Text('${item['prix']} €'),
                  ),
                );
              },
            );
          } else {
            return const Center(child: Text('Aucune concession trouvée'));
          }
        },
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: const Color(0xFFC70039), // BMW M Red 🔴
        foregroundColor: Colors.white, // цвет иконки
        elevation: 4,
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const AddConcessionPage()),
          );
        },
        child: const Icon(Icons.add, size: 28),
      ),
    );
  }
}
