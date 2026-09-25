<?php
$dataFile = __DIR__ . '/confirmaciones.json';
$adminPin = 'boda2026';
$providedPin = $_GET['pin'] ?? '';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, "[]");
}

if ($providedPin !== $adminPin) {
?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso | Confirmaciones</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #fffaf7; color: #2f2a27; font-family: Arial, sans-serif; }
        form { width: min(360px, calc(100% - 32px)); padding: 24px; background: #fff; border: 1px solid rgba(142, 94, 79, 0.18); box-shadow: 0 12px 34px rgba(47, 42, 39, 0.06); }
        h1 { margin: 0 0 16px; font-size: 26px; }
        input { width: 100%; height: 44px; padding: 0 12px; border: 1px solid rgba(142, 94, 79, 0.18); }
        button { width: 100%; height: 44px; margin-top: 12px; border: 0; color: #fff; background: #8e5e4f; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <form method="get">
        <h1>Confirmaciones</h1>
        <input type="password" name="pin" placeholder="Clave de acceso" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
<?php
    exit;
}

$confirmations = json_decode(file_get_contents($dataFile), true);

if (!is_array($confirmations)) {
    $confirmations = [];
}

$latestByKey = [];

foreach ($confirmations as $item) {
    $key = trim($item['code'] ?? '');
    if ($key === '') {
        $key = 'manual-' . md5(($item['name'] ?? '') . ($item['created_at'] ?? ''));
    }
    $latestByKey[$key] = $item;
}

$confirmedAdults = 0;
$confirmedChildren = 0;
$notAttending = 0;

foreach ($latestByKey as $item) {
    $attendance = strtolower($item['attendance'] ?? '');
    if (strpos($attendance, 'no') !== false) {
        $notAttending += 1;
        continue;
    }
    $confirmedAdults += (int)($item['adults'] ?? 0);
    $confirmedChildren += (int)($item['children'] ?? 0);
}

$rows = array_reverse(array_values($latestByKey));
?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmaciones | Hector & Cynthia</title>
    <style>
        :root { --ink: #2f2a27; --muted: #6f6460; --accent: #8e5e4f; --paper: #fffaf7; --line: rgba(142, 94, 79, 0.18); }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: var(--paper); font-family: Arial, sans-serif; }
        main { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 36px 0 48px; }
        header { display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap; }
        h1 { margin: 0 0 8px; font-size: 34px; }
        p { color: var(--muted); }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 14px; color: #fff; background: var(--accent); text-decoration: none; font-weight: 700; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 24px 0; }
        .stat, .panel { background: #fff; border: 1px solid var(--line); box-shadow: 0 12px 34px rgba(47, 42, 39, 0.06); }
        .stat { padding: 18px; }
        .stat span { display: block; color: var(--muted); font-size: 13px; font-weight: 700; }
        .stat strong { display: block; margin-top: 8px; font-size: 34px; }
        .panel { padding: 18px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 13px 12px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { color: var(--muted); font-size: 13px; }
        code { font-weight: 700; }
        @media (max-width: 800px) { .stats { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 520px) { .stats { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Confirmaciones</h1>
            <p>Resumen estimado con la ultima respuesta registrada por codigo.</p>
        </div>
        <a class="button" href="invitados.php?pin=<?php echo urlencode($adminPin); ?>">Volver a invitados</a>
    </header>

    <section class="stats">
        <div class="stat"><span>Adultos confirmados</span><strong><?php echo $confirmedAdults; ?></strong></div>
        <div class="stat"><span>Ni&ntilde;os confirmados</span><strong><?php echo $confirmedChildren; ?></strong></div>
        <div class="stat"><span>Total estimado</span><strong><?php echo $confirmedAdults + $confirmedChildren; ?></strong></div>
        <div class="stat"><span>No asistir&aacute;n</span><strong><?php echo $notAttending; ?></strong></div>
    </section>

    <section class="panel">
        <table>
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Codigo</th>
                <th>Invitacion</th>
                <th>Nombre</th>
                <th>Asistencia</th>
                <th>Adultos</th>
                <th>Ni&ntilde;os</th>
                <th>Mensaje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['created_at'] ?? ''); ?></td>
                    <td><code><?php echo htmlspecialchars($item['code'] ?? ''); ?></code></td>
                    <td><?php echo htmlspecialchars($item['invitation_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($item['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($item['attendance'] ?? ''); ?></td>
                    <td><?php echo (int)($item['adults'] ?? 0); ?></td>
                    <td><?php echo (int)($item['children'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($item['message'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="8">Aun no hay confirmaciones.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
