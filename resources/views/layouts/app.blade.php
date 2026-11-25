<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sewa Perlengkapan Pesta')</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 0; color: #1f2937; }

        /* Navbar */
        nav { background: #1e3a8a; padding: 14px 40px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        nav .brand { color: #fff; font-weight: bold; font-size: 18px; margin-right: 32px; }
        nav ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
        nav ul li a { color: #cbd5e1; text-decoration: none; padding: 6px 4px; }
        nav ul li a.active { color: #fff; font-weight: bold; border-bottom: 2px solid #fff; }
        nav svg { max-height: 20px; display: inline-block; }
        .nav-user { margin-left: auto; display: flex; align-items: center; gap: 10px; color: #fff; font-size: 14px; }
        .btn-logout { background: #dc2626; color: #fff; border: 0; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-logout:hover { background: #b91c1c; }

        /* Konten */
        main { max-width: 900px; margin: 0 auto; padding: 30px 40px; }
        footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb; margin-top: 40px; }

        /* Tabel */
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }

        /* Alert */
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }

        /* Form */
        form { max-width: 500px; }
        form.inline { display: inline; max-width: none; }
        label { display: block; margin-top: 14px; margin-bottom: 4px; font-weight: bold; }
        input[type="text"],
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }
        textarea { resize: vertical; }
        .error { color: #dc2626; font-size: 13px; margin-top: 4px; }

        /* Tombol */
        .btn { display: inline-block; margin-top: 18px; padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { margin-top: 0; padding: 4px 10px; font-size: 13px; }

        /* Badge status */
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 13px; }
        .badge-disewa       { background: #fef3c7; color: #92400e; }
        .badge-dikembalikan { background: #dcfce7; color: #166534; }
        .badge-terlambat    { background: #fee2e2; color: #991b1b; }

        /* Pagination */
        .pagination { margin-top: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .pagination a { color: #2563eb; text-decoration: none; }
        .pagination a:hover { text-decoration: underline; }
        .pagination .pagination-current { font-weight: bold; color: #1e3a8a; }
        .pagination .pagination-disabled { color: #9ca3af; }
    </style>
</head>
<body>
    @include('partials.navbar')

    <main>
        @include('partials.alert')

        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} Sistem Sewa Perlengkapan Pesta
    </footer>
</body>
</html>