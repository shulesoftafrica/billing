@props([
    'id',
    'method',
    'url',
    'title',
    'description' => null
])

@php
    $methodClasses = [
        'GET' => 'method-get',
        'POST' => 'method-post',
        'PUT' => 'method-put',
        'PATCH' => 'method-patch',
        'DELETE' => 'method-delete',
    ];
    $methodClass = $methodClasses[$method] ?? 'method-get';
    $searchString = strtolower("$title $method $url");
@endphp

<div class="endpoint-card" id="{{ $id }}" data-search="{{ $searchString }}">
    <div class="endpoint-header" role="button" tabindex="0" aria-expanded="false">
        <div class="endpoint-header-left">
            <span class="method-badge {{ $methodClass }}">{{ $method }}</span>
            <code class="endpoint-url">{{ $url }}</code>
            <span class="endpoint-name">{{ $title }}</span>
        </div>
        <span class="endpoint-toggle">▼</span>
    </div>

    <div class="endpoint-body">
        <div class="endpoint-body-inner">
            @if($description)
                <p style="margin: 0 0 16px; color: var(--text-soft); line-height: 1.6;">{{ $description }}</p>
            @endif

            @if(isset($urlParams) && !empty(trim($urlParams)))
                <div>
                    <h4 class="block-title">URL Parameters</h4>
                    <div style="background: var(--surface-soft); padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                        {!! $urlParams !!}
                    </div>
                </div>
            @endif

            @if(isset($headers) && !empty(trim($headers)))
                <div>
                    <h4 class="block-title">Required Headers</h4>
                    {{ $headers }}
                </div>
            @endif

            @if(isset($requestBody) && !empty(trim($requestBody)))
                <div>
                    <h4 class="block-title">Request Body</h4>
                    {{ $requestBody }}
                </div>
            @endif

            @if(isset($howToUse) && !empty(trim($howToUse)))
                <div>
                    <h4 class="block-title" style="font-size: 1rem; margin-bottom: 12px;">How to Use This Endpoint</h4>
                    <div style="background: var(--surface-soft); padding: 16px; border-radius: 8px; border-left: 4px solid var(--accent); margin-bottom: 16px;">
                        {!! $howToUse !!}
                    </div>
                </div>
            @endif

            @if(isset($examples) && !empty(trim($examples)))
                <div>
                    <h4 class="block-title">Example Requests</h4>
                    {{ $examples }}
                </div>
            @endif

            @if(isset($responses) && !empty(trim($responses)))
                {{ $responses }}
            @endif
        </div>
    </div>
</div>
