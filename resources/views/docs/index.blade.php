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
    <link id="prism-dark" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" disabled>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    
    @include('docs.partials.styles')
</head>
<body>
<div class="layout">
    @include('docs.partials.sidebar')

    <main class="main">
        @include('docs.partials.topbar')

        <div class="content" id="content">
            @include('docs.sections.authentication')
            @include('docs.sections.products')
            @include('docs.sections.invoices')
            @include('docs.sections.subscriptions')
            @include('docs.sections.customers')
            @include('docs.sections.payments')
            @include('docs.sections.taxes')
            @include('docs.sections.webhooks')
            @include('docs.sections.organizations')
            @include('docs.sections.wallets')
        </div>
    </main>
</div>

@include('docs.partials.scripts')
</body>
</html>
