@extends('layouts.app.master')

@section('title', 'Account Tree')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Chart of Accounts Tree</h4></div><div class="page-btn"><a href="{{ route('accounts.index') }}" class="btn btn-secondary">Back</a></div></div>

<div class="card"><div class="card-body">
    @php
        $render = function(array $nodes, int $depth = 0) use (&$render) {
            $html = '<ul class="list-unstyled ms-' . min($depth * 2, 5) . '">';
            foreach ($nodes as $node) {
                $html .= '<li class="mb-2"><span class="fw-semibold">' . e($node['code']) . '</span> - ' . e($node['name']) . ' <span class="text-muted">(' . e(ucfirst($node['type'])) . ')</span>';
                if (!empty($node['children'])) {
                    $html .= $render($node['children'], $depth + 1);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
            return $html;
        };
    @endphp

    {!! $render($tree) !!}
</div></div>
@endsection
