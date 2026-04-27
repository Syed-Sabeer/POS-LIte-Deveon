<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route(config('accounting_v2.enabled') ? 'v2.dashboard' : 'accounting.sales') }}" class="logo logo-normal">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a href="{{ route(config('accounting_v2.enabled') ? 'v2.dashboard' : 'accounting.sales') }}" class="logo logo-white">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a href="{{ route(config('accounting_v2.enabled') ? 'v2.dashboard' : 'accounting.sales') }}" class="logo-small">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
    </div>

    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                @if(config('accounting_v2.enabled'))
                    @canany([
                        'v2 dashboard',
                        'v2 purchase book',
                        'v2 sale bill book',
                        'v2 receipt vouchers',
                        'v2 payment vouchers',
                        'v2 journal vouchers',
                        'v2 accounts manager',
                        'v2 account details manager',
                        'v2 stock manager',
                        'v2 stock ledger',
                        'v2 trial balance',
                        'v2 add remove users'
                    ])
                        <li class="submenu-open">
                            <h6 class="submenu-hdr">Accounting V2</h6>
                            <ul>
                                @can('v2 dashboard')<li><a href="{{ route('v2.dashboard') }}" class="{{ request()->routeIs('v2.dashboard') ? 'active' : '' }}"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Program Manager</span></a></li>@endcan
                                @can('v2 purchase book')<li><a href="{{ route('v2.purchase.index') }}" class="{{ request()->routeIs('v2.purchase.*') ? 'active' : '' }}"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Purchase Invoices</span></a></li>@endcan
                                @can('v2 sale bill book')<li><a href="{{ route('v2.sales.index') }}" class="{{ request()->routeIs('v2.sales.*') ? 'active' : '' }}"><i class="ti ti-receipt fs-16 me-2"></i><span>Sale Invoices</span></a></li>@endcan
                                @can('v2 receipt vouchers')<li><a href="{{ route('v2.receipts.index') }}" class="{{ request()->routeIs('v2.receipts.*') ? 'active' : '' }}"><i class="ti ti-cash fs-16 me-2"></i><span>Receipts</span></a></li>@endcan
                                @can('v2 payment vouchers')<li><a href="{{ route('v2.payments.index') }}" class="{{ request()->routeIs('v2.payments.*') ? 'active' : '' }}"><i class="ti ti-cash-banknote fs-16 me-2"></i><span>Payments</span></a></li>@endcan
                                @can('v2 journal vouchers')<li><a href="{{ route('v2.journal.index') }}" class="{{ request()->routeIs('v2.journal.*') ? 'active' : '' }}"><i class="ti ti-notebook fs-16 me-2"></i><span>Journal Vouchers</span></a></li>@endcan
                                @can('v2 accounts manager')<li><a href="{{ route('v2.accounts.index') }}" class="{{ request()->routeIs('v2.accounts.*') ? 'active' : '' }}"><i class="ti ti-users fs-16 me-2"></i><span>Accounts Manager</span></a></li>@endcan
                                @can('v2 account details manager')<li><a href="{{ route('v2.account-details.index') }}" class="{{ request()->routeIs('v2.account-details.*') ? 'active' : '' }}"><i class="ti ti-address-book fs-16 me-2"></i><span>Account Details</span></a></li>@endcan
                                @can('v2 stock manager')<li><a href="{{ route('v2.items.index') }}" class="{{ request()->routeIs('v2.items.*') ? 'active' : '' }}"><i class="ti ti-package fs-16 me-2"></i><span>Stock Manager</span></a></li>@endcan
                                @can('v2 category manager')<li><a href="{{ route('v2.categories.index') }}" class="{{ request()->routeIs('v2.categories.*') ? 'active' : '' }}"><i class="ti ti-category fs-16 me-2"></i><span>Category Manager</span></a></li>@endcan
                                @can('v2 brand manager')<li><a href="{{ route('v2.brands.index') }}" class="{{ request()->routeIs('v2.brands.*') ? 'active' : '' }}"><i class="ti ti-tag fs-16 me-2"></i><span>Brand Manager</span></a></li>@endcan
                                @can('v2 stock ledger')<li><a href="{{ route('v2.stock-ledger.index') }}" class="{{ request()->routeIs('v2.stock-ledger.*') ? 'active' : '' }}"><i class="ti ti-stack fs-16 me-2"></i><span>Stock Ledger</span></a></li>@endcan
                                @canany(['v2 trial balance', 'v2 trial balance aging', 'v2 income statement', 'v2 balance sheet'])<li><a href="{{ route('v2.reports.index') }}" class="{{ request()->routeIs('v2.reports.*') ? 'active' : '' }}"><i class="ti ti-printer fs-16 me-2"></i><span>Reports</span></a></li>@endcanany
                                @can('v2 add remove users')<li><a href="{{ route('v2.users.index') }}" class="{{ request()->routeIs('v2.users.*') ? 'active' : '' }}"><i class="ti ti-user-cog fs-16 me-2"></i><span>User Rights</span></a></li>@endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                @if(config('accounting_v2.show_old_accounting_menu'))
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Accounting</h6>
                        <ul>
                            <li><a href="{{ route('accounting.sales') }}"><i class="ti ti-report-money fs-16 me-2"></i><span>Sales</span></a></li>
                            <li><a href="{{ route('accounting.purchase') }}"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Purchase</span></a></li>
                            <li><a href="{{ route('accounting.receivable') }}"><i class="ti ti-receipt-2 fs-16 me-2"></i><span>Receivable</span></a></li>
                            <li><a href="{{ route('accounting.payable') }}"><i class="ti ti-file-dollar fs-16 me-2"></i><span>Payable</span></a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
