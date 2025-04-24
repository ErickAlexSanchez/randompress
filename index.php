<?php
// ─── Paso 0: Carga de configuración (.env + $newsApiKey) ────────────────────
require __DIR__ . '/bootstrap.php';

// ─── Paso 1: Rate Limiting por IP ───────────────────────────────────────────
$maxReq     = 30;    // peticiones permitidas
$perSeconds = 60;    // intervalo en segundos

$rawIp    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$cleanIp  = preg_replace('/[^A-Za-z0-9]/','_',$rawIp);
$rlKey    = "rl_{$cleanIp}";
$rlCache  = sys_get_temp_dir()."/{$rlKey}.json";

if (file_exists($rlCache)) {
    list($count,$start) = json_decode(file_get_contents($rlCache), true);
} else {
    $count = 0; $start = time();
}
if (time() - $start >= $perSeconds) {
    $count = 0; $start = time();
}
if ($count >= $maxReq) {
    http_response_code(429);
    exit('429 Too Many Requests – Intenta de nuevo en un momento.');
}
$count++;
file_put_contents($rlCache, json_encode([$count,$start]));

// ─── Paso 2: Parámetros de paginación y cache ───────────────────────────────
$pageSize = 10;
$requested = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Limitamos page entre 1 y 4
$page = max(1, min($requested, 4));
$cacheTtl  = 300; // 5 minutos
$cacheDir  = __DIR__ . '/cache';
@mkdir($cacheDir, 0755, true);
$cacheFile = "{$cacheDir}/news_{$page}.json";

// ─── Helper para peticiones con cache y validación ─────────────────────────
function fetchJson(string $url, string $cacheFile, int $ttl): array {
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $ttl) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'RandomPress/1.0',
        ]);
        $resp = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
    } else {
        $resp = @file_get_contents($url);
        $info = ['http_code' => $resp ? 200 : 0];
    }
    if ($info['http_code'] !== 200 || !$resp) {
        return ['status'=>'error','code'=>"HTTP_{$info['http_code']}",'message'=>'Error al conectar con NewsAPI'];
    }
    $data = json_decode($resp, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'ok') {
        return $data + ['status'=>'error','message'=>'Respuesta inválida de NewsAPI'];
    }
    file_put_contents($cacheFile, json_encode($data));
    return $data;
}

// ─── Paso 3: Usar ‘everything’ sin filtrar por idioma para garantizar >40 items ─
$topic    = 'technology';
$apiKey   = $newsApiKey;
$everythingUrl = "https://newsapi.org/v2/everything?"
               . "q=" . urlencode($topic)
               . "&sortBy=publishedAt"
               . "&pageSize={$pageSize}&page={$page}"
               . "&apiKey={$apiKey}";
$data     = fetchJson($everythingUrl, $cacheFile, $cacheTtl);
$articles = $data['articles']     ?? [];
$total    = $data['totalResults'] ?? 0;

// ─── Forzamos EXACTAMENTE 4 páginas de paginación ──────────────────────────
$totalPages = 4;

// ─── Paso 4: Random User para autores ──────────────────────────────────────
$authFile = "{$cacheDir}/authors_{$page}.json";
$authData = fetchJson("https://randomuser.me/api/?results={$pageSize}&nat=us", $authFile, $cacheTtl);
$authors  = $authData['results'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>RandomPress: Proyecto de Erick Sánchez</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet">
  <style>
    body.light-mode { background: #fff; color: #000; }
    body.dark-mode  { background: #121212; color: #e1e1e1; }
    .dark-mode .card { background: #1e1e1e; color: #e1e1e1; }
    .theme-toggle { position: fixed; top: 1rem; right: 1rem; z-index: 1000; }
  </style>
</head>
<body>
  <button id="themeToggle" class="btn btn-sm btn-secondary theme-toggle">🌓</button>
  <div class="container py-4">
    <h1 class="text-center mb-4">RandomPress: Proyecto de Erick Sánchez</h1>

    <?php if (($data['status'] ?? '') === 'error'): ?>
      <div class="alert alert-danger text-center">
        <strong>Error:</strong> <?= htmlspecialchars($data['message'] ?? 'Desconocido') ?>
      </div>
    <?php endif; ?>

    <div class="row gy-4">
      <?php if (empty($articles)): ?>
        <div class="col-12 text-center text-muted">
          No se encontraron noticias.
        </div>
      <?php else: foreach ($articles as $i => $art):
            $auth = $authors[$i] ?? null; ?>
        <div class="col-md-6">
          <div class="card h-100 shadow-sm">
            <?php if (!empty($art['urlToImage'])): ?>
              <img src="<?= htmlspecialchars($art['urlToImage']) ?>"
                   class="card-img-top" loading="lazy">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($art['title']) ?></h5>
              <p class="card-text text-truncate"><?= htmlspecialchars($art['description'] ?? '') ?></p>
              <div class="mt-auto">
                <?php if ($auth): ?>
                  <small class="text-muted">
                    Por <?= htmlspecialchars("{$auth['name']['first']} {$auth['name']['last']}") ?>
                  </small><br>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($art['url']) ?>" target="_blank"
                   class="btn btn-sm btn-primary mt-2">Leer más</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Paginación limitada a 4 páginas -->
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
        </li>
      </ul>
    </nav>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const btn = document.getElementById('themeToggle');
    const saved = localStorage.getItem('theme') || 'light';
    document.body.classList.add(saved + '-mode');
    btn.textContent = saved === 'light' ? '🌙' : '☀️';
    btn.onclick = () => {
      const next = document.body.classList.contains('light-mode') ? 'dark' : 'light';
      document.body.classList.toggle('light-mode');
      document.body.classList.toggle('dark-mode');
      btn.textContent = next === 'light' ? '🌙' : '☀️';
      localStorage.setItem('theme', next);
    };
  </script>
</body>
</html>
