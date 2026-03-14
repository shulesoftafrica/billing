@props(['parameters'])

<table>
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
            <th>Description</th>
            <th>Required</th>
        </tr>
    </thead>
    <tbody>
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
    </tbody>
</table>
