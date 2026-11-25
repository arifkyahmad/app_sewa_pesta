<nav>
    <span class="brand">Sewa Pesta</span>
    <ul>
        @if (auth()->user()->role === 'admin')
            <li>
                <a href="{{ route('categories.index') }}"
                class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Kategori</a>
            </li>
        @endif
        <li><a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">Barang</a></li>
        <li><a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">Pelanggan</a></li>
        <li><a href="{{ route('rentals.index') }}" class="{{ request()->routeIs('rentals.*') ? 'active' : '' }}">Sewa</a></li>
        <li><a href="{{ route('laporan.kerusakan') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">Laporan Kerusakan</a></li>
    </ul>
    <div class="nav-user">
        <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</nav>