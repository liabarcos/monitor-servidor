<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 24px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            width: 100%;
            max-width: 380px;
            margin-top: 48px;
        }
        h1 { margin-top: 0; font-size: 22px; }
        label { display: block; font-size: 14px; color: #444; margin-top: 14px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding: 9px 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
        }
        button {
            width: 100%;
            margin-top: 18px;
            padding: 10px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 9px 10px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .hint { color: #777; font-size: 13px; margin-top: 16px; }
        a { color: #2563eb; }
    </style>
</head>
<body>
<div class="card">
    <h1>Iniciar sesión</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first('email') }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required>

        <label style="margin-top:10px">
            <input type="checkbox" name="remember" value="1"> Recordarme
        </label>

        <button type="submit">Entrar</button>
    </form>

    <p class="hint">
        <a href="{{ route('servidor') }}">Ver el monitor del servidor</a>
    </p>
</div>
</body>
</html>
