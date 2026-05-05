<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SPMB') ?> — SMK Al-Munawwir IIBS</title>

    <!-- Google Fonts: Plus Jakarta Sans + Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <!-- ══════════════════════════════════════════════════════════════════
         Font Awesome 6 — dibutuhkan untuk ikon centang/silang di tombol
         approve/tolak pada halaman verifikasi dokumen admin
    ══════════════════════════════════════════════════════════════════ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom assets -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script defer src="<?= base_url('assets/js/app.js') ?>"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'hsl(220,54%,20%)',
                            light: 'hsl(220,54%,30%)',
                            dark: 'hsl(220,54%,14%)'
                        },
                        secondary: {
                            DEFAULT: 'hsl(43,70%,47%)',
                            light: 'hsl(43,80%,58%)'
                        },
                        accent: {
                            DEFAULT: 'hsl(160,60%,40%)'
                        },
                        info: {
                            DEFAULT: 'hsl(199,89%,48%)'
                        },
                        success: {
                            DEFAULT: 'hsl(142,71%,45%)'
                        },
                        warning: {
                            DEFAULT: 'hsl(38,92%,50%)'
                        },
                        muted: {
                            DEFAULT: 'hsl(220,20%,92%)'
                        },
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                }
            }
        }
    </script>

    <style>
        /* ── Reset & Base ─────────────────────────────────────── */
        [x-cloak] {
            display: none !important;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: hsl(220, 54%, 15%);
            background: hsl(220, 20%, 96%);
            -webkit-font-smoothing: antialiased;
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        /* ── Card ─────────────────────────────────────────────── */
        .card-elevated {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid hsl(220, 20%, 88%);
            box-shadow: 0 4px 6px -1px hsl(220 54% 20%/0.07), 0 2px 4px -2px hsl(220 54% 20%/0.05);
            transition: box-shadow .2s ease, transform .2s ease;
        }

        .card-elevated:hover {
            box-shadow: 0 10px 15px -3px hsl(220 54% 20%/0.1), 0 4px 6px -4px hsl(220 54% 20%/0.08);
        }

        /* ── Progress ─────────────────────────────────────────── */
        .progress-track {
            background: hsl(220, 20%, 92%);
            height: .5rem;
            border-radius: 9999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 9999px;
            background: hsl(220, 54%, 20%);
            transition: width .6s cubic-bezier(.4, 0, .2, 1);
        }

        /* ── Progress Steps ───────────────────────────────────── */
        .step-completed {
            background: hsl(142, 71%, 45%);
            color: white;
        }

        .step-active {
            background: hsl(220, 54%, 20%);
            color: hsl(45, 70%, 95%);
        }

        .step-pending {
            background: hsl(220, 20%, 92%);
            color: hsl(220, 15%, 50%);
        }

        /* ── Status Badges ────────────────────────────────────── */
        .status-draft {
            background: hsl(220, 20%, 92%);
            color: hsl(220, 15%, 40%);
            border: 1px solid hsl(220, 20%, 82%);
        }

        .status-pending {
            background: hsl(38, 92%, 50%, .12);
            color: hsl(38, 60%, 35%);
            border: 1px solid hsl(38, 92%, 50%, .30);
        }

        .status-verified {
            background: hsl(142, 71%, 45%, .12);
            color: hsl(142, 60%, 28%);
            border: 1px solid hsl(142, 71%, 45%, .30);
        }

        .status-rejected {
            background: hsl(0, 72%, 51%, .10);
            color: hsl(0, 55%, 40%);
            border: 1px solid hsl(0, 72%, 51%, .25);
        }

        /* ── Form Input ───────────────────────────────────────── */
        .form-input {
            width: 100%;
            padding: .625rem .75rem .625rem 2.75rem;
            border: 1px solid hsl(220, 20%, 88%);
            border-radius: .5rem;
            font-size: .875rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: white;
            color: hsl(220, 54%, 15%);
            transition: border-color .15s, box-shadow .15s;
        }

        .form-input:focus {
            outline: none;
            border-color: hsl(220, 54%, 30%);
            box-shadow: 0 0 0 3px hsl(220 54% 30%/.12);
        }

        .form-input::placeholder {
            color: hsl(220, 15%, 65%);
        }

        .form-input.has-error {
            border-color: hsl(0, 72%, 51%);
            box-shadow: 0 0 0 2px hsl(0 72% 51%/.15);
        }

        .form-input.has-success {
            border-color: hsl(142, 71%, 45%);
            box-shadow: 0 0 0 2px hsl(142 71% 45%/.15);
        }

        .form-input.has-right {
            padding-right: 2.75rem;
        }

        /* ── Animations ───────────────────────────────────────── */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        .animate-scale-in {
            animation: scaleIn .2s ease-out;
        }

        /* ── Scrollbar ────────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: hsl(220, 20%, 96%);
        }

        ::-webkit-scrollbar-thumb {
            background: hsl(220, 20%, 78%);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: hsl(220, 30%, 60%);
        }

        /* ── Tombol Aksi Dokumen (approve / reject) ───────────── */
        .btn-approve {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.65rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            background: hsl(142, 71%, 45%, .1);
            color: hsl(142, 60%, 28%);
            border: 1px solid hsl(142, 71%, 45%, .3);
            transition: background .15s, border-color .15s, transform .1s;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-approve:hover {
            background: hsl(142, 71%, 45%, .2);
            border-color: hsl(142, 71%, 45%, .5);
            transform: translateY(-1px);
        }

        .btn-approve:active {
            transform: translateY(0);
        }

        .btn-reject {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.65rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            background: hsl(0, 72%, 51%, .08);
            color: hsl(0, 55%, 40%);
            border: 1px solid hsl(0, 72%, 51%, .25);
            transition: background .15s, border-color .15s, transform .1s;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-reject:hover {
            background: hsl(0, 72%, 51%, .18);
            border-color: hsl(0, 72%, 51%, .45);
            transform: translateY(-1px);
        }

        .btn-reject:active {
            transform: translateY(0);
        }
    </style>
</head>

<body class="h-full" x-data="mainLayout()" x-cloak>

    <!-- MOBILE OVERLAY -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 z-30 lg:hidden"
        style="background:rgba(0,0,0,.45);"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- SIDEBAR -->
    <?= view('App\Views\Layouts\Partials\sidebar') ?>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="lg:pl-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <?= view('App\Views\Layouts\Partials\topbar') ?>

        <!-- FLASH MESSAGES -->
        <?php
        $flash_success = session()->getFlashdata('success');
        $flash_error   = session()->getFlashdata('error');
        $flash_errors  = session()->getFlashdata('errors');
        if ($flash_success || $flash_error || $flash_errors):
        ?>
            <div class="px-4 sm:px-6 lg:px-8 pt-4 space-y-2">
                <?php if ($flash_success): ?>
                    <div class="flex items-center gap-3 p-4 rounded-xl text-sm animate-scale-in"
                        style="background:hsl(142,71%,45%,.08);border:1px solid hsl(142,71%,45%,.25);color:hsl(142,60%,28%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <?= esc($flash_success) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flash_error): ?>
                    <div class="flex items-center gap-3 p-4 rounded-xl text-sm animate-scale-in"
                        style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.25);color:hsl(0,55%,40%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                        </svg>
                        <?= esc($flash_error) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flash_errors): ?>
                    <div class="p-4 rounded-xl text-sm animate-scale-in"
                        style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.25);color:hsl(0,55%,40%);">
                        <ul class="space-y-1">
                            <?php foreach ($flash_errors as $fe): ?>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2" />
                                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    </svg>
                                    <?= esc($fe) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- PAGE CONTENT -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
            <?= $content ?? '' ?>
        </main>

        <!-- FOOTER -->
        <?= view('App\Views\Layouts\Partials\footer') ?>

    </div>

    <script>
        function mainLayout() {
            return {
                sidebarOpen: false,
                init() {
                    this.$watch('sidebarOpen', val => {
                        document.body.style.overflow = val ? 'hidden' : '';
                    });
                },
                notifCount: 0,
                init() {
                    this.fetchNotifCount();
                    setInterval(() => this.fetchNotifCount(), 60000);
                },
                fetchNotifCount() {
                    fetch('<?= base_url('api/notifikasi/count') ?>', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) this.notifCount = data.count;
                        })
                        .catch(() => {});
                },
                async postJson(url, data = {}) {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(data),
                    });
                    return res.json();
                },
            };
        }
    </script>

    <?= $scripts ?? '' ?>
</body>

</html>