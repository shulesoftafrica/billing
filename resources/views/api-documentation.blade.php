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
    
    <!-- Prism.js for Syntax Highlighting -->
    <link id="prism-light" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css">
    <link id="prism-dark" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" disabled>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    
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

        [data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-soft: #1c2128;
            --surface-code: #0d1117;
            --border: #30363d;
            --text: #e6edf3;
            --text-soft: #c9d1d9;
            --accent: #58a6ff;
            --accent-soft: rgba(88, 166, 255, 0.15);
            --success: #3fb950;
            --danger: #f85149;
            --warning: #d29922;
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

        [data-theme="dark"] .sidebar {
            background: #0d1117;
        }

        .logo {
            font-size: 1.35rem;
            margin: 0 0 14px;
            letter-spacing: 0.3px;
            color: var(--text);
            font-weight: 600;
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

        .search::placeholder {
            color: var(--text-soft);
            opacity: 1;
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

        /* Authentication menu item styling */
        a.nav-section-toggle {
            transition: all 0.2s ease;
            font-weight: 600;
        }

        a.nav-section-toggle:hover {
            background: linear-gradient(135deg, rgba(79, 141, 255, 0.12) 0%, rgba(79, 141, 255, 0.05) 100%);
            color: var(--accent);
            transform: translateX(2px);
        }

        a.nav-section-toggle:hover span {
            color: var(--accent);
        }

        a.nav-section-toggle:active {
            transform: translateX(4px);
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

        [data-theme="dark"] .nav-link.active {
            color: #79c0ff;
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

        /* Dark mode method badges */
        [data-theme="dark"] .method-get { background: rgba(46, 160, 67, 0.25); color: #7ee787; border-color: rgba(46, 160, 67, .50); }
        [data-theme="dark"] .method-post { background: rgba(88, 166, 255, 0.25); color: #79c0ff; border-color: rgba(88, 166, 255, .50); }
        [data-theme="dark"] .method-put { background: rgba(210, 153, 34, 0.25); color: #f0b72f; border-color: rgba(210, 153, 34, .50); }
        [data-theme="dark"] .method-patch { background: rgba(191, 132, 255, 0.25); color: #e0b2ff; border-color: rgba(191, 132, 255, .50); }
        [data-theme="dark"] .method-delete { background: rgba(248, 81, 73, 0.25); color: #ffa198; border-color: rgba(248, 81, 73, .50); }

        .status-2xx { background: rgba(34, 197, 94, 0.20); color: #0f6b3f; border-color: rgba(22, 163, 74, .50); }
        .status-4xx { background: rgba(239, 68, 68, 0.20); color: #b91c1c; border-color: rgba(220, 38, 38, .50); }
        .status-5xx { background: rgba(245, 158, 11, 0.20); color: #92400e; border-color: rgba(217, 119, 6, .50); }
        .status-default { background: rgba(148, 163, 184, 0.20); color: #334155; border-color: rgba(100, 116, 139, .50); }

        /* Dark mode status badges */
        [data-theme="dark"] .status-2xx { background: rgba(46, 160, 67, 0.25); color: #7ee787; border-color: rgba(46, 160, 67, .50); }
        [data-theme="dark"] .status-4xx { background: rgba(248, 81, 73, 0.25); color: #ffa198; border-color: rgba(248, 81, 73, .50); }
        [data-theme="dark"] .status-5xx { background: rgba(210, 153, 34, 0.25); color: #f0b72f; border-color: rgba(210, 153, 34, .50); }
        [data-theme="dark"] .status-default { background: rgba(148, 163, 184, 0.25); color: #94a3b8; border-color: rgba(148, 163, 184, .50); }

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

        [data-theme="dark"] .topbar {
            background: rgba(13, 17, 23, 0.92);
        }

        .topbar h1 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--text);
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

        .theme-toggle {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--surface);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .theme-toggle:hover {
            background: var(--surface-soft);
            border-color: var(--accent);
            transform: scale(1.05);
        }

        .theme-toggle:active {
            transform: scale(0.95);
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
            color: var(--text);
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

        [data-theme="dark"] .endpoint-card.open {
            border-color: rgba(88, 166, 255, 0.50);
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

        [data-theme="dark"] .endpoint-url {
            color: #79c0ff;
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

        [data-theme="dark"] th,
        [data-theme="dark"] td {
            color: #c9d1d9;
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

        [data-theme="dark"] pre {
            color: #e6edf3;
        }

        .request-pre { border-left: 4px solid #3b82f6; }
        .success-pre { border-left: 4px solid #22c55e; }
        .error-pre { border-left: 4px solid #ef4444; }

        /* Copy Button Styles */
        .code-block-wrapper {
            position: relative;
        }

        .copy-button {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 6px 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-soft);
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.7rem;
            cursor: pointer;
            opacity: 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .code-block-wrapper:hover .copy-button {
            opacity: 1;
        }

        .copy-button:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
            transform: translateY(-1px);
        }

        .copy-button:active {
            transform: translateY(0);
        }

        .copy-button.copied {
            background: var(--success);
            color: white;
            border-color: var(--success);
            opacity: 1;
        }

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

        /* Authentication Guide Styles */
        .auth-guide {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 28px;
        }

        .auth-guide h2 {
            margin: 0 0 16px;
            font-size: 1.5rem;
            color: var(--text);
        }

        .auth-guide h3 {
            margin: 24px 0 12px;
            font-size: 1.1rem;
            color: var(--text);
        }

        .auth-guide h4 {
            margin: 16px 0 8px;
            font-size: 0.95rem;
            color: var(--text-soft);
        }

        .auth-guide p {
            margin: 8px 0;
            line-height: 1.6;
            color: var(--text-soft);
            font-size: 0.9rem;
        }

        [data-theme="dark"] .auth-guide p {
            color: #c9d1d9;
        }

        .auth-guide ul, .auth-guide ol {
            margin: 8px 0;
            padding-left: 24px;
            color: var(--text-soft);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .auth-guide li {
            margin: 4px 0;
        }

        .auth-guide code {
            background: var(--surface-code);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 2px 6px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
            color: #17315b;
        }

        [data-theme="dark"] .auth-guide code {
            color: #79c0ff;
        }

        .auth-steps {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin: 24px 0;
        }

        .auth-step-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text);
        }

        .request-pre, .success-pre, .error-pre {
            background: var(--surface-soft);
            border-left: 4px solid var(--border);
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            margin: 12px 0;
        }

        .request-pre {
            background: rgba(79, 141, 255, 0.05);
            border-left-color: #4F8DFF;
        }

        .success-pre {
            background: rgba(46, 213, 115, 0.05);
            border-left-color: #2ed573;
        }

        .error-pre {
            background: rgba(255, 71, 87, 0.05);
            border-left-color: #ff4757;
        }

        .auth-guide .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin: 12px 0;
            color: #b91c1c;
            font-size: 0.9rem;
        }

        .auth-guide .alert strong {
            color: #991b1b;
        }

        .auth-guide .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-left: 4px solid #22c55e;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            color: #15803d;
            font-size: 0.9rem;
        }

        .auth-guide .alert-success strong {
            color: #166534;
        }

        .auth-guide .alert-success a {
            color: #1976d2;
            text-decoration: underline;
        }

        .auth-guide .info-box {
            background: rgba(79, 141, 255, 0.1);
            border: 1px solid rgba(79, 141, 255, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin: 12px 0;
            color: #1e40af;
            font-size: 0.9rem;
        }

        /* Dark mode overrides for alert boxes */
        [data-theme="dark"] .auth-guide .alert {
            background: rgba(248, 81, 73, 0.15);
            border-color: rgba(248, 81, 73, 0.3);
            color: #ffa198;
        }

        [data-theme="dark"] .auth-guide .alert strong {
            color: #ff7b72;
        }

        [data-theme="dark"] .auth-guide .alert-success {
            background: rgba(63, 185, 80, 0.15);
            border-color: rgba(63, 185, 80, 0.3);
            border-left-color: #3fb950;
            color: #7ee787;
        }

        [data-theme="dark"] .auth-guide .alert-success strong {
            color: #a5e3b5;
        }

        [data-theme="dark"] .auth-guide .alert-success a {
            color: #58a6ff;
        }

        [data-theme="dark"] .auth-guide .info-box {
            background: rgba(88, 166, 255, 0.15);
            border-color: rgba(88, 166, 255, 0.3);
            color: #79c0ff;
        }

        .auth-steps {
            display: grid;
            gap: 20px;
            margin: 20px 0;
        }

        .auth-step {
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .auth-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 12px;
        }

        .auth-step-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
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

            .auth-guide {
                padding: 16px;
            }
        }

        /* Alert Boxes */
        .alert {
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
        }

        .alert-warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            color: #663c00;
        }

        .alert-warning h4 {
            margin-top: 0;
            color: #e65100;
        }

        .alert-warning ul {
            margin: 10px 0 0 20px;
            line-height: 1.8;
        }

        .alert-warning strong {
            color: #663c00;
        }

        .alert-warning code {
            background: rgba(255, 152, 0, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
        }

        /* Dark mode alert styles */
        [data-theme="dark"] .alert-warning {
            background: rgba(210, 153, 34, 0.15);
            border-left-color: #d29922;
            color: #e6c384;
        }

        [data-theme="dark"] .alert-warning h4 {
            color: #f0b72f;
        }

        [data-theme="dark"] .alert-warning strong {
            color: #f0b72f;
        }

        [data-theme="dark"] .alert-warning code {
            background: rgba(210, 153, 34, 0.25);
            color: #f0b72f;
        }

        /* Alert Info (Blue) */
        .alert-info {
            background: #e8f4fd;
            border-left: 4px solid #2196F3;
            color: #0d47a1;
        }

        .alert-info strong {
            color: #0d47a1;
        }

        .alert-info code {
            background: rgba(33, 150, 243, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
            color: #1565c0;
        }

        .alert-info ol, .alert-info ul {
            margin: 10px 0 0 20px;
            line-height: 1.8;
        }

        /* Dark mode info alert */
        [data-theme="dark"] .alert-info {
            background: rgba(88, 166, 255, 0.15);
            border-left-color: #58a6ff;
            color: #79c0ff;
        }

        [data-theme="dark"] .alert-info strong {
            color: #58a6ff;
        }

        [data-theme="dark"] .alert-info code {
            background: rgba(88, 166, 255, 0.25);
            color: #58a6ff;
        }

        /* Alert Success (Green) */
        .alert-success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #1b5e20;
        }

        .alert-success strong {
            color: #1b5e20;
        }

        .alert-success code {
            background: rgba(76, 175, 80, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
            color: #2e7d32;
        }

        .alert-success ol, .alert-success ul {
            margin: 10px 0 0 20px;
            line-height: 1.8;
        }

        /* Dark mode success alert */
        [data-theme="dark"] .alert-success {
            background: rgba(46, 160, 67, 0.15);
            border-left-color: #3fb950;
            color: #7ee787;
        }

        [data-theme="dark"] .alert-success strong {
            color: #3fb950;
        }

        [data-theme="dark"] .alert-success code {
            background: rgba(46, 160, 67, 0.25);
            color: #3fb950;
        }

        /* Inline notice boxes */
        .notice-box {
            margin-top: 15px;
            padding: 12px;
            border-radius: 4px;
        }

        .notice-warning {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            color: #856404;
        }

        .notice-warning strong {
            color: #856404;
        }

        .notice-warning code {
            background: rgba(255, 193, 7, 0.2);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
        }

        .notice-info {
            background: #e3f2fd;
            border-left: 3px solid #2196F3;
            color: #0d47a1;
        }

        .notice-info strong {
            color: #0d47a1;
        }

        .notice-info code {
            background: rgba(33, 150, 243, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
        }

        .notice-success {
            background: #e8f5e9;
            border-left: 3px solid #4caf50;
            color: #1b5e20;
        }

        .notice-success strong {
            color: #1b5e20;
        }

        .notice-success code {
            background: rgba(76, 175, 80, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.85em;
        }

        /* Dark mode notice boxes */
        [data-theme="dark"] .notice-warning {
            background: rgba(210, 153, 34, 0.15);
            border-left-color: #d29922;
            color: #e6c384;
        }

        [data-theme="dark"] .notice-warning strong {
            color: #f0b72f;
        }

        [data-theme="dark"] .notice-warning code {
            background: rgba(210, 153, 34, 0.25);
            color: #f0b72f;
        }

        [data-theme="dark"] .notice-info {
            background: rgba(88, 166, 255, 0.15);
            border-left-color: #58a6ff;
            color: #79c0ff;
        }

        [data-theme="dark"] .notice-info strong {
            color: #58a6ff;
        }

        [data-theme="dark"] .notice-info code {
            background: rgba(88, 166, 255, 0.25);
            color: #58a6ff;
        }

        [data-theme="dark"] .notice-success {
            background: rgba(46, 160, 67, 0.15);
            border-left-color: #3fb950;
            color: #7ee787;
        }

        [data-theme="dark"] .notice-success strong {
            color: #3fb950;
        }

        [data-theme="dark"] .notice-success code {
            background: rgba(46, 160, 67, 0.25);
            color: #3fb950;
        }

        /* Section headings */
        .section-heading {
            margin-top: 40px;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        [data-theme="dark"] .section-heading {
            color: #e6edf3;
            border-bottom-color: #30363d;
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <h2 class="logo">API Documentation</h2>
        <input id="endpointSearch" class="search" type="text" placeholder="Search endpoints...">

        <nav id="sidebarNav">
            {{-- Authentication Section --}}
            <div class="nav-section">
                <a href="#authentication-guide" class="nav-section-toggle" style="text-decoration: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>🔐 Authentication</span>
                    <span style="font-size: 1.2em;">→</span>
                </a>
            </div>

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
                <button type="button" class="theme-toggle" id="themeToggle" title="Toggle dark mode">
                    <span id="themeIcon">🌙</span>
                </button>
                <span class="pill">/api</span>
                <span class="pill">v1</span>
            </div>
        </header>

        <div class="content" id="content">
            {{-- Authentication Guide Section --}}
            <section class="auth-guide" id="authentication-guide">
                <h2>🔐 Authentication Guide</h2>
                <p>This API uses <strong>OAuth 2.0 Client Credentials</strong> for authentication. Follow this guide to obtain your API credentials and start making authenticated requests.</p>

                <div class="info-box">
                    <strong>Quick Reference:</strong><br>
                    <strong>Token Expiration:</strong> 90 days (7,776,000 seconds)<br>
                    <strong>Rate Limit:</strong> 60 requests per minute<br>
                    <strong>Base URL:</strong> <code>{{ url('/') }}</code>
                </div>

                <div class="alert-success">
                    <strong>🎯 Getting Started:</strong><br>
                    <strong>Step 1:</strong> Register your account and organization through the <strong>Dashboard</strong> at <a href="{{ url('/dashboard/register') }}" style="color: #1976d2; text-decoration: underline;">{{ url('/dashboard/register') }}</a><br>
                    <strong>Step 2:</strong> After registration, use the dashboard or the API endpoints below to create your OAuth client credentials<br>
                    <strong>Step 3:</strong> Use your client credentials to get access tokens and start making API requests
                </div>

                <h3>📋 Option 1: Create OAuth Client via Dashboard (Recommended)</h3>
                <p>After registering through the dashboard, you can generate your API credentials directly from your account settings. This is the easiest way to get started.</p>

                <h3>📋 Option 2: Create OAuth Client via API</h3>
                <p>Alternatively, you can programmatically create OAuth clients using your user token:</p>

                <div class="auth-steps">
                    <div class="auth-step">
                        <div class="auth-step-title">
                            <span class="auth-step-number">1</span>
                            <span>Login to Get User Token</span>
                        </div>
                        <p>Use your dashboard credentials to get a user token:</p>
                        <pre class="request-pre">POST {{ url('/api/v1/auth/login') }}
Content-Type: application/json

{
  "email": "your-email@example.com",
  "password": "YourPassword123!"
}</pre>
                        <p><strong>Response:</strong> You'll receive a user token.</p>
                        <pre class="success-pre">{
  "access_token": "shulesoft_1|abc123xyz...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "Your Name",
    "email": "your-email@example.com",
    "role": "admin"
  }
}</pre>
                    </div>

                    <div class="auth-step">
                        <div class="auth-step-title">
                            <span class="auth-step-number">2</span>
                            <span>Create OAuth Client</span>
                        </div>
                        <p>Use your user token to create API client credentials:</p>
                        <pre class="request-pre">POST {{ url('/api/v1/oauth/clients') }}
Authorization: Bearer {YOUR_USER_TOKEN_FROM_STEP_1}
Content-Type: application/json

{
  "name": "Production API Client",
  "environment": "live",
  "allowed_scopes": ["*"]
}</pre>
                        <p class="text-muted small"><strong>🔒 Security:</strong> The client is automatically created for your organization. You cannot create clients for other organizations.</p>
                        <div class="alert">
                            <strong>⚠️ CRITICAL:</strong> Save your <code>client_id</code> and <code>client_secret</code> immediately! The <code>client_secret</code> is shown only once and cannot be retrieved again.
                        </div>
                        <p><strong>Response:</strong></p>
                        <pre class="success-pre">{
  "message": "OAuth client created successfully",
  "client": {
    "client_id": "org_live_client_abc123xyz...",
    "client_secret": "org_live_secret_xyz789def...",
    "environment": "live",
    "allowed_scopes": ["*"]
  }
}</pre>
                    </div>

                    <div class="auth-step">
                        <div class="auth-step-title">
                            <span class="auth-step-number">3</span>
                            <span>Get Access Token (For API Requests)</span>
                        </div>
                        <p>Exchange your client credentials for an access token:</p>
                        <pre class="request-pre">POST {{ url('/api/v1/oauth/token') }}
Content-Type: application/json

{
  "grant_type": "client_credentials",
  "client_id": "org_live_client_abc123xyz...",
  "client_secret": "org_live_secret_xyz789def...",
  "scope": "*"
}</pre>
                        <p><strong>Response:</strong></p>
                        <pre class="success-pre">{
  "access_token": "shulesoft_2|def456ghi789...",
  "token_type": "Bearer",
  "expires_in": 7776000,
  "scope": "*",
  "organization_id": 1
}</pre>
                        <p><strong>Use this access token</strong> in all your API requests:</p>
                        <pre class="request-pre">GET {{ url('/api/v1/products') }}
Authorization: Bearer shulesoft_2|def456ghi789...
Accept: application/json</pre>
                    </div>
                </div>
       

                <h3>🔄 Token Management</h3>
                <ul>
                    <li><strong>Token Lifetime:</strong> Access tokens expire after 90 days</li>
                    <li><strong>Token Caching:</strong> Cache tokens and reuse them until expiration</li>
                    <li><strong>Token Refresh:</strong> When you receive a 401 error, request a new token</li>
                    <li><strong>Security:</strong> Store credentials in environment variables, never in code</li>
                </ul>

                <h3>🔐 Best Practices</h3>
                <ol>
                    <li><strong>Never commit credentials to version control</strong> - Use environment variables</li>
                    <li><strong>Use separate clients for different environments</strong> - Create <code>test</code> clients for development</li>
                    <li><strong>Implement automatic token refresh</strong> - Handle 401 errors gracefully</li>
                    <li><strong>Cache access tokens</strong> - Reduce unnecessary token requests</li>
                    <li><strong>Monitor token usage</strong> - Check last_used_at in client list endpoint</li>
                </ol>

                <h3>❓ Common Errors</h3>
                <div class="auth-step">
                    <h4>Invalid Client Credentials (401)</h4>
                    <pre class="error-pre">{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}</pre>
                    <p><strong>Solution:</strong> Verify your <code>client_id</code> and <code>client_secret</code> are correct.</p>
                </div>

                <div class="auth-step">
                    <h4>Expired Token (401)</h4>
                    <pre class="error-pre">{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}</pre>
                    <p><strong>Solution:</strong> Request a new access token using your client credentials.</p>
                </div>

                <div class="auth-step">
                    <h4>Rate Limit Exceeded (429)</h4>
                    <pre class="error-pre">{
  "message": "Too Many Attempts."
}</pre>
                    <p><strong>Solution:</strong> Wait 60 seconds before making more requests. Implement exponential backoff.</p>
                </div>

                <h3>🛠️ Managing Your OAuth Clients</h3>
                <p>You can manage your OAuth clients using these endpoints:</p>

                <h4>List All Clients</h4>
                <pre class="request-pre">GET {{ url('/api/v1/oauth/clients') }}
Authorization: Bearer {YOUR_USER_TOKEN}</pre>

                <h4>Revoke a Client</h4>
                <pre class="request-pre">DELETE {{ url('/api/v1/oauth/clients/{client_id}') }}
Authorization: Bearer {YOUR_USER_TOKEN}</pre>

                <div class="info-box">
                    <strong>📖 Ready to start?</strong> Use your access token with any of the endpoints documented below. All endpoints require the <code>Authorization: Bearer {token}</code> header.
                </div>
            </section>

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

    // Handle authentication link click
    const authLink = document.querySelector('a.nav-section-toggle[href="#authentication-guide"]');
    if (authLink) {
        authLink.addEventListener('click', () => {
            if (window.innerWidth <= 980) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }

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

    // Dark/Light Mode Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    // Function to switch Prism.js theme
    const switchPrismTheme = (theme) => {
        const prismLight = document.getElementById('prism-light');
        const prismDark = document.getElementById('prism-dark');
        
        if (theme === 'dark') {
            prismLight.setAttribute('disabled', 'true');
            prismDark.removeAttribute('disabled');
        } else {
            prismDark.setAttribute('disabled', 'true');
            prismLight.removeAttribute('disabled');
        }
    };

    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('theme') || 
                        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Apply theme on page load
    htmlElement.setAttribute('data-theme', currentTheme);
    themeIcon.textContent = currentTheme === 'dark' ? '☀️' : '🌙';
    switchPrismTheme(currentTheme);

    // Toggle theme
    themeToggle.addEventListener('click', () => {
        const theme = htmlElement.getAttribute('data-theme');
        const newTheme = theme === 'dark' ? 'light' : 'dark';
        
        htmlElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeIcon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        switchPrismTheme(newTheme);
    });

    // Copy Buttons for Code Blocks
    document.querySelectorAll('pre').forEach((pre) => {
        // Don't add copy button if already wrapped
        if (pre.parentElement.classList.contains('code-block-wrapper')) return;

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';
        pre.parentNode.insertBefore(wrapper, pre);
        wrapper.appendChild(pre);

        // Create copy button
        const button = document.createElement('button');
        button.className = 'copy-button';
        button.innerHTML = '<span>📋</span><span>Copy</span>';
        button.setAttribute('aria-label', 'Copy code to clipboard');
        
        // Add click handler
        button.addEventListener('click', async () => {
            const code = pre.textContent;
            
            try {
                await navigator.clipboard.writeText(code);
                button.innerHTML = '<span>✓</span><span>Copied!</span>';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = '<span>📋</span><span>Copy</span>';
                    button.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
                button.innerHTML = '<span>✗</span><span>Failed</span>';
                setTimeout(() => {
                    button.innerHTML = '<span>📋</span><span>Copy</span>';
                }, 2000);
            }
        });

        wrapper.appendChild(button);
    });
</script>
</body>
</html>
