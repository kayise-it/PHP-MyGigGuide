<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oops! 500 Error • My Gig Guide</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .code-block {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.4;
        }
        .error-line {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .stack-trace {
            background-color: #1f2937;
            color: #f9fafb;
        }
        .blink {
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 text-gray-100">
    <!-- Terminal Header -->
    <div class="bg-gray-800 border-b border-gray-700 px-4 py-2 flex items-center">
        <div class="flex space-x-2">
            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
        </div>
        <div class="ml-4 text-sm text-gray-400">mygigguide.co.za - Internal Server Error</div>
    </div>

    <div class="p-6">
        <!-- Error Header -->
        <div class="mb-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="bg-red-600 text-white px-3 py-1 rounded text-sm font-mono">ERROR</div>
                <div class="text-red-400 font-mono">500</div>
                <div class="text-gray-400">Internal Server Error</div>
            </div>
            <h1 class="text-2xl font-bold text-red-400 mb-2">Oops! Something went wrong</h1>
            <p class="text-gray-300">The server encountered an unexpected condition that prevented it from fulfilling the request.</p>
        </div>

        <!-- Error Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Code Snippet -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                <div class="bg-gray-700 px-4 py-2 border-b border-gray-600 flex items-center justify-between">
                    <span class="text-sm text-gray-300">app/Http/Controllers/EventController.php</span>
                    <span class="text-xs text-gray-400">Line 47</span>
                </div>
                <div class="p-4 code-block">
                    <div class="text-gray-500 text-xs mb-2">// EventController.php - Line 42-52</div>
                    <div class="space-y-1">
                        <div class="text-gray-400">42 | &nbsp;&nbsp;&nbsp;&nbsp;public function show(Event $event)</div>
                        <div class="text-gray-400">43 | &nbsp;&nbsp;&nbsp;&nbsp;{</div>
                        <div class="text-gray-400">44 | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// Load related data</div>
                        <div class="text-gray-400">45 | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$event->load(['artists', 'venue', 'reviews']);</div>
                        <div class="text-gray-400">46 | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div class="error-line px-2 py-1 rounded">
                            <span class="text-red-300">47 | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return view('events.show', compact('event'));</span>
                            <span class="text-red-400 ml-2">← Call to undefined method</span>
                        </div>
                        <div class="text-gray-400">48 | &nbsp;&nbsp;&nbsp;&nbsp;}</div>
                        <div class="text-gray-400">49 | &nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div class="text-gray-400">50 | &nbsp;&nbsp;&nbsp;&nbsp;public function create()</div>
                        <div class="text-gray-400">51 | &nbsp;&nbsp;&nbsp;&nbsp;{</div>
                        <div class="text-gray-400">52 | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return view('events.create');</div>
                    </div>
                </div>
            </div>

            <!-- Stack Trace -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                <div class="bg-gray-700 px-4 py-2 border-b border-gray-600">
                    <span class="text-sm text-gray-300">Stack Trace</span>
                </div>
                <div class="p-4 stack-trace code-block text-xs">
                    <div class="text-red-400 mb-2">Fatal error: Call to undefined method App\Models\Event::load()</div>
                    <div class="space-y-1">
                        <div class="text-gray-300">#0 /var/www/html/app/Http/Controllers/EventController.php(47)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;EventController->show()</div>
                        <div class="text-gray-300">#1 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;Controller->callAction()</div>
                        <div class="text-gray-300">#2 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(45)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;ControllerDispatcher->dispatch()</div>
                        <div class="text-gray-300">#3 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Route.php(254)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;Route->runController()</div>
                        <div class="text-gray-300">#4 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Route.php(197)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;Route->run()</div>
                        <div class="text-gray-300">#5 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php(693)</div>
                        <div class="text-gray-400">&nbsp;&nbsp;&nbsp;&nbsp;Router->Illuminate\Routing\{closure}()</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Information -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 mb-6">
            <div class="bg-gray-700 px-4 py-2 border-b border-gray-600">
                <span class="text-sm text-gray-300">Debug Information</span>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-400 mb-1">Request ID</div>
                        <div class="text-gray-200 font-mono">req_7f8a2b3c4d5e</div>
                    </div>
                    <div>
                        <div class="text-gray-400 mb-1">Timestamp</div>
                        <div class="text-gray-200">{{ now()->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 mb-1">Environment</div>
                        <div class="text-gray-200">production</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Actions -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-200 mb-2">What can you do?</h3>
                    <p class="text-gray-400 text-sm">This appears to be a server-side issue. Our development team has been notified.</p>
                </div>
                <div class="flex space-x-3">
                    <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition">
                        Go Home
                    </a>
                    <a href="/events" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition">
                        Browse Events
                    </a>
                    <button onclick="location.reload()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition">
                        Try Again
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <div class="flex items-center justify-center space-x-2 mb-2">
                <div class="w-2 h-2 bg-red-500 rounded-full blink"></div>
                <span>Server Error Detected</span>
            </div>
            <p>&copy; {{ date('Y') }} My Gig Guide - Error Logging Active</p>
        </div>
    </div>

    <script>
        // Add some terminal-like behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate typing effect for error message
            const errorMessage = document.querySelector('.text-red-400');
            if (errorMessage) {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.style.transition = 'opacity 0.5s';
                    errorMessage.style.opacity = '1';
                }, 500);
            }
        });
    </script>
</body>
</html>








