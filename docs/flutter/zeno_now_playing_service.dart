import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

/// Live now-playing from Zeno.fm SSE metadata API.
class ZenoNowPlayingService {
  ZenoNowPlayingService({required this.mountId});

  final String mountId;
  http.Client? _client;
  StreamSubscription<List<int>>? _subscription;
  final _controller = StreamController<String>.broadcast();
  String _buffer = '';

  Stream<String> get stream => _controller.stream;

  Future<void> start() async {
    await stop();
    _client = http.Client();
    final uri = Uri.parse(
      'https://api.zeno.fm/mounts/metadata/subscribe/$mountId',
    );
    final request = http.Request('GET', uri)
      ..headers['Accept'] = 'text/event-stream';

    try {
      final response = await _client!.send(request);
      _subscription = response.stream.listen(
        _onChunk,
        onError: (_) => _scheduleReconnect(),
        onDone: _scheduleReconnect,
        cancelOnError: true,
      );
    } catch (_) {
      _scheduleReconnect();
    }
  }

  void _onChunk(List<int> chunk) {
    _buffer += utf8.decode(chunk, allowMalformed: true);
    final parts = _buffer.split('\n');
    _buffer = parts.removeLast();

    for (final line in parts) {
      if (!line.startsWith('data:')) continue;
      final payload = line.substring(5).trim();
      if (payload.isEmpty || payload == 'ping') continue;
      try {
        final map = json.decode(payload) as Map<String, dynamic>;
        final title = map['streamTitle'] as String?;
        if (title != null && title.trim().isNotEmpty) {
          _controller.add(title.trim());
        }
      } catch (_) {}
    }
  }

  void _scheduleReconnect() {
    Future.delayed(const Duration(seconds: 8), () {
      if (!_controller.isClosed) start();
    });
  }

  Future<void> stop() async {
    await _subscription?.cancel();
    _subscription = null;
    _client?.close();
    _client = null;
    _buffer = '';
  }

  Future<void> dispose() async {
    await stop();
    await _controller.close();
  }
}
