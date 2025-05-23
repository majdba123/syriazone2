class UserProfile {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final Location? location;

  UserProfile({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.location,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      location: json['location'] != null
          ? Location.fromJson(json['location'])
          : null,
    );
  }
}

class Location {
  final double? lat;
  final double? lang;

  Location({this.lat, this.lang});

  factory Location.fromJson(Map<String, dynamic> json) {
    return Location(
      lat: json['lat']?.toDouble(),
      lang: json['lang']?.toDouble(),
    );
  }
}