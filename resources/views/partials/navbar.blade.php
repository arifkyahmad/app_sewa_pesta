<nav>
    <span class="brand">Sewa Pesta</span>
    <ul>
        <li><a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Kategori</a></li>
        <li><a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">Barang</a></li>
        <li><a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">Pelanggan</a></li>
        <li><a href="{{ route('rentals.index') }}" class="{{ request()->routeIs('rentals.*') ? 'active' : '' }}">Sewa</a></li>
        <li><a href="{{ route('laporan.kerusakan') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">Laporan Kerusakan</a></li>
    </ul>
</nav>