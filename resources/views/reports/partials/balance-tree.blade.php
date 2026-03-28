@if(!empty($nodes))
    <ul class="list-unstyled">
        @foreach($nodes as $node)
            <li class="mb-1">
                <div class="d-flex justify-content-between">
                    <span>{{ $node['code'] }} - {{ $node['name'] }}</span>
                    <span>PKR {{ number_format($node['balance'],2) }}</span>
                </div>
                @includeWhen(!empty($node['children']), 'reports.partials.balance-tree', ['nodes' => $node['children']])
            </li>
        @endforeach
    </ul>
@else
    <div class="text-muted">No accounts found.</div>
@endif
