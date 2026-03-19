@props([
    'code' => null,
    'language' => 'json',
    'label' => 'code'
])

@php
    $labelClass = match($label) {
        'request' => 'request-pre',
        'success' => 'success-pre',
        'error' => 'error-pre',
        default => 'request-pre'
    };
    
    // Use the code prop if provided, otherwise use the slot content
    $codeContent = $code ?? $slot;
@endphp

<div class="code-block-wrapper">
    <pre class="{{ $labelClass }}"><code class="language-{{ $language }}">{{ trim($codeContent) }}</code></pre>
    <button class="copy-button" aria-label="Copy code to clipboard">
        <span>📋</span><span>Copy</span>
    </button>
</div>
