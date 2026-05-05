<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="text-8xl font-black text-gray-200 mb-4">404</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-500 mb-6"><?= esc($message ?? 'Halaman yang Anda cari tidak ada.') ?></p>
        <a href="<?= base_url('/') ?>" class="inline-flex items-center px-6 py-3 bg-blue-700 text-white rounded-xl font-semibold hover:bg-blue-800 transition">
            ← Kembali ke Beranda
        </a>
    </div>
</body>
</html>