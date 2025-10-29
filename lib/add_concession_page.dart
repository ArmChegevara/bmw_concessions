import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'api_service.dart';

class AddConcessionPage extends StatefulWidget {
  const AddConcessionPage({super.key});

  @override
  State<AddConcessionPage> createState() => _AddConcessionPageState();
}

class _AddConcessionPageState extends State<AddConcessionPage> {
  final _formKey = GlobalKey<FormState>();
  final _nomController = TextEditingController();
  final _descController = TextEditingController();
  final _prixController = TextEditingController();
  final _emailController = TextEditingController();
  final _contactController = TextEditingController();

  File? _selectedImage;
  bool _loading = false;

  // 📍 Проверка и запрос разрешений на геолокацию
  Future<void> _checkLocationPermission() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("⚠️ Active la localisation sur ton appareil."),
        ),
      );
      return;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("🚫 Permission de localisation refusée."),
          ),
        );
        return;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            "❌ Permission de localisation bloquée dans les paramètres.",
          ),
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    _checkLocationPermission(); // 🔔 Проверяем разрешения при запуске страницы
  }

  // 📷 Выбор фото из галереи
  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);

    if (pickedFile != null) {
      setState(() => _selectedImage = File(pickedFile.path));
    }
  }

  // 📤 Отправка данных на сервер
  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    try {
      // Получаем координаты устройства
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      final uri = Uri.parse("${ApiService.baseUrl}?key=${ApiService.apiKey}");
      final request = http.MultipartRequest('POST', uri);

      // Добавляем текстовые поля
      request.fields['nom'] = _nomController.text;
      request.fields['description'] = _descController.text;
      request.fields['prix'] = _prixController.text;
      request.fields['contact_email'] = _emailController.text;
      request.fields['contact_name'] = _contactController.text;
      request.fields['latitude'] = position.latitude.toString();
      request.fields['longitude'] = position.longitude.toString();

      // Если выбрано фото — добавляем файл
      if (_selectedImage != null) {
        request.files.add(await http.MultipartFile.fromPath(
          'photo',
          _selectedImage!.path,
        ));
      }

      // Отправляем запрос
      final response = await request.send();

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("✅ Concession ajoutée avec succès!")),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Erreur (${response.statusCode})")),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur: $e")),
      );
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ajouter une concession'),
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
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              GestureDetector(
                onTap: _pickImage,
                child: Container(
                  height: 160,
                  decoration: BoxDecoration(
                    color: Colors.grey[200],
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey),
                  ),
                  child: _selectedImage == null
                      ? const Center(child: Text('📷 Choisir une photo'))
                      : ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: Image.file(
                            _selectedImage!,
                            fit: BoxFit.cover,
                            width: double.infinity,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _nomController,
                decoration: const InputDecoration(labelText: 'Nom'),
                validator: (v) => v!.isEmpty ? 'Champ requis' : null,
              ),
              TextFormField(
                controller: _descController,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 3,
              ),
              TextFormField(
                controller: _prixController,
                decoration: const InputDecoration(labelText: 'Prix (€)'),
                keyboardType: TextInputType.number,
              ),
              TextFormField(
                controller: _contactController,
                decoration: const InputDecoration(labelText: 'Nom du contact'),
              ),
              TextFormField(
                controller: _emailController,
                decoration:
                    const InputDecoration(labelText: 'Email du contact'),
                keyboardType: TextInputType.emailAddress,
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _loading ? null : _submit,
                icon: const Icon(Icons.save),
                label: _loading
                    ? const CircularProgressIndicator()
                    : const Text('Enregistrer'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFC70039),
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
