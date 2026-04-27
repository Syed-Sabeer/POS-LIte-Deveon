@extends('layouts.app.master')

@section('title', $voucher->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $voucher->voucher_no }}</h4><h6>Journal Voucher</h6></div><div class="v2-actions"><a class="btn btn-primary" href="{{ route('v2.journal.print',$voucher) }}">Print</a><a class="btn btn-secondary" href="{{ route('v2.journal.index') }}">List</a></div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Account Code</th><th>Account Name</th><th>Particulars</th><th>Post Date</th><th>Debit</th><th>Credit</th></tr></thead><tbody>@foreach($voucher->lines as $line)<tr><td>{{ $line->account_code }}</td><td>{{ $line->account_name }}</td><td>{{ $line->particulars }}</td><td>{{ optional($line->post_date)->format('Y-m-d') }}</td><td>{{ number_format((float)$line->debit,2) }}</td><td>{{ number_format((float)$line->credit,2) }}</td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection
