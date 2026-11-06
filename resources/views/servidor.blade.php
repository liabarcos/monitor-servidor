<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor del servidor</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 24px;
        }
        h1 { margin-top: 0; }
        h2 { font-size: 17px; margin: 0; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .label { color: #666; font-size: 14px; }
        .value { font-size: 24px; font-weight: 600; margin-top: 4px; }

        /* --- Estado del servidor: barras de uso --- */
        .estado {
            background: #fff;
            border-radius: 8px;
            padding: 18px 20px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            margin-top: 16px;
        }
        .estado-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .estado-head .hint { color: #888; font-size: 13px; }
        .metric { margin-bottom: 16px; }
        .metric:last-child { margin-bottom: 0; }
        .metric-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .metric-name { color: #444; font-weight: 500; }
        .metric-val { color: #222; font-weight: 600; font-variant-numeric: tabular-nums; }
        .track {
            height: 14px;
            background: #eceff2;
            border-radius: 999px;
            overflow: hidden;
        }
        .fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            transition: width .6s ease, background-color .3s ease;
        }
        .fill.ok   { background: #2e9e5b; }
        .fill.warn { background: #e0a800; }
        .fill.bad  { background: #d64545; }
        .fill.na   { background: #b9c0c7; }
        .scale {
            display: flex;
            justify-content: space-between;
            color: #999;
            font-size: 11.5px;
            margin-top: 4px;
        }
        .legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: #666;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid #f0f1f3;
        }
        .legend span { display: inline-flex; align-items: center; gap: 6px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    </style>
</head>
<body>
<h1>Monitor del servidor</h1>
<p>Última lectura: <span id="at">{{ $data['at'] }}</span></p>
<div class="grid">
    <div class="card">
        <div class="label">Nombre del equipo</div>
        <div class="value">{{ $data['hostname'] }}</div>
    </div>
    <div class="card">
        <div class="label">Sistema operativo</div>
        <div class="value">{{ $data['os'] }}</div>
    </div>
    <div class="card">
        <div class="label">Versión de PHP</div>
        <div class="value">{{ $data['php'] }}</div>
    </div>
    <div class="card">
        <div class="label">Disco usado (%)</div>
        <div class="value" id="disk">{{ $data['disk']['used_pct'] }}</div>
    </div>
    <div class="card">
        <div class="label">Memoria usada (%)</div>
        <div class="value" id="memory">{{ $data['memory']['used_pct'] ?? 'No disponible' }}</div>
    </div>
    <div class="card">
        <div class="label">Uso de CPU (%)</div>
        <div class="value" id="cpu">{{ $data['cpu_pct'] ?? 'No disponible' }}</div>
    </div>
    <div class="card">
        <div class="label">Temperatura CPU (°C)</div>
        <div class="value" id="temp">{{ $data['cpu_temp_c'] ?? 'No disponible' }}</div>
    </div>
    <div class="card">
        <div class="label">Encendido hace (segundos)</div>
        <div class="value" id="uptime">{{ $data['uptime_s'] ?? 'No disponible' }}</div>
    </div>
</div>

<section class="estado">
    <div class="estado-head">
        <h2>Estado del servidor</h2>
        <span class="hint">Se actualiza cada 5 segundos</span>
    </div>

    <div class="metric">
        <div class="metric-head">
            <span class="metric-name">CPU</span>
            <span class="metric-val" id="val-cpu">{{ $data['cpu_pct'] ?? 'No disponible' }}</span>
        </div>
        <div class="track"><div class="fill" id="bar-cpu" style="width: {{ $data['cpu_pct'] ?? 0 }}%"></div></div>
        <div class="scale"><span>0 %</span><span>50 %</span><span>100 %</span></div>
    </div>

    <div class="metric">
        <div class="metric-head">
            <span class="metric-name">Memoria</span>
            <span class="metric-val" id="val-mem">{{ $data['memory']['used_pct'] ?? 'No disponible' }}</span>
        </div>
        <div class="track"><div class="fill" id="bar-mem" style="width: {{ $data['memory']['used_pct'] ?? 0 }}%"></div></div>
        <div class="scale"><span>0 %</span><span>50 %</span><span>100 %</span></div>
    </div>

    <div class="metric">
        <div class="metric-head">
            <span class="metric-name">Disco</span>
            <span class="metric-val" id="val-disk">{{ $data['disk']['used_pct'] }}%</span>
        </div>
        <div class="track"><div class="fill" id="bar-disk" style="width: {{ $data['disk']['used_pct'] }}%"></div></div>
        <div class="scale"><span>0 %</span><span>50 %</span><span>100 %</span></div>
    </div>

    <div class="metric">
        <div class="metric-head">
            <span class="metric-name">Temperatura</span>
            <span class="metric-val" id="val-temp">{{ $data['cpu_temp_c'] !== null ? $data['cpu_temp_c'].' °C' : 'No disponible' }}</span>
        </div>
        <div class="track"><div class="fill" id="bar-temp" style="width: {{ $data['cpu_temp_c'] ?? 0 }}%"></div></div>
        <div class="scale"><span>0 °C</span><span>50 °C</span><span>100 °C</span></div>
    </div>

    <div class="legend">
        <span><i class="dot" style="background:#2e9e5b"></i> Uso normal (hasta 60 %)</span>
        <span><i class="dot" style="background:#e0a800"></i> Uso elevado (60 – 85 %)</span>
        <span><i class="dot" style="background:#d64545"></i> Uso crítico (más de 85 %)</span>
        <span><i class="dot" style="background:#b9c0c7"></i> Sin dato</span>
    </div>
</section>

<script>
    // Pinta una barra: ancho, color según el tramo y texto del valor.
    function barra(fillId, valId, pct, texto) {
        const fill = document.getElementById(fillId);
        const val = document.getElementById(valId);

        if (pct === null || pct === undefined || isNaN(pct)) {
            fill.style.width = '0%';
            fill.className = 'fill na';
            val.textContent = 'No disponible';
            return;
        }

        const p = Math.max(0, Math.min(100, Number(pct)));
        fill.style.width = p + '%';
        fill.className = 'fill ' + (p > 85 ? 'bad' : p > 60 ? 'warn' : 'ok');
        val.textContent = texto;
    }

    function pintar(d) {
        barra('bar-cpu', 'val-cpu', d.cpu_pct, d.cpu_pct != null ? d.cpu_pct + ' %' : null);
        barra('bar-mem', 'val-mem', d.memory?.used_pct, d.memory?.used_pct != null ? d.memory.used_pct + ' %' : null);
        barra('bar-disk', 'val-disk', d.disk?.used_pct, d.disk?.used_pct != null ? d.disk.used_pct + ' %' : null);
        barra('bar-temp', 'val-temp', d.cpu_temp_c, d.cpu_temp_c != null ? d.cpu_temp_c + ' °C' : null);
    }

    async function actualizar() {
        const res = await fetch('/api/metrics');
        const d = await res.json();
        document.getElementById('at').textContent = d.at;
        document.getElementById('disk').textContent = d.disk.used_pct;
        document.getElementById('memory').textContent = d.memory?.used_pct ?? 'No disponible';
        document.getElementById('cpu').textContent = d.cpu_pct ?? 'No disponible';
        document.getElementById('temp').textContent = d.cpu_temp_c ?? 'No disponible';
        document.getElementById('uptime').textContent = d.uptime_s ?? 'No disponible';
        pintar(d);
    }

    pintar(@json($data));
    setInterval(actualizar, 5000); // cada 5 segundos
</script>
</body>
</html>
