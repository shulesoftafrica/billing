@props(['examples'])

<div class="code-tabs-container">
    <div class="code-tabs">
        @foreach($examples as $index => $example)
            <button 
                class="code-tab {{ $index === 0 ? 'active' : '' }}" 
                onclick="switchCodeTab(event, 'tab-content-{{ md5(json_encode($example)) }}')"
            >
                {{ $example['label'] }}
            </button>
        @endforeach
    </div>

    @foreach($examples as $index => $example)
        <div id="tab-content-{{ md5(json_encode($example)) }}" class="code-tab-content {{ $index === 0 ? 'active' : '' }}">
            <x-docs.code-block 
                :code="$example['code']" 
                :language="$example['language']" 
                :label="$example['type'] ?? 'code'" 
            />
        </div>
    @endforeach
</div>
