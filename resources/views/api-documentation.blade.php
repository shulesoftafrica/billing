@php
    $markdownPath = base_path('docs/api-documentation.md');
    $markdown = file_exists($markdownPath) ? file_get_contents($markdownPath) : '';
    $lines = preg_split('/\R/', (string) $markdown);

    $sections = [];
    $currentSection = null;
    $currentEndpoint = null;

    $newEndpoint = static function (string $name): array {
        return [
            'name' => $name,
            'method' => '',
            'url' => '',
            'headers' => [],
            'request_body' => '{}',
            'success_code' => '',
            'success_body' => '{}',
            'errors' => [],
        ];
    };

    $flushEndpoint = static function () use (&$sections, &$currentSection, &$currentEndpoint): void {
        if ($currentSection !== null && $currentEndpoint !== null) {
            $sections[$currentSection]['endpoints'][] = $currentEndpoint;
        }
        $currentEndpoint = null;
    };

    $captureCodeBlock = static function (array $allLines, int &$index): string {
        $total = count($allLines);

        while ($index < $total && !preg_match('/^```/', trim($allLines[$index]))) {
            $index++;
        }

        if ($index >= $total) {
            return '{}';
        }

        $index++;
        $bodyLines = [];
        while ($index < $total && !preg_match('/^```/', trim($allLines[$index]))) {
            $bodyLines[] = rtrim($allLines[$index], "\r");
            $index++;
        }

        return trim(implode("\n", $bodyLines)) ?: '{}';
    };

    for ($i = 0; $i < count($lines); $i++) {
        $line = rtrim($lines[$i], "\r");
        $trim = trim($line);

        if (preg_match('/^##\s+(.+)$/', $trim, $matches)) {
            $flushEndpoint();
            $sections[] = [
                'name' => trim($matches[1]),
                'endpoints' => [],
            ];
            $currentSection = count($sections) - 1;
            continue;
        }

        if (preg_match('/^###\s+(.+)$/', $trim, $matches)) {
            $flushEndpoint();
            $currentEndpoint = $newEndpoint(trim($matches[1]));
            continue;
        }

        if ($currentEndpoint === null) {
            continue;
        }

        if (preg_match('/^\*\*Method:\*\*\s*`([^`]+)`/', $trim, $matches)) {
            $currentEndpoint['method'] = trim($matches[1]);
            continue;
        }

        if (preg_match('/^\*\*URL:\*\*\s*`([^`]+)`/', $trim, $matches)) {
            $currentEndpoint['url'] = trim($matches[1]);
            continue;
        }

        if (preg_match('/^\*\*Required Headers:\*\*/', $trim)) {
            $headers = [];
            for ($j = $i + 1; $j < count($lines); $j++) {
                $headerLine = trim($lines[$j]);
                if ($headerLine === '') {
                    continue;
                }
                if (!str_starts_with($headerLine, '|')) {
                    $i = $j - 1;
                    break;
                }
                if (preg_match('/^\|\s*-+/', $headerLine) || stripos($headerLine, '| key |') !== false) {
                    continue;
                }

                $parts = array_values(array_filter(array_map('trim', explode('|', $headerLine)), static fn ($v) => $v !== ''));
                if (count($parts) >= 2) {
                    $headers[] = [
                        'key' => $parts[0],
                        'value' => $parts[1],
                    ];
                }

                if ($j === count($lines) - 1) {
                    $i = $j;
                }
            }
            $currentEndpoint['headers'] = $headers;
            continue;
        }

        if (preg_match('/^\*\*Request Body:\*\*/', $trim)) {
            $i++;
            $currentEndpoint['request_body'] = $captureCodeBlock($lines, $i);
            continue;
        }

        if (preg_match('/^\*\*Success Response:\*\*\s*`([^`]+)`/', $trim, $matches)) {
            $currentEndpoint['success_code'] = trim($matches[1]);
            $i++;
            $currentEndpoint['success_body'] = $captureCodeBlock($lines, $i);
            continue;
        }

        if (preg_match('/^`([^`]+)`$/', $trim, $matches)) {
            $statusCode = trim($matches[1]);
            $i++;
            $errorBody = $captureCodeBlock($lines, $i);
            $currentEndpoint['errors'][] = [
                'code' => $statusCode,
                'body' => $errorBody,
            ];
            continue;
        }
    }

    $flushEndpoint();

    $methodClasses = [
        'GET' => 'method-get',
        'POST' => 'method-post',
        'PUT' => 'method-put',
        'PATCH' => 'method-patch',
        'DELETE' => 'method-delete',
    ];

    $statusClass = static function (string $code): string {
        if (preg_match('/^(\d)/', trim($code), $matches)) {
            return match ($matches[1]) {
                '2' => 'status-2xx',
                '4' => 'status-4xx',
                '5' => 'status-5xx',
                default => 'status-default',
            };
        }

        return 'status-default';
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Syne:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f8ff;
            --surface: #ffffff;
            --surface-soft: #f6f9ff;
            --surface-code: #f2f6ff;
            --border: #d7e2f0;
            --text: #0f1e36;
            --text-soft: #4f6688;
            --accent: #4f8dff;
            --accent-soft: rgba(79, 141, 255, 0.15);
            --success: #31c48d;
            --danger: #ef4444;
            --warning: #f59e0b;
            --radius: 14px;
            --sidebar-width: 280px;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Syne', system-ui, sans-serif;
            scroll-behavior: smooth;
        }

        .layout {
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #eef4ff;
            border-right: 1px solid var(--border);
            padding: 20px 16px;
            overflow-y: auto;
            z-index: 20;
        }

        .logo {
            font-size: 1.35rem;
            margin: 0 0 14px;
            letter-spacing: 0.3px;
        }

        .search {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            outline: none;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.84rem;
            margin-bottom: 16px;
        }

        .search:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .nav-section {
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 10px;
            background: var(--surface);
            overflow: hidden;
        }

        .nav-section-toggle {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--text);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.88rem;
        }

        .nav-section-toggle span:last-child {
            color: var(--text-soft);
            transition: transform .2s ease;
        }

        .nav-section.collapsed .nav-section-toggle span:last-child {
            transform: rotate(-90deg);
        }

        .nav-links {
            border-top: 1px solid var(--border);
            padding: 6px;
            display: grid;
            gap: 6px;
        }

        .nav-section.collapsed .nav-links { display: none; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-soft);
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 0.74rem;
        }

        .nav-link:hover {
            color: var(--text);
            border-color: var(--border);
            background: var(--surface-soft);
        }

        .nav-link.active {
            color: #17315b;
            border-color: rgba(79, 141, 255, 0.35);
            background: var(--accent-soft);
        }

        .method-badge,
        .status-badge {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.68rem;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .method-get { background: rgba(34, 197, 94, 0.20); color: #0f6b3f; border-color: rgba(22, 163, 74, .50); }
        .method-post { background: rgba(59, 130, 246, 0.20); color: #1d4ed8; border-color: rgba(37, 99, 235, .50); }
        .method-put { background: rgba(245, 158, 11, 0.20); color: #92400e; border-color: rgba(217, 119, 6, .50); }
        .method-patch { background: rgba(168, 85, 247, 0.20); color: #6b21a8; border-color: rgba(147, 51, 234, .50); }
        .method-delete { background: rgba(239, 68, 68, 0.20); color: #b91c1c; border-color: rgba(220, 38, 38, .50); }

        .status-2xx { background: rgba(34, 197, 94, 0.20); color: #0f6b3f; border-color: rgba(22, 163, 74, .50); }
        .status-4xx { background: rgba(239, 68, 68, 0.20); color: #b91c1c; border-color: rgba(220, 38, 38, .50); }
        .status-5xx { background: rgba(245, 158, 11, 0.20); color: #92400e; border-color: rgba(217, 119, 6, .50); }
        .status-default { background: rgba(148, 163, 184, 0.20); color: #334155; border-color: rgba(100, 116, 139, .50); }

        .main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: rgba(245, 248, 255, 0.92);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(8px);
        }

        .topbar h1 {
            margin: 0;
            font-size: 1.3rem;
        }

        .topbar-meta {
            display: flex;
            gap: 10px;
            align-items: center;
            font-family: 'IBM Plex Mono', monospace;
            color: var(--text-soft);
            font-size: 0.75rem;
        }

        .pill {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 10px;
            background: var(--surface);
        }

        .content {
            padding: 24px;
        }

        .api-section {
            margin-bottom: 28px;
        }

        .api-section.hidden,
        .endpoint-card.hidden,
        .nav-link.hidden,
        .nav-section.hidden {
            display: none !important;
        }

        /* Hide Bank Accounts, Countries, Organizations, Payment Gateways, Customers, and Product Types sections */
        .nav-section[data-section^="bank-accounts"],
        .nav-section[data-section^="countries"],
        .nav-section[data-section^="organizations"],
        .nav-section[data-section^="payment-gateways"],
        .nav-section[data-section^="customers"],
        .nav-section[data-section^="product-types"],
        .api-section[data-section="bank accounts"],
        .api-section[data-section="countries"],
        .api-section[data-section="organizations"],
        .api-section[data-section="payment gateways"],
        .api-section[data-section="customers"],
        .api-section[data-section="product types"] {
            display: none !important;
        }

        /* Hide specific non-essential Product endpoints */
        .nav-link[data-search*="delete products delete"],
        .nav-link[data-search*="update product put"],
        .nav-link[data-search*="delete product price-plans delete"],
        .nav-link[data-search*="get product price-plans get"][data-search*="/api/product-price-plan/{id}"],
        .nav-link[data-search*="update product price-plans put"],
        .endpoint-card[data-search*="delete products delete"],
        .endpoint-card[data-search*="update product put"],
        .endpoint-card[data-search*="delete product price-plans delete"],
        .endpoint-card[data-search*="get product price-plans get"][data-search*="/api/product-price-plan/{id}"],
        .endpoint-card[data-search*="update product price-plans put"] {
            display: none !important;
        }

        /* Hide specific non-essential Invoice endpoints */
        .nav-link[data-search*="get invoices by subscriptions"],
        .nav-link[data-search*="get invoices by product_id"],
        .nav-link[data-search*="get invoice payment gateways"],
        .endpoint-card[data-search*="get invoices by subscriptions"],
        .endpoint-card[data-search*="get invoices by product_id"],
        .endpoint-card[data-search*="get invoice payment gateways"] {
            display: none !important;
        }

        /* Hide specific non-essential Tax Rate endpoints */
        .nav-link[data-search*="delete tax rates delete"],
        .nav-link[data-search*="get tax rates get"][data-search*="/api/tax-rates/{id}"],
        .nav-link[data-search*="update tax rates put"],
        .endpoint-card[data-search*="delete tax rates delete"],
        .endpoint-card[data-search*="get tax rates get"][data-search*="/api/tax-rates/{id}"],
        .endpoint-card[data-search*="update tax rates put"] {
            display: none !important;
        }

        /* Hide specific non-essential Payment endpoints */
        .nav-link[data-search*="get payments by invoice"],
        .endpoint-card[data-search*="get payments by invoice"] {
            display: none !important;
        }

        .api-section h2 {
            margin: 0 0 14px;
            font-size: 1.25rem;
        }

        .endpoint-card {
            border: 1px solid var(--border);
            background: var(--surface);
            border-radius: var(--radius);
            margin-bottom: 12px;
            overflow: hidden;
            transition: border-color .2s ease;
        }

        .endpoint-card.open {
            border-color: rgba(79, 141, 255, 0.45);
        }

        .endpoint-header {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
        }

        .endpoint-header-left {
            min-width: 0;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .endpoint-url {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.8rem;
            color: #274068;
            background: var(--surface-soft);
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 4px 8px;
        }

        .endpoint-name {
            font-size: 0.9rem;
            color: var(--text-soft);
        }

        .endpoint-toggle {
            color: var(--text-soft);
            transition: transform .2s ease;
        }

        .endpoint-card.open .endpoint-toggle {
            transform: rotate(180deg);
        }

        .endpoint-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height .28s ease;
            border-top: 1px solid transparent;
        }

        .endpoint-card.open .endpoint-body {
            border-top-color: var(--border);
        }

        .endpoint-body-inner {
            padding: 14px 16px 16px;
            display: grid;
            gap: 14px;
        }

        .block-title {
            margin: 0 0 8px;
            font-size: 0.8rem;
            color: var(--text-soft);
            letter-spacing: 0.3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            background: var(--surface-soft);
        }

        th, td {
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid var(--border);
            font-size: 0.8rem;
            font-family: 'IBM Plex Mono', monospace;
            color: #2b4368;
        }

        tr:last-child td { border-bottom: 0; }

        pre {
            margin: 0;
            background: var(--surface-code);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.76rem;
            color: #17315b;
            line-height: 1.55;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-x: auto;
        }

        .request-pre { border-left: 4px solid #3b82f6; }
        .success-pre { border-left: 4px solid #22c55e; }
        .error-pre { border-left: 4px solid #ef4444; }

        .response-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 10px;
        }

        .response-title {
            font-size: 0.82rem;
            color: var(--text-soft);
        }

        /* Code Tabs */
        .code-tabs-container {
            margin-bottom: 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--surface);
        }

        .code-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 0;
            border-bottom: 2px solid var(--border);
            overflow-x: auto;
            padding: 0;
            background: var(--surface-soft);
            scrollbar-width: thin;
        }

        .code-tabs::-webkit-scrollbar {
            height: 6px;
        }

        .code-tabs::-webkit-scrollbar-track {
            background: var(--surface-soft);
        }

        .code-tabs::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .code-tab {
            border: 0;
            background: transparent;
            color: var(--text-soft);
            padding: 12px 20px;
            cursor: pointer;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.82rem;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: all 0.2s ease;
            position: relative;
            font-weight: 500;
        }

        .code-tab:hover {
            color: var(--text);
            background: rgba(79, 141, 255, 0.08);
        }

        .code-tab.active {
            color: var(--accent);
            background: var(--surface);
            border-bottom-color: var(--accent);
            font-weight: 600;
        }

        .code-tab-content {
            display: none;
            padding: 0;
        }

        .code-tab-content.active {
            display: block;
        }

        .code-tab-content pre {
            margin: 0;
            border: 0;
            border-radius: 0;
        }

        .mobile-toggle {
            display: none;
        }

        @media (max-width: 980px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .2s ease;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
            }

            .mobile-toggle {
                display: inline-flex;
                border: 1px solid var(--border);
                background: var(--surface);
                color: var(--text);
                border-radius: 8px;
                padding: 7px 10px;
                font-family: 'IBM Plex Mono', monospace;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <h2 class="logo">API Documentation</h2>
        <input id="endpointSearch" class="search" type="text" placeholder="Search endpoints...">

        <nav id="sidebarNav">
            @foreach ($sections as $sectionIndex => $section)
                @php
                    $sectionSlug = \Illuminate\Support\Str::slug($section['name']) . '-' . $sectionIndex;
                @endphp
                <div class="nav-section" data-section="{{ $sectionSlug }}">
                    <button type="button" class="nav-section-toggle">
                        <span>{{ $section['name'] }}</span>
                        <span>▾</span>
                    </button>
                    <div class="nav-links">
                        @foreach ($section['endpoints'] as $endpointIndex => $endpoint)
                            @php
                                $endpointId = $sectionSlug . '-endpoint-' . $endpointIndex;
                                $mClass = $methodClasses[strtoupper($endpoint['method'])] ?? 'method-post';
                            @endphp
                            <a href="#{{ $endpointId }}"
                               class="nav-link"
                               data-target="{{ $endpointId }}"
                               data-search="{{ strtolower($section['name'] . ' ' . $endpoint['name'] . ' ' . $endpoint['method'] . ' ' . $endpoint['url']) }}">
                                <span class="method-badge {{ $mClass }}">{{ strtoupper($endpoint['method']) }}</span>
                                <span>{{ $endpoint['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button type="button" class="mobile-toggle" id="mobileMenuToggle">Menu</button>
                <h1>API Reference</h1>
            </div>
            <div class="topbar-meta">
                <span class="pill">/api</span>
                <span class="pill">v1</span>
            </div>
        </header>

        <div class="content" id="content">
            @foreach ($sections as $sectionIndex => $section)
                @php
                    $sectionSlug = \Illuminate\Support\Str::slug($section['name']) . '-' . $sectionIndex;
                @endphp
                <section class="api-section" id="{{ $sectionSlug }}" data-section="{{ strtolower($section['name']) }}">
                    <h2>{{ $section['name'] }}</h2>

                    @foreach ($section['endpoints'] as $endpointIndex => $endpoint)
                        @php
                            $endpointId = $sectionSlug . '-endpoint-' . $endpointIndex;
                            $mClass = $methodClasses[strtoupper($endpoint['method'])] ?? 'method-post';
                            $requestBody = trim((string) $endpoint['request_body']);
                            $showRequest = $requestBody !== '' && $requestBody !== '{}';
                        @endphp

                        <article id="{{ $endpointId }}"
                                 class="endpoint-card"
                                 data-endpoint-id="{{ $endpointId }}"
                                 data-search="{{ strtolower($section['name'] . ' ' . $endpoint['name'] . ' ' . $endpoint['method'] . ' ' . $endpoint['url']) }}">
                            <div class="endpoint-header" role="button" tabindex="0" aria-expanded="false">
                                <div class="endpoint-header-left">
                                    <span class="method-badge {{ $mClass }}">{{ strtoupper($endpoint['method']) }}</span>
                                    <span class="endpoint-url">{{ $endpoint['url'] }}</span>
                                    <span class="endpoint-name">{{ $endpoint['name'] }}</span>
                                </div>
                                <span class="endpoint-toggle">▾</span>
                            </div>

                            <div class="endpoint-body">
                                <div class="endpoint-body-inner">
                                    <div>
                                        <h3 class="block-title">Required Headers</h3>
                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Key</th>
                                                <th>Value</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($endpoint['headers'] as $header)
                                                <tr>
                                                    <td>{{ $header['key'] }}</td>
                                                    <td>{{ $header['value'] }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if ($showRequest)
                                        <div>
                                            <h3 class="block-title">Request Body</h3>
                                            <pre class="request-pre">{{ $requestBody }}</pre>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="response-head">
                                            <span class="response-title">Success Response</span>
                                            <span class="status-badge {{ $statusClass($endpoint['success_code']) }}">{{ $endpoint['success_code'] }}</span>
                                        </div>
                                        <pre class="success-pre">{{ trim((string) $endpoint['success_body']) }}</pre>
                                    </div>

                                    @if (!empty($endpoint['errors']))
                                        <div>
                                            @php
                                                // Check if this is a multi-language code example (multiple error blocks with same code)
                                                $isCodeExample = count($endpoint['errors']) > 1 && 
                                                    collect($endpoint['errors'])->unique('code')->count() <= 2;
                                                
                                                // Extract language from code comment
                                                $getLanguage = function($code) {
                                                    if (preg_match('/===\s*(.+?)\s*===/', $code, $matches)) {
                                                        return trim($matches[1]);
                                                    }
                                                    return null;
                                                };
                                                
                                                // Normalize language names for cleaner display
                                                $normalizeLanguage = function($lang) {
                                                    $lang = trim($lang);
                                                    $map = [
                                                        'cURL' => 'shell',
                                                        'curl' => 'shell',
                                                        'bash' => 'shell',
                                                        'JavaScript' => 'nodejs',
                                                        'JavaScript (Fetch)' => 'nodejs',
                                                        'Javascript' => 'nodejs',
                                                        'Node.js' => 'nodejs',
                                                        'NodeJS' => 'nodejs',
                                                        'Python' => 'python',
                                                        'Python (Requests)' => 'python',
                                                        'PHP (Guzzle)' => 'php',
                                                        'PHP (cURL)' => 'php',
                                                        'PHP' => 'php',
                                                        'Go' => 'go',
                                                        'C#' => 'csharp',
                                                        'CSharp' => 'csharp',
                                                        'Java' => 'java',
                                                    ];
                                                    
                                                    return $map[$lang] ?? strtolower($lang);
                                                };
                                                
                                                $languages = [];
                                                $seenLanguages = []; // Track to avoid duplicates
                                                
                                                foreach ($endpoint['errors'] as $error) {
                                                    $rawLang = $getLanguage($error['body']);
                                                    if ($rawLang) {
                                                        $normalizedLang = $normalizeLanguage($rawLang);
                                                        
                                                        // Skip if we've already added this language
                                                        if (isset($seenLanguages[$normalizedLang])) {
                                                            continue;
                                                        }
                                                        
                                                        $seenLanguages[$normalizedLang] = true;
                                                        $languages[] = [
                                                            'name' => $normalizedLang,
                                                            'display' => $rawLang,
                                                            'code' => $error['code'],
                                                            'body' => $error['body']
                                                        ];
                                                    }
                                                }
                                                
                                                $showTabs = count($languages) > 1;
                                            @endphp
                                            
                                            @if ($showTabs)
                                                <h3 class="block-title">Code Examples (Select Language)</h3>
                                                <div class="code-tabs-container">
                                                    <div class="code-tabs" role="tablist" aria-label="Code examples in different programming languages">
                                                        @foreach ($languages as $index => $lang)
                                                            <button class="code-tab {{ $index === 0 ? 'active' : '' }}"
                                                                    role="tab"
                                                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                                                    aria-controls="lang-{{ $endpointId }}-{{ $index }}"
                                                                    data-tab="lang-{{ $endpointId }}-{{ $index }}"
                                                                    onclick="switchCodeTab(event, 'lang-{{ $endpointId }}-{{ $index }}')">
                                                                {{ $lang['name'] }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                    
                                                    @foreach ($languages as $index => $lang)
                                                        <div class="code-tab-content {{ $index === 0 ? 'active' : '' }}"
                                                             id="lang-{{ $endpointId }}-{{ $index }}"
                                                             role="tabpanel"
                                                             aria-labelledby="tab-{{ $endpointId }}-{{ $index }}">
                                                            <pre class="success-pre">{{ trim((string) $lang['body']) }}</pre>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <h3 class="block-title">Error Responses</h3>
                                                @foreach ($endpoint['errors'] as $error)
                                                    <div style="margin-bottom: 10px;">
                                                        <div class="response-head">
                                                            <span class="response-title">Error</span>
                                                            <span class="status-badge {{ $statusClass($error['code']) }}">{{ $error['code'] }}</span>
                                                        </div>
                                                        <pre class="error-pre">{{ trim((string) $error['body']) }}</pre>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </div>
    </main>
</div>

<script>
    // Tab switching function
    function switchCodeTab(event, tabId) {
        event.preventDefault();
        const button = event.currentTarget;
        const container = button.closest('.code-tabs-container');
        
        // Remove active class from all tabs and contents in this container
        container.querySelectorAll('.code-tab').forEach(tab => tab.classList.remove('active'));
        container.querySelectorAll('.code-tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to clicked tab and corresponding content
        button.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    const navSections = Array.from(document.querySelectorAll('.nav-section'));
    const navToggles = Array.from(document.querySelectorAll('.nav-section-toggle'));
    const navLinks = Array.from(document.querySelectorAll('.nav-link'));
    const endpointCards = Array.from(document.querySelectorAll('.endpoint-card'));
    const searchInput = document.getElementById('endpointSearch');
    const sidebar = document.getElementById('sidebar');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');

    const setCardOpen = (card, open) => {
        const body = card.querySelector('.endpoint-body');
        const header = card.querySelector('.endpoint-header');
        if (!body || !header) return;

        card.classList.toggle('open', open);
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
        body.style.maxHeight = open ? body.scrollHeight + 'px' : '0px';
    };

    const refreshOpenCardHeights = () => {
        endpointCards.forEach(card => {
            if (!card.classList.contains('open')) return;
            const body = card.querySelector('.endpoint-body');
            if (body) body.style.maxHeight = body.scrollHeight + 'px';
        });
    };

    navToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const section = toggle.closest('.nav-section');
            if (section) section.classList.toggle('collapsed');
        });
    });

    endpointCards.forEach(card => {
        const header = card.querySelector('.endpoint-header');
        if (!header) return;

        header.addEventListener('click', () => {
            const isOpen = card.classList.contains('open');
            setCardOpen(card, !isOpen);
        });

        header.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const isOpen = card.classList.contains('open');
                setCardOpen(card, !isOpen);
            }
        });
    });

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const targetId = link.dataset.target;
            const card = document.getElementById(targetId);
            if (card && !card.classList.contains('open')) {
                setCardOpen(card, true);
            }
            if (window.innerWidth <= 980) {
                sidebar.classList.remove('mobile-open');
            }
        });
    });

    searchInput.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();

        endpointCards.forEach(card => {
            const searchable = card.dataset.search || '';
            card.classList.toggle('hidden', query !== '' && !searchable.includes(query));
        });

        navLinks.forEach(link => {
            const searchable = link.dataset.search || '';
            link.classList.toggle('hidden', query !== '' && !searchable.includes(query));
        });

        document.querySelectorAll('.api-section').forEach(section => {
            const visibleCards = section.querySelectorAll('.endpoint-card:not(.hidden)').length;
            section.classList.toggle('hidden', visibleCards === 0);
        });

        navSections.forEach(section => {
            const visibleLinks = section.querySelectorAll('.nav-link:not(.hidden)').length;
            section.classList.toggle('hidden', visibleLinks === 0);
        });

        refreshOpenCardHeights();
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const id = entry.target.id;
            navLinks.forEach(link => {
                link.classList.toggle('active', link.dataset.target === id);
            });
        });
    }, {
        rootMargin: '-25% 0px -60% 0px',
        threshold: 0
    });

    endpointCards.forEach(card => observer.observe(card));

    navSections.forEach(section => section.classList.remove('collapsed'));
    if (endpointCards.length > 0) {
        setCardOpen(endpointCards[0], true);
    }

    window.addEventListener('resize', refreshOpenCardHeights);

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });
    }
</script>
</body>
</html>
