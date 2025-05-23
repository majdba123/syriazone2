import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:image_picker/image_picker.dart';
import 'package:syriazon/services/product_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  late Map<String, dynamic> _userData;
  bool _isLoading = true;
  bool _isEditing = false;
  File? _selectedImage;
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();
  LatLng? _selectedLocation;
  GoogleMapController? _mapController;
  final _locationController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  Future<void> _loadProfileData() async {
    try {
      final response = await ProductService.getProfile();
      if (response['success']) {
        setState(() {
          _userData = response['data'];
          _nameController.text = _userData['name'];
          _emailController.text = _userData['email'];
          _phoneController.text = _userData['phone'] ?? '';
          if (_userData['location'] != null) {
            _selectedLocation = LatLng(
              double.parse(_userData['location']['lat'].toString()),
              double.parse(_userData['location']['lang'].toString()),
            );
            _locationController.text =
                "${_selectedLocation!.latitude}, ${_selectedLocation!.longitude}";
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading profile: ${e.toString()}')),
      );
    }
  }

  Future<void> _updateLocation() async {
    try {
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      setState(() {
        _selectedLocation = LatLng(position.latitude, position.longitude);
        _locationController.text =
            "${position.latitude}, ${position.longitude}";

        // تحريك الخريطة للموقع الجديد
        _mapController?.animateCamera(
          CameraUpdate.newLatLng(_selectedLocation!),
        );
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error getting location: ${e.toString()}')),
      );
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) return;

    final Map<String, dynamic> updatedData = {
      'name': _nameController.text,
      'phone': _phoneController.text,
      'password':
          _passwordController.text.isNotEmpty ? _passwordController.text : null,
      'password_confirmation':
          _confirmPasswordController.text.isNotEmpty
              ? _confirmPasswordController.text
              : null,
      'image': _selectedImage,
      'lat': _selectedLocation?.latitude.toString(),
      'lang': _selectedLocation?.longitude.toString(),
    };

    try {
      final response = await ProductService.updateProfile(
        updatedData,
        image: _selectedImage,
      );

      if (response['success']) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profile updated successfully')),
        );
        setState(() => _isEditing = false);
        await _loadProfileData();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error updating profile: ${e.toString()}')),
      );
    }
  }

  Future<void> _pickImage() async {
    final pickedFile = await ImagePicker().pickImage(
      source: ImageSource.gallery,
    );
    if (pickedFile != null) {
      setState(() => _selectedImage = File(pickedFile.path));
    }
  }

  Widget _buildLocationSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'الموقع الحالي:',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: _locationController,
          readOnly: true,
          decoration: InputDecoration(
            suffixIcon: IconButton(
              icon: const Icon(Icons.location_searching),
              onPressed: _updateLocation,
            ),
          ),
        ),
        const SizedBox(height: 16),
        if (_selectedLocation != null)
          SizedBox(
            height: 200,
            child: GoogleMap(
              initialCameraPosition: CameraPosition(
                target: _selectedLocation!,
                zoom: 15,
              ),
              onMapCreated: (controller) => _mapController = controller,
              markers: {
                Marker(
                  markerId: const MarkerId('user_location'),
                  position: _selectedLocation!,
                  infoWindow: const InfoWindow(title: 'موقعك الحالي'),
                ),
              },
              onTap: (LatLng location) {
                setState(() {
                  _selectedLocation = location;
                  _locationController.text =
                      "${location.latitude}, ${location.longitude}";
                });
              },
            ),
          ),
        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: _updateLocation,
          child: const Text('تحديث الموقع الحالي'),
        ),
      ],
    );
  }

  Widget _buildEditForm() {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          GestureDetector(
            onTap: _pickImage,
            child: CircleAvatar(
              radius: 50,
              backgroundImage:
                  _selectedImage != null
                      ? FileImage(_selectedImage!)
                      : (_userData['image_url'] != null
                              ? NetworkImage(_userData['image_url'])
                              : null)
                          as ImageProvider?,
              child:
                  _selectedImage == null && _userData['image_url'] == null
                      ? const Icon(Icons.camera_alt, size: 40)
                      : null,
            ),
          ),
          TextFormField(
            controller: _nameController,
            decoration: const InputDecoration(labelText: 'Name'),
            validator: (value) => value!.isEmpty ? 'Required' : null,
          ),
          TextFormField(
            controller: _emailController,
            decoration: const InputDecoration(labelText: 'Email'),
            enabled: false,
          ),
          TextFormField(
            controller: _phoneController,
            decoration: const InputDecoration(labelText: 'Phone'),
            keyboardType: TextInputType.phone,
          ),
          _buildLocationSection(),
          TextFormField(
            controller: _passwordController,
            decoration: const InputDecoration(labelText: 'New Password'),
            obscureText: true,
          ),
          TextFormField(
            controller: _confirmPasswordController,
            decoration: const InputDecoration(labelText: 'Confirm Password'),
            obscureText: true,
            validator: (value) {
              if (value!.isNotEmpty && value != _passwordController.text) {
                return 'Passwords do not match';
              }
              return null;
            },
          ),
          ElevatedButton(
            onPressed: _updateProfile,
            child: const Text('Save Changes'),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileInfo() {
    return Column(
      children: [
        CircleAvatar(
          radius: 50,
          backgroundImage:
              _userData['image_url'] != null
                  ? NetworkImage(_userData['image_url'])
                  : null,
          child:
              _userData['image_url'] == null
                  ? const Icon(Icons.person, size: 50)
                  : null,
        ),
        ListTile(
          title: Text(_userData['name']),
          subtitle: Text(_userData['email']),
        ),
        if (_userData['phone'] != null)
          ListTile(
            leading: const Icon(Icons.phone),
            title: Text(_userData['phone']),
          ),
        _buildLocationSection(),
        ElevatedButton(
          onPressed: () => setState(() => _isEditing = true),
          child: const Text('Edit Profile'),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          if (_isEditing)
            IconButton(
              icon: const Icon(Icons.cancel),
              onPressed: () => setState(() => _isEditing = false),
            ),
        ],
      ),
      body:
          _isLoading
              ? const Center(child: CircularProgressIndicator())
              : Padding(
                padding: const EdgeInsets.all(16.0),
                child: SingleChildScrollView(
                  child: _isEditing ? _buildEditForm() : _buildProfileInfo(),
                ),
              ),
    );
  }
}
