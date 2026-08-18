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
  });

  factory StationPoll.fromJson(Map<String, dynamic> json) {
    final optionsRaw = json['options'];
    final options = optionsRaw is List
        ? optionsRaw.map((e) => e.toString()).toList()
        : <String>[];

    final countsRaw = json['vote_counts'];
    final voteCounts = <int>[];
    if (countsRaw is List) {
      for (final v in countsRaw) {
        voteCounts.add(v is int ? v : int.tryParse('$v') ?? 0);
      }
    }
    while (voteCounts.length < options.length) {
      voteCounts.add(0);
    }

    return StationPoll(
      id: json['id'] as int,
      context: json['context'] as String? ?? '',
      question: json['question'] as String? ?? '',
      options: options,
      isOpen: json['is_open'] as bool? ?? false,
      closesAt: json['closes_at'] as String?,
      voteCounts: voteCounts,
      totalVotes: json['total_votes'] as int? ?? 0,
    );
  }

  final int id;
  final String context;
  final String question;
  final List<String> options;
  final bool isOpen;
  final String? closesAt;
  final List<int> voteCounts;
  final int totalVotes;
}
