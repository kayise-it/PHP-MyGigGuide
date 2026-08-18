class StationPoll {
  const StationPoll({
    required this.id,
    required this.context,
    required this.question,
    required this.options,
    required this.isOpen,
    required this.voteCounts,
    required this.totalVotes,
    this.closesAt,
    this.userVoteIndex,
  });

  factory StationPoll.fromJson(Map<String, dynamic> json) {
    final optionsRaw = json['options'];
    final options = <String>[];
    final voteCounts = <int>[];

    if (optionsRaw is List) {
      for (final entry in optionsRaw) {
        if (entry is Map) {
          options.add(entry['label']?.toString() ?? '');
          final votes = entry['votes'];
          voteCounts.add(votes is int ? votes : int.tryParse('$votes') ?? 0);
        } else {
          options.add(entry.toString());
        }
      }
    }

    // Legacy/alternate API shape: parallel vote_counts array + string options.
    if (voteCounts.length < options.length) {
      final countsRaw = json['vote_counts'];
      if (countsRaw is List) {
        voteCounts.clear();
        for (final v in countsRaw) {
          voteCounts.add(v is int ? v : int.tryParse('$v') ?? 0);
        }
      }
    }
    while (voteCounts.length < options.length) {
      voteCounts.add(0);
    }

    final closesAt = json['closes_at'] as String?;
    final isOpen = json['is_open'] as bool? ??
        json['active'] as bool? ??
        _inferOpenFromClosesAt(closesAt);

    return StationPoll(
      id: json['id'] as int,
      context: json['context'] as String? ?? '',
      question: json['question'] as String? ?? '',
      options: options,
      isOpen: isOpen,
      closesAt: closesAt,
      voteCounts: voteCounts,
      totalVotes: json['total_votes'] as int? ?? 0,
      userVoteIndex: json['user_vote_index'] as int?,
    );
  }

  static bool _inferOpenFromClosesAt(String? closesAt) {
    if (closesAt == null || closesAt.isEmpty) return true;
    final parsed = DateTime.tryParse(closesAt);
    if (parsed == null) return true;
    return parsed.isAfter(DateTime.now());
  }

  final int id;
  final String context;
  final String question;
  final List<String> options;
  final bool isOpen;
  final String? closesAt;
  final List<int> voteCounts;
  final int totalVotes;
  final int? userVoteIndex;
}
