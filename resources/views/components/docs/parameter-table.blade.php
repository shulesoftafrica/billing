@props(['parameters' => null, 'headers' => null])

<table>
    <thead>
        <tr>
            @if($headers)
                {{-- Custom headers --}}
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            @else
                {{-- Default headers --}}
                <th>Key</th>
                <th>Value</th>
                <th>Description</th>
                <th>Required</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @if($parameters)
            {{-- Array-based parameters --}}
            @foreach($parameters as $param)
                <tr>
                    <td><code>{{ $param['key'] }}</code></td>
                    <td><code>{{ $param['value'] }}</code></td>
                    <td>{{ $param['description'] ?? '' }}</td>
                    <td>
                        @if(isset($param['required']) && $param['required'])
                            <span class="badge badge-required">Required</span>
                        @else
                            <span class="badge badge-optional">Optional</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        @else
            {{-- Slot-based custom content --}}
            {{ $slot }}
        @endif
    </tbody>
</table>
