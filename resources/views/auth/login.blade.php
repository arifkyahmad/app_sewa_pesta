<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sewa Pesta</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0;
               min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; width: 100%; max-width: 380px; padding: 28px;
                border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1 { margin: 0 0 4px; font-size: 22px; }
        p.sub { margin: 0 0 20px; color: #6b7280; font-size: 14px; }
        label { display: block; margin-bottom: 4px; font-size: 14px; }
        input[type=email], input[type=password] { width: 100%; padding: 9px; margin-bottom: 14px;
                border: 1px solid #d1d5db; border-radius: 4px; }
        .check { display: flex; align-items: center; gap: 6px; margin-bottom: 16px; font-size: 14px; }
        button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: 0;
                 border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background: #1d4ed8; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 14px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Sewa Pesta</h1>
        <p class="sub">Masuk sebagai admin atau petugas</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <div class="check">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin:0">Ingat saya</label>
            </div>

            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>