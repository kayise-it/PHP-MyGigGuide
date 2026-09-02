import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/hot1027_schedule.dart';

/// Loads schedule, on-air, and presenter data from hot1027.co.za (Radio Station Pro API).
class Hot1027RadioRepository {
  Hot1027RadioRepository({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  static const _base = 'https://hot1027.co.za/wp-json/radio';

  static const _weekdayNames = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  Future<Hot1027DaySchedule?> fetchTodaySchedule() async {
    final response = await _client
        .get(Uri.parse('$_base/schedule/'))
        .timeout(const Duration(seconds: 20));
    if (response.statusCode != 200) return null;

    final body = json.decode(response.body) as Map<String, dynamic>;
    final schedule = body['schedule'] as Map<String, dynamic>?;
    if (schedule == null) return null;

    final now = DateTime.now();
    final dayName = _weekdayNames[now.weekday - 1];
    final daySlots = schedule[dayName];
    if (daySlots is! List) return null;

    final shows = daySlots
        .whereType<Map<String, dynamic>>()
        .map(_slotFromScheduleJson)
        .where((slot) => slot.title.trim().isNotEmpty)
        .toList();

    return Hot1027DaySchedule(
      weekday: now.weekday,
      dayName: dayName,
      shows: shows,
    );
  }

  Future<Hot1027Broadcast?> fetchBroadcast() async {
    final response = await _client
        .get(Uri.parse('$_base/broadcast/'))
        .timeout(const Duration(seconds: 20));
    if (response.statusCode != 200) return null;

    final body = json.decode(response.body) as Map<String, dynamic>;
    final broadcast = body['broadcast'] as Map<String, dynamic>?;
    if (broadcast == null) return null;

    final current = broadcast['current_show'];
    final next = broadcast['next_show'];

    Hot1027ShowSlot? currentSlot;
    String? imageUrl;
    if (current is Map<String, dynamic>) {
      currentSlot = _slotFromScheduleJson(current);
      imageUrl = _showImage(current['show']);
    }

    Hot1027ShowSlot? nextSlot;
    if (next is Map<String, dynamic>) {
      nextSlot = _slotFromScheduleJson(next);
    }

    return Hot1027Broadcast(
      currentShow: currentSlot,
      nextShow: nextSlot,
      currentImageUrl: imageUrl,
    );
  }

  Future<List<Hot1027Presenter>> fetchPresenters() async {
    final response = await _client
        .get(Uri.parse('$_base/shows/'))
        .timeout(const Duration(seconds: 20));
    if (response.statusCode != 200) return [];

    final body = json.decode(response.body) as Map<String, dynamic>;
    final shows = body['shows'];
    if (shows is! List) return [];

    final presenters = <Hot1027Presenter>[];
    for (final raw in shows.whereType<Map<String, dynamic>>()) {
      final name = (raw['name'] as String? ?? '').trim();
      if (name.isEmpty) continue;

      final schedule = raw['schedule'];
      final summary = <String>[];
      if (schedule is List) {
        for (final slot in schedule.whereType<Map<String, dynamic>>()) {
          final day = slot['day'] as String? ?? '';
          final start = slot['start'] as String? ?? '';
          final end = slot['end'] as String? ?? '';
          if (day.isNotEmpty && start.isNotEmpty && end.isNotEmpty) {
            summary.add('$day $start–$end');
          }
        }
      }

      presenters.add(
        Hot1027Presenter(
          name: name,
          imageUrl: raw['avatar_url'] as String? ?? raw['image_url'] as String?,
          scheduleSummary: summary,
          showUrl: raw['url'] as String?,
        ),
      );
    }

    presenters.sort((a, b) => a.name.compareTo(b.name));
    return presenters;
  }

  Hot1027ShowSlot _slotFromScheduleJson(Map<String, dynamic> json) {
    final show = json['show'];
    final title = show is Map<String, dynamic>
        ? (show['name'] as String? ?? '').trim()
        : '';
    return Hot1027ShowSlot(
      title: title.isNotEmpty ? title : 'On air',
      start: json['start'] as String? ?? '',
      end: json['end'] as String? ?? '',
      imageUrl: show is Map<String, dynamic> ? _showImage(show) : null,
    );
  }

  String? _showImage(Map<String, dynamic>? show) {
    if (show == null) return null;
    final url = (show['avatar_url'] as String?) ?? (show['image_url'] as String?);
    if (url == null || url.trim().isEmpty) return null;
    return url;
  }
}
