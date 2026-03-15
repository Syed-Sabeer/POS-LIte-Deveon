<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo logo-normal">
            <img src="{{ asset('AdminAssets/img/logo.svg') }}" alt="Img">
        </a>
        <a href="{{ route('dashboard') }}" class="logo logo-white">
            <img src="{{ asset('AdminAssets/img/logo-white.svg') }}" alt="Img">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img src="{{ asset('AdminAssets/img/logo-small.png') }}" alt="Img">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
    </div>

    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Main</h6>
                    <ul>
                        <li>
                            <a href="{{ route('dashboard') }}"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span></a>
                        </li>
                    </ul>
                </li>

                <li class="submenu-open">
                    <h6 class="submenu-hdr">Inventory</h6>
                    <ul>
                        <li><a href="{{ route('admin.products.index') }}"><i class="ti ti-package fs-16 me-2"></i><span>Products</span></a></li>
                        <li><a href="{{ route('stock.index') }}"><i class="ti ti-stack-2 fs-16 me-2"></i><span>Stock Maintenance</span></a></li>
                    </ul>
                </li>

                <li class="submenu-open">
                    <h6 class="submenu-hdr">POS</h6>
                    <ul>
                        <li><a href="{{ route('pos.index') }}"><i class="ti ti-device-laptop fs-16 me-2"></i><span>POS Checkout</span></a></li>
                        <li><a href="{{ route('pos.orders') }}"><i class="ti ti-receipt fs-16 me-2"></i><span>POS Orders</span></a></li>
                        <li><a href="{{ route('customers.index') }}"><i class="ti ti-users fs-16 me-2"></i><span>Customers</span></a></li>
                    </ul>
                </li>

                <li class="submenu-open">
                    <h6 class="submenu-hdr">Reports</h6>
                    <ul>
                        <li><a href="{{ route('reports.sales') }}"><i class="ti ti-chart-line fs-16 me-2"></i><span>Sales Reports</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
