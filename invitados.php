<?php
$dataFile = __DIR__ . '/invitados.json';
$adminPin = 'boda2026';
$providedPin = $_POST['pin'] ?? $_GET['pin'] ?? '';

function getBaseUrl() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (strtolower($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on');

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    return $scheme . '://' . $host . ($path === '' ? '' : $path) . '/';
}

$baseUrl = getBaseUrl();

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, "[]");
}

function readGuests($dataFile) {
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function writeGuests($dataFile, $guests) {
    file_put_contents($dataFile, json_encode(array_values($guests), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function makeCode($guests) {
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $exists = false;
        foreach ($guests as $guest) {
            if (($guest['code'] ?? '') === $code) {
                $exists = true;
                break;
            }
        }
    } while ($exists);

    return $code;
}

$guests = readGuests($dataFile);

if ($providedPin !== $adminPin) {
?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso | Invitados</title>
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
        <h1>Panel de invitados</h1>
        <input type="password" name="pin" placeholder="Clave de acceso" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
<?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $guests = array_filter($guests, function ($guest) use ($code) {
            return ($guest['code'] ?? '') !== $code;
        });
        writeGuests($dataFile, $guests);
        header('Location: invitados.php?pin=' . urlencode($adminPin));
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $adults = max(0, (int)($_POST['adults'] ?? 0));
    $children = max(0, (int)($_POST['children'] ?? 0));

    if ($name !== '') {
        $guests[] = [
            'code' => makeCode($guests),
            'name' => $name,
            'adults' => $adults,
            'children' => $children,
            'created_at' => date('c'),
        ];
        writeGuests($dataFile, $guests);
    }

    header('Location: invitados.php?pin=' . urlencode($adminPin));
    exit;
}
?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitados | Hector & Cynthia</title>
    <style>
        :root {
            --ink: #2f2a27;
            --muted: #6f6460;
            --accent: #8e5e4f;
            --paper: #fffaf7;
            --line: rgba(142, 94, 79, 0.18);
        }

        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: var(--paper); font-family: Arial, sans-serif; }
        main { width: min(1120px, calc(100% - 32px)); margin: 0 auto; padding: 36px 0 48px; }
        h1 { margin: 0 0 8px; font-size: 34px; }
        p { color: var(--muted); }
        .panel { margin: 24px 0; padding: 22px; background: #fff; border: 1px solid var(--line); box-shadow: 0 12px 34px rgba(47, 42, 39, 0.06); }
        form.grid { display: grid; grid-template-columns: 1fr 120px 120px auto; gap: 12px; align-items: end; }
        label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 13px; font-weight: 700; }
        input { width: 100%; height: 44px; padding: 0 12px; border: 1px solid var(--line); color: var(--ink); background: #fff; }
        button, .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 16px; border: 0; color: #fff; background: var(--accent); cursor: pointer; text-decoration: none; font-weight: 700; }
        button.secondary { color: var(--accent); background: transparent; border: 1px solid var(--line); }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 13px 12px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: middle; }
        th { color: var(--muted); font-size: 13px; }
        code { font-weight: 700; }
        .link { width: min(420px, 100%); font-size: 13px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }

        @media (max-width: 760px) {
            form.grid { grid-template-columns: 1fr; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { padding: 12px; border-bottom: 1px solid var(--line); }
            td { padding: 8px 0; border: 0; }
        }
    </style>
</head>
<body>
<main>
    <h1>Links personalizados</h1>
    <p>Genera un codigo por invitado o familia. La invitacion se personaliza con el parametro <code>?inv=CODIGO</code>.</p>
    <p><a class="button" href="confirmaciones.php?pin=<?php echo urlencode($adminPin); ?>">Ver confirmaciones</a></p>

    <section class="panel">
        <form method="post" class="grid">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="pin" value="<?php echo htmlspecialchars($adminPin); ?>">
            <div>
                <label for="name">Nombre para la invitacion</label>
                <input id="name" name="name" required placeholder="Ej. Familia Perez Lopez">
            </div>
            <div>
                <label for="adults">Adultos</label>
                <input id="adults" name="adults" type="number" min="0" value="2">
            </div>
            <div>
                <label for="children">Ni&ntilde;os</label>
                <input id="children" name="children" type="number" min="0" value="0">
            </div>
            <button type="submit">Generar link</button>
        </form>
    </section>

    <section class="panel">
        <table>
            <thead>
            <tr>
                <th>Codigo</th>
                <th>Invitado</th>
                <th>Adultos</th>
                <th>Ni&ntilde;os</th>
                <th>Link</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (array_reverse($guests) as $guest): ?>
                <?php $link = $baseUrl . '?inv=' . urlencode($guest['code']); ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($guest['code']); ?></code></td>
                    <td><?php echo htmlspecialchars($guest['name']); ?></td>
                    <td><?php echo (int)$guest['adults']; ?></td>
                    <td><?php echo (int)$guest['children']; ?></td>
                    <td><input class="link" readonly value="<?php echo htmlspecialchars($link); ?>"></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="secondary" data-copy="<?php echo htmlspecialchars($link); ?>">Copiar</button>
                            <a class="button" href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener">Abrir</a>
                            <form method="post" onsubmit="return confirm('Eliminar este invitado?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="pin" value="<?php echo htmlspecialchars($adminPin); ?>">
                                <input type="hidden" name="code" value="<?php echo htmlspecialchars($guest['code']); ?>">
                                <button type="submit" class="secondary">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$guests): ?>
                <tr><td colspan="6">Aun no hay invitados personalizados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            navigator.clipboard.writeText(button.getAttribute('data-copy'));
            button.textContent = 'Copiado';
            window.setTimeout(function () { button.textContent = 'Copiar'; }, 1400);
        });
    });
</script>
</body>
</html>
