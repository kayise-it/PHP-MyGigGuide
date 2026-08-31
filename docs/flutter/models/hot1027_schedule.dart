class Hot1027ShowSlot {
  const Hot1027ShowSlot({
    required this.title,
    required this.start,
    required this.end,
    this.imageUrl,
  });

  final String title;
  final String start;
  final String end;
  final String? imageUrl;

  String get timeRange => '${_formatTime(start)}–${_formatTime(end)}';

  static String _formatTime(String raw) {
    final parts = raw.split(':');
    if (parts.length < 2) return raw;
    final hour = int.tryParse(parts[0]);
    final minute = int.tryParse(parts[1]);
    if (hour == null || minute == null) return raw;
    return '${hour.toString().padLeft(2, '0')}:${minute.toString().padLeft(2, '0')}';
  }
}

class Hot1027DaySchedule {
  const Hot1027DaySchedule({
    required this.weekday,
    required this.dayName,
    required this.shows,
  });

  final int weekday;
  final String dayName;
  final List<Hot1027ShowSlot> shows;
}

class Hot1027Broadcast {
  const Hot1027Broadcast({
    this.currentShow,
    this.nextShow,
    this.currentImageUrl,
  });

  final Hot1027ShowSlot? currentShow;
  final Hot1027ShowSlot? nextShow;
  final String? currentImageUrl;
}

class Hot1027Presenter {
  const Hot1027Presenter({
    required this.name,
    this.imageUrl,
    this.scheduleSummary = const [],
    this.showUrl,
  });

  final String name;
  final String? imageUrl;
  final List<String> scheduleSummary;
  final String? showUrl;
}
