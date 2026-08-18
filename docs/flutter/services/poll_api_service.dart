import 'dart:convert';
import 'dart:math';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../brand_config.dart';
import '../models/station_poll.dart';

class PollApiService {
  PollApiService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;
  static const _deviceIdKey = 'mgg_poll_device_id';

  String get _apiV1Base {
    final site = BrandConfig.siteUrl.trim().replaceAll(RegExp(r'/+$'), '');
    return '$site/api/v1';
  }

  Future<String> deviceId() async {
    final prefs = await SharedPreferences.getInstance();
    var id = prefs.getString(_deviceIdKey);
    if (id != null && id.isNotEmpty) return id;
    id = 'mgg-${DateTime.now().millisecondsSinceEpoch}-${Random().nextInt(999999)}';
    await prefs.setString(_deviceIdKey, id);
    return id;
  }

  Future<StationPoll?> fetchPoll(String context) async {
    final uri = Uri.parse('$_apiV1Base/polls/$context');
    final response = await _client.get(uri).timeout(const Duration(seconds: 15));
    if (response.statusCode != 200) {
      throw PollApiException('Could not load poll (${response.statusCode})');
    }
    final body = json.decode(response.body) as Map<String, dynamic>;
    final pollJson = body['poll'];
    if (pollJson == null) return null;
    return StationPoll.fromJson(pollJson as Map<String, dynamic>);
  }

  Future<StationPoll> vote({
    required int pollId,
    required int optionIndex,
  }) async {
    final uri = Uri.parse('$_apiV1Base/polls/$pollId/vote');
    final response = await _client
        .post(
          uri,
          headers: {'Content-Type': 'application/json'},
          body: json.encode({
            'device_id': await deviceId(),
            'option_index': optionIndex,
          }),
        )
        .timeout(const Duration(seconds: 15));

    final body = json.decode(response.body) as Map<String, dynamic>;
    if (response.statusCode == 409) {
      final pollJson = body['poll'];
      if (pollJson is Map<String, dynamic>) {
        throw PollAlreadyVotedException(StationPoll.fromJson(pollJson));
      }
      throw PollApiException(body['message'] as String? ?? 'Already voted');
    }
    if (response.statusCode != 200) {
      throw PollApiException(body['message'] as String? ?? 'Vote failed');
    }
    return StationPoll.fromJson(body['poll'] as Map<String, dynamic>);
  }
}

class PollApiException implements Exception {
  PollApiException(this.message);
  final String message;
  @override
  String toString() => message;
}

class PollAlreadyVotedException implements Exception {
  PollAlreadyVotedException(this.poll);
  final StationPoll poll;
}
