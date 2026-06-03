@if($config = \App\Support\FirebaseWeb::clientConfig())
    @push('head')
        <script>window.__FIREBASE_WEB_CONFIG__ = @json($config);</script>
    @endpush
@endif
