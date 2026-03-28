<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('home') }}" class="logo logo-normal">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a href="{{ route('home') }}" class="logo logo-white">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a href="{{ route('home') }}" class="logo-small">
            <img src="{{ asset('AdminAssets/img/ddb.png') }}" alt="Img">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
    </div>

    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                @can('view dashboard')
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Main</h6>
                        <ul>
                            <li><a href="{{ route('home') }}"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span></a></li>
                        </ul>
                    </li>
                @endcan

                @canany(['manage products', 'manage stock', 'manage purchases', 'manage suppliers'])
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Inventory & Purchase</h6>
                        <ul>
                            @can('manage products')<li><a href="{{ route('admin.products.index') }}"><i class="ti ti-package fs-16 me-2"></i><span>Products</span></a></li>@endcan
                            @can('manage stock')<li><a href="{{ route('stock.index') }}"><i class="ti ti-stack-2 fs-16 me-2"></i><span>Stock Maintenance</span></a></li>@endcan
                            {{-- @can('manage suppliers')<li><a href="{{ route('suppliers.index') }}"><i class="ti ti-truck fs-16 me-2"></i><span>Suppliers</span></a></li>@endcan
                            @can('manage purchases')<li><a href="{{ route('purchases.index') }}"><i class="ti ti-file-invoice fs-16 me-2"></i><span>Purchases</span></a></li>@endcan --}}
                        </ul>
                    </li>
                @endcanany

                @canany(['pos checkout', 'pos orders', 'manage customers', 'manage customer payments', 'manage supplier payments'])
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">POS & Payments</h6>
                        <ul>
                            @can('pos checkout')<li><a href="{{ route('pos.index') }}"><i class="ti ti-device-laptop fs-16 me-2"></i><span>POS Checkout</span></a></li>@endcan
                            @can('pos orders')<li><a href="{{ route('pos.orders') }}"><i class="ti ti-receipt fs-16 me-2"></i><span>POS Orders</span></a></li>@endcan
                            @can('manage customers')<li><a href="{{ route('customers.index') }}"><i class="ti ti-users fs-16 me-2"></i><span>Customers</span></a></li>@endcan
                            @can('manage customer payments')<li><a href="{{ route('customer-payments.index') }}"><i class="ti ti-cash fs-16 me-2"></i><span>Customer Payments</span></a></li>@endcan
                            {{-- @can('manage supplier payments')<li><a href="{{ route('supplier-payments.index') }}"><i class="ti ti-cash-banknote fs-16 me-2"></i><span>Supplier Payments</span></a></li>@endcan --}}
                        </ul>
                    </li>
                @endcanany

                @canany(['view receivables report', 'view payables report', 'view sales reports'])
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Reports</h6>
                        <ul>
                            @can('view sales reports')<li><a href="{{ route('reports.sales') }}"><i class="ti ti-chart-line fs-16 me-2"></i><span>Sales Reports</span></a></li>@endcan
                            {{-- @can('view receivables report')<li><a href="{{ route('reports.receivables') }}"><i class="ti ti-report-money fs-16 me-2"></i><span>Receivables Report</span></a></li>@endcan
                            @can('view payables report')<li><a href="{{ route('reports.payables') }}"><i class="ti ti-report-search fs-16 me-2"></i><span>Payables Report</span></a></li>@endcan --}}
                        </ul>
                    </li>
                @endcanany

                @canany(['view customer ledger', 'view supplier ledger', 'view journal entries', 'manage chart of accounts', 'view balance sheet', 'manage receivables', 'manage payables'])
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Accounting</h6>
                        <ul>
                            {{-- @canany(['manage receivables', 'manage customer payments'])<li><a href="{{ route('customer-payments.index') }}"><i class="ti ti-receipt-2 fs-16 me-2"></i><span>Receivables</span></a></li>@endcanany
                            @canany(['manage payables', 'manage supplier payments'])<li><a href="{{ route('supplier-payments.index') }}"><i class="ti ti-file-dollar fs-16 me-2"></i><span>Payables</span></a></li>@endcanany --}}
                            @can('view customer ledger')<li><a href="{{ route('ledgers.customers') }}"><i class="ti ti-book-2 fs-16 me-2"></i><span>Customer Ledger</span></a></li>@endcan
                            {{-- @can('view supplier ledger')<li><a href="{{ route('ledgers.suppliers') }}"><i class="ti ti-book fs-16 me-2"></i><span>Supplier Ledger</span></a></li>@endcan
                            @can('view journal entries')<li><a href="{{ route('ledgers.accounts') }}"><i class="ti ti-list-details fs-16 me-2"></i><span>General Ledger</span></a></li>@endcan
                            @can('view journal entries')<li><a href="{{ route('ledgers.cash-book') }}"><i class="ti ti-wallet fs-16 me-2"></i><span>Cash Book</span></a></li>@endcan
                            @can('view journal entries')<li><a href="{{ route('ledgers.bank-book') }}"><i class="ti ti-building-bank fs-16 me-2"></i><span>Bank Book</span></a></li>@endcan
                            @can('manage chart of accounts')<li><a href="{{ route('accounts.index') }}"><i class="ti ti-chart-dots fs-16 me-2"></i><span>Chart of Accounts</span></a></li>@endcan
                            @can('view journal entries')<li><a href="{{ route('journals.index') }}"><i class="ti ti-notebook fs-16 me-2"></i><span>Journal Entries</span></a></li>@endcan
                            @can('view balance sheet')<li><a href="{{ route('reports.balance-sheet') }}"><i class="ti ti-scale fs-16 me-2"></i><span>Balance Sheet</span></a></li>@endcan
                            @can('manage chart of accounts')<li><a href="{{ route('access.roles.index') }}"><i class="ti ti-shield fs-16 me-2"></i><span>Role Permissions</span></a></li>@endcan
                            @can('manage chart of accounts')<li><a href="{{ route('access.users.index') }}"><i class="ti ti-user-cog fs-16 me-2"></i><span>User Access</span></a></li>@endcan --}}
                        </ul>
                    </li>
                @endcanany
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
