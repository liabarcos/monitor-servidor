<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de autenticaciones</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 24px;
        }
        h1 { margin-top: 0; }
        h2 { font-size: 17px; margin-bottom: 8px; }
        .top { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .label { color: #666; font-size: 14px; }
        .value { font-size: 28px; font-weight: 600; margin-top: 4px; }
        .ok { color: #15803d; }
        .warn { color: #b45309; }
        .bad { color: #b91c1c; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            margin-top: 8px;
            font-size: 14px;
        }
        th, td { text-align: left; padding: 9px 12px; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; color: #555; font-weight: 600; }
        tr:last-child td { border-bottom: 0; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .b-success { background: #dcfce7; color: #15803d; }
        .b-failed { background: #fef3c7; color: #b45309; }
        .b-blocked { background: #fee2e2; color: #b91c1c; }
        .empty { color: #777; font-size: 14px; padding: 12px 0; }
        .actions a, .actions button {
            font-size: 14px;
            color: #2563eb;
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
        }
        .section { margin-top: 28px; }
    </style>
</head>
<body>
<div class="top">
    <h1>Panel de autenticaciones</h1>
    <div class="actions">
        <a href="{{ route('servidor') }}">Monitor del servidor</a>
        &nbsp;·&nbsp;
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Cerrar sesión ({{ $user->name }})</button>
        </form>
    </div>
</div>

<div class="grid">
    <div class="card">
        <div class="label">Logins correctos (24 h)</div>
        <div class="value ok">{{ $stats['success'] }}</div>
    </div>
    <div class="card">
        <div class="label">Intentos fallidos (24 h)</div>
        <div class="value warn">{{ $stats['failed'] }}</div>
    </div>
    <div class="card">
        <div class="label">Intentos bloqueados (24 h)</div>
        <div class="value bad">{{ $stats['blocked'] }}</div>
    </div>
</div>

<div class="section">
    <h2>IP con tres o más fallos en los últimos cinco minutos</h2>
    @if ($suspicious->isEmpty())
        <div class="empty">Sin patrones sospechosos en los últimos cinco minutos.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>IP</th>
                    <th>Fallos (5 min)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suspicious as $row)
                    <tr>
                        <td>{{ $row->ip_address }}</td>
                        <td>{{ $row->total_fallos }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="section">
    <h2>Últimos registros (24 h)</h2>
    @if ($recent->isEmpty())
        <div class="empty">Todavía no hay registros.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>IP</th>
                    <th>Estado</th>
                    <th>Identificador</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recent as $row)
                    <tr>
                        <td>{{ $row->created_at }}</td>
                        <td>{{ $row->ip_address }}</td>
                        <td><span class="badge b-{{ $row->status }}">{{ $row->status }}</span></td>
                        <td>{{ $row->identifier_hash ? \Illuminate\Support\Str::limit($row->identifier_hash, 16, '…') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>
