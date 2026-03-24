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
        --surface-code: #1c2128;
        --border: #30363d;
        --text: #f0f6fc;
        --text-soft: #e6edf3;
        --accent: #58a6ff;
        --accent-soft: rgba(88, 166, 255, 0.15);
        --success: #3fb950;
        --danger: #f85149;
        --warning: #d29922;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Syne', system-ui, -apple-system, sans-serif;
        scroll-behavior: smooth;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }

    .layout { min-height: 100vh; }

    /* Sidebar */
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
        transition: transform 0.2s ease;
    }

    [data-theme="dark"] .sidebar { background: #0d1117; }

    .logo {
        font-size: 1.35rem;
        margin: 0 0 14px;
        letter-spacing: 0.3px;
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
        transition: all 0.2s ease;
    }

    .search::placeholder { color: var(--text-soft); opacity: 1; }
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
        transition: all 0.2s ease;
        text-align: left;
    }

    .nav-section-toggle span:last-child {
        color: var(--text-soft);
        transition: transform 0.2s ease;
    }

    .nav-section.collapsed .nav-section-toggle span:last-child {
        transform: rotate(-90deg);
    }

    a.nav-section-toggle {
        text-decoration: none;
        font-weight: 600;
    }

    a.nav-section-toggle:hover {
        background: linear-gradient(135deg, rgba(79, 141, 255, 0.12) 0%, rgba(79, 141, 255, 0.05) 100%);
        color: var(--accent);
        transform: translateX(2px);
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
        transition: all 0.2s ease;
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

    [data-theme="dark"] .nav-link.active { color: #79c0ff; }

    /* Method Badges */
    .method-badge, .status-badge {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        padding: 4px 8px;
        border-radius: 999px;
        border: 1px solid transparent;
        white-space: nowrap;
        font-weight: 600;
    }

    .method-get { background: rgba(34, 197, 94, 0.20); color: #0f6b3f; border-color: rgba(22, 163, 74, .50); }
    .method-post { background: rgba(59, 130, 246, 0.20); color: #1d4ed8; border-color: rgba(37, 99, 235, .50); }
    .method-put { background: rgba(245, 158, 11, 0.20); color: #92400e; border-color: rgba(217, 119, 6, .50); }
    .method-patch { background: rgba(168, 85, 247, 0.20); color: #6b21a8; border-color: rgba(147, 51, 234, .50); }
    .method-delete { background: rgba(239, 68, 68, 0.20); color: #b91c1c; border-color: rgba(220, 38, 38, .50); }

    [data-theme="dark"] .method-get { background: rgba(46, 160, 67, 0.25); color: #7ee787; border-color: rgba(46, 160, 67, .50); }
    [data-theme="dark"] .method-post { background: rgba(88, 166, 255, 0.25); color: #79c0ff; border-color: rgba(88, 166, 255, .50); }
    [data-theme="dark"] .method-put { background: rgba(210, 153, 34, 0.25); color: #f0b72f; border-color: rgba(210, 153, 34, .50); }
    [data-theme="dark"] .method-patch { background: rgba(191, 132, 255, 0.25); color: #e0b2ff; border-color: rgba(191, 132, 255, .50); }
    [data-theme="dark"] .method-delete { background: rgba(248, 81, 73, 0.25); color: #ffa198; border-color: rgba(248, 81, 73, .50); }

    /* Status Badges */
    .status-2xx { background: rgba(34, 197, 94, 0.20); color: #0f6b3f; border-color: rgba(22, 163, 74, .50); }
    .status-4xx { background: rgba(239, 68, 68, 0.20); color: #b91c1c; border-color: rgba(220, 38, 38, .50); }
    .status-5xx { background: rgba(245, 158, 11, 0.20); color: #92400e; border-color: rgba(217, 119, 6, .50); }

    [data-theme="dark"] .status-2xx { background: rgba(46, 160, 67, 0.25); color: #7ee787; border-color: rgba(46, 160, 67, .50); }
    [data-theme="dark"] .status-4xx { background: rgba(248, 81, 73, 0.25); color: #ffa198; border-color: rgba(248, 81, 73, .50); }
    [data-theme="dark"] .status-5xx { background: rgba(210, 153, 34, 0.25); color: #f0b72f; border-color: rgba(210, 153, 34, .50); }

    /* Main Content */
    .main { margin-left: var(--sidebar-width); min-height: 100vh; }

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
        gap: 16px;
    }

    [data-theme="dark"] .topbar { background: rgba(13, 17, 23, 0.92); }

    .topbar h1 { font-size: 1.3rem; margin: 0; }

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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--surface);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.2s ease;
    }

    .theme-toggle:hover {
        background: var(--surface-soft);
        border-color: var(--accent);
        transform: scale(1.05);
    }

    .mobile-toggle {
        display: none;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        border-radius: 8px;
        padding: 7px 10px;
        font-family: 'IBM Plex Mono', monospace;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .content { padding: 24px; max-width: 1400px; }

    /* Endpoint Cards */
    .endpoint-card {
        border: 1px solid var(--border);
        background: var(--surface);
        border-radius: var(--radius);
        margin-bottom: 16px;
        overflow: hidden;
        transition: border-color 0.2s ease;
    }

    .endpoint-card.open { border-color: rgba(79, 141, 255, 0.45); }
    [data-theme="dark"] .endpoint-card.open { border-color: rgba(88, 166, 255, 0.50); }

    .endpoint-header {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        transition: background 0.2s ease;
    }

    .endpoint-header:hover { background: var(--surface-soft); }

    .endpoint-header-left {
        min-width: 0;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        flex: 1;
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

    [data-theme="dark"] .endpoint-url { color: #79c0ff; }

    .endpoint-name {
        font-size: 0.9rem;
        color: var(--text-soft);
        font-weight: 500;
    }

    .endpoint-toggle {
        color: var(--text-soft);
        transition: transform 0.2s ease;
        font-size: 1.2rem;
    }

    .endpoint-card.open .endpoint-toggle { transform: rotate(180deg); }

    .endpoint-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        border-top: 1px solid transparent;
    }

    .endpoint-card.open .endpoint-body {
        max-height: 10000px;
        border-top-color: var(--border);
    }

    .endpoint-body-inner {
        padding: 20px;
        display: grid;
        gap: 20px;
    }

    .block-title {
        margin: 0 0 10px;
        font-size: 0.85rem;
        color: var(--text-soft);
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    /* Tables */
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
        padding: 10px 12px;
        border-bottom: 1px solid var(--border);
        font-size: 0.82rem;
        font-family: 'IBM Plex Mono', monospace;
    }

    th {
        background: var(--surface);
        font-weight: 600;
        color: var(--text);
    }

    td { color: var(--text-soft); }
    [data-theme="dark"] td { 
        color: #e6edf3;
        font-weight: 400;
    }

    tr:last-child td { border-bottom: 0; }

    /* Code Blocks */
    pre {
        margin: 0;
        background: var(--surface-code);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.82rem;
        color: var(--text);
        line-height: 1.7;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-x: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }

    /* Enhanced dark mode code block styling */
    [data-theme="dark"] pre {
        background: #161b22;
        border-color: #30363d;
        font-weight: 400;
    }

    [data-theme="dark"] code {
        font-weight: 400;
        text-shadow: none;
        color: #f0f6fc;
    }

    /* Comprehensive Prism.js color overrides for excellent dark mode readability */
    [data-theme="dark"] pre[class*="language-"],
    [data-theme="dark"] code[class*="language-"] {
        text-shadow: none !important;
        font-weight: 400 !important;
        color: #f0f6fc !important;
    }

    [data-theme="dark"] .token.comment,
    [data-theme="dark"] .token.prolog,
    [data-theme="dark"] .token.doctype,
    [data-theme="dark"] .token.cdata {
        color: #8b949e !important;
    }

    [data-theme="dark"] .token.string,
    [data-theme="dark"] .token.attr-value {
        color: #a5d6ff !important;
        font-weight: 400 !important;
    }

    [data-theme="dark"] .token.number {
        color: #79c0ff !important;
        font-weight: 500 !important;
    }

    [data-theme="dark"] .token.boolean,
    [data-theme="dark"] .token.constant {
        color: #ffa657 !important;
        font-weight: 500 !important;
    }

    [data-theme="dark"] .token.property,
    [data-theme="dark"] .token.tag {
        color: #7ee787 !important;
        font-weight: 400 !important;
    }

    [data-theme="dark"] .token.punctuation,
    [data-theme="dark"] .token.operator {
        color: #e6edf3 !important;
        font-weight: 400 !important;
    }

    [data-theme="dark"] .token.keyword,
    [data-theme="dark"] .token.function {
        color: #d2a8ff !important;
        font-weight: 500 !important;
    }

    [data-theme="dark"] .token.class-name {
        color: #ffa657 !important;
    }

    [data-theme="dark"] .token.null,
    [data-theme="dark"] .token.undefined {
        color: #ff7b72 !important;
        font-weight: 500 !important;
    }

    .code-block-wrapper {
        position: relative;
        margin: 12px 0;
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
    }

    .code-block-wrapper:hover .copy-button { opacity: 1; }

    .copy-button:hover {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .copy-button.copied {
        background: var(--success);
        color: white;
        border-color: var(--success);
        opacity: 1;
    }

    /* Code Tabs */
    .code-tabs-container {
        margin: 16px 0;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        background: var(--surface);
    }

    .code-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid var(--border);
        overflow-x: auto;
        background: var(--surface-soft);
        scrollbar-width: thin;
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

    .code-tab-content.active { display: block; }
    .code-tab-content pre {
        margin: 0;
        border: 0;
        border-radius: 0;
    }

    /* Section Styling */
    .api-section {
        margin-bottom: 40px;
        scroll-margin-top: 80px;
    }

    .api-section h2 {
        font-size: 1.8rem;
        margin: 0 0 20px;
        color: var(--text);
    }

    .api-section h3 {
        font-size: 1.3rem;
        margin: 24px 0 12px;
        color: var(--text);
    }

    .api-section p {
        line-height: 1.6;
        color: var(--text-soft);
        margin: 10px 0;
    }

    /* Responsive */
    @media (max-width: 980px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.mobile-open {
            transform: translateX(0);
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
        }

        .main { margin-left: 0; }
        .mobile-toggle { display: inline-flex; }
        .content { padding: 16px; }

        .endpoint-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .topbar-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
    }

    /* Hidden utility */
    .hidden { display: none !important; }

    /* Smooth scrolling */
    html { scroll-padding-top: 80px; }
</style>
