<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="/" class="app-brand-link">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Ayasya Tech Indonesia" width="50" height="50">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">AyasyaTech</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Login -->
        {{-- <li class="menu-item">
            <a href="{{ route('login') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-in"></i>
                <div>Login</div>
            </a>
        </li> --}}

        <!-- Price List -->
        <li class="menu-item {{ request()->is('price-list/*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icon bx bx-list-ul"></i>
                <div>Daftar Harga</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('price-list/prabayar*') ? 'active' : '' }}">
                    <a href="{{ route('price.list.prabayar') }}" class="menu-link">
                        <div>Prabayar</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Histories Transaksi --}}
        <li class="menu-item {{ request()->is('histories/transaction/*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icon bx bx-history"></i>
                <div>Riwayat Transaksi</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('histories/transaction/prabayar*') ? 'active' : '' }}">
                    <a href="{{ route('history.transaction.prabayar') }}" class="menu-link">
                        <div>Prabayar</div>
                    </a>
                </li>
                {{-- <li class="menu-item {{ request()->is('histories/transaction/*') ? 'active' : '' }}">
                    <a href="{{ route('history.transaction.prabayar') }}" class="menu-link">
                        <div>Pascabayar</div>
                    </a>
                </li> --}}
            </ul>
        </li>

        {{-- Histories Deposit --}}
        {{-- <li class="menu-item {{ request()->is('histories/deposit/*') ? 'active' : '' }}">
            <a href="index.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wallet"></i>
                <div>Riwayat Deposit</div>
            </a>
        </li> --}}

    </ul>
</aside>
