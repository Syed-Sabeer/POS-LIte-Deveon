@extends('layouts.app.master')

@section('title', 'Accounting V2')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header">
        <div class="page-title v2-title">
            <h4 class="fw-bold">Program Manager</h4>
            <h6>Accounting V2</h6>
        </div>
    </div>

    @foreach($groups as $group => $buttons)
        <div class="card v2-simple-card mb-4">
            <div class="card-header"><h5 class="fw-bold mb-0">{{ $group }}</h5></div>
            <div class="card-body">
                <div class="v2-button-grid">
                    @foreach($buttons as $button)
                        @can($button['permission'])
                            <a href="{{ route($button['route'], $button['params'] ?? []) }}" class="v2-big-button">
                                <i class="{{ $button['icon'] }}"></i>
                                <span>{{ $button['label'] }}</span>
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
