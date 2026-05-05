<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= esc($code ?? 500) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-2xl w-full text-center">
        <div class="text-8xl font-black text-red-100 mb-4"><?= esc($code ?? 500) ?></div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= esc($title ?? 'Terjadi Kesalahan') ?></h1>
        <p class="text-gray-500 mb-6"><?= esc($message ?? 'Terjadi kesalahan pada server.') ?></p>
        <?php if (ENVIRONMENT === 'development' && isset($exception)): ?>
            <div class="text-left bg-red-50 border border-red-200 rounded-xl p-4 text-sm font-mono text-red-800 overflow-auto max-h-64">
                <p class="font-bold mb-1"><?= esc(get_class($exception)) ?></p>
                <p><?= esc($exception->getMessage()) ?></p>
                <p class="text-xs mt-2 text-red-400"><?= esc($exception->getFile()) ?>:<?= $exception->getLine() ?></p>
            </div>
        <?php endif; ?>
        <a href="<?= base_url('/') ?>" class="mt-6 inline-flex items-center px-6 py-3 bg-blue-700 text-white rounded-xl font-semibold hover:bg-blue-800 transition">
            ← Kembali ke Beranda
        </a>
    </div>
</body>

</html>