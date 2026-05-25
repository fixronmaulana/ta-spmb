<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Lupa Password') ?> — SMK Al-Munawwir IIBS</title>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'hsl(220, 54%, 20%)',
                            light: 'hsl(220, 54%, 30%)'
                        },
                        secondary: {
                            DEFAULT: 'hsl(43, 70%, 47%)'
                        },
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    keyframes: {
                        scaleIn: {
                            from: { opacity: '0', transform: 'scale(0.95)' },
                            to:   { opacity: '1', transform: 'scale(1)' }
                        },
                    },
                    animation: {
                        'scale-in': 'scaleIn 0.2s ease-out',
                    },
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: hsl(220, 54%, 15%);
            background: hsl(45, 30%, 98%);
        }

        .font-serif { font-family: 'Playfair Display', serif; }

        .form-input {
            width: 100%;
            padding: 0.625rem 0.75rem 0.625rem 2.75rem;
            border: 1px solid hsl(220, 20%, 88%);
            border-radius: 0.5rem;
            font-size: max(1rem, 0.875rem);
            background: white;
            color: hsl(220, 54%, 15%);
            transition: border-color 0.15s, box-shadow 0.15s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: hsl(220, 54%, 30%);
            box-shadow: 0 0 0 3px hsl(220, 54%, 30%, 0.12);
        }

        .form-input::placeholder { color: hsl(220, 15%, 65%); }

        .form-input.has-error {
            border-color: hsl(0, 72%, 51%);
            box-shadow: 0 0 0 2px hsl(0, 72%, 51%, 0.15);
        }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="min-h-screen flex">

    <div class="min-h-screen flex w-full">

        <!-- ============ LEFT: FORM PANEL ============ -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-20 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">

                <!-- Breadcrumb -->
                <nav class="mb-8">
                    <ol class="flex items-center gap-2 text-sm" style="color: hsl(220,15%,45%);">
                        <li>
                            <a href="<?= base_url('/') ?>" class="hover:text-primary transition-colors"
                                style="color: hsl(220,15%,45%);"
                                onmouseover="this.style.color='hsl(220,54%,20%)'"
                                onmouseout="this.style.color='hsl(220,15%,45%)'">
                                Home
                            </a>
                        </li>
                        <li>/</li>
                        <li>
                            <a href="<?= base_url('auth/login') ?>"
                                style="color: hsl(220,15%,45%);"
                                onmouseover="this.style.color='hsl(220,54%,20%)'"
                                onmouseout="this.style.color='hsl(220,15%,45%)'">
                                Login
                            </a>
                        </li>
                        <li>/</li>
                        <li class="font-medium" style="color: hsl(220,54%,15%);">Lupa Password</li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="flex items-center gap-3 mb-2">
                    <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS"
                        class="w-12 h-12 rounded-full flex-shrink-0 object-cover"
                        style="background: hsl(220,54%,20%);">
                    <div>
                        <h1 class="text-2xl font-bold font-serif">LUPA PASSWORD</h1>
                        <p class="text-sm" style="color: hsl(220,15%,45%);">Kami akan kirimkan link reset ke email Anda</p>
                    </div>
                </div>

                <!-- Info box -->
                <div class="flex items-start gap-3 p-4 rounded-lg mb-6 text-sm"
                    style="background: hsl(220,54%,20%,0.06); border: 1px solid hsl(220,54%,20%,0.18); color: hsl(220,54%,25%);">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4M12 8h.01"/>
                    </svg>
                    <span>Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mereset password Anda.</span>
                </div>

                <!-- Flash: error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="flex items-center gap-3 p-4 rounded-lg mb-6 animate-scale-in text-sm"
                        style="background: hsl(0,72%,51%,0.08); border: 1px solid hsl(0,72%,51%,0.25); color: hsl(0,72%,40%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                        </svg>
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <!-- Flash: success -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="flex items-center gap-3 p-4 rounded-lg mb-6 animate-scale-in text-sm"
                        style="background: hsl(142,71%,45%,0.08); border: 1px solid hsl(142,71%,45%,0.25); color: hsl(142,71%,30%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>

                <!-- Validation Errors -->
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="p-4 rounded-lg mb-6 text-sm animate-scale-in"
                        style="background: hsl(0,72%,51%,0.08); border: 1px solid hsl(0,72%,51%,0.25); color: hsl(0,72%,40%);">
                        <ul class="space-y-1">
                            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                    </svg>
                                    <?= esc($err) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form action="<?= base_url('auth/forgot-password') ?>" method="POST" class="space-y-5"
                    onsubmit="document.getElementById('submitBtn').disabled=true; document.getElementById('submitBtn').style.opacity='0.7'; document.getElementById('submitBtn').style.cursor='not-allowed'; document.getElementById('submitSpinner').style.display='inline-block'; document.getElementById('submitBtnText').textContent='Mengirim...';">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="<?= esc(old('email')) ?>"
                                placeholder="contoh@email.com"
                                class="form-input <?= session()->getFlashdata('errors') ? 'has-error' : '' ?>"
                                autocomplete="email"
                                required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        id="submitBtn"
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 px-6 font-semibold text-white rounded-xl transition-all text-sm"
                        style="background: hsl(220,54%,20%);"
                        onmouseover="if(!this.disabled) this.style.background='hsl(220,54%,30%)'"
                        onmouseout="if(!this.disabled) this.style.background='hsl(220,54%,20%)'">
                        <svg id="submitSpinner" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="submitBtnText">Kirim Link Reset Password</span>
                    </button>
                </form>

                <!-- Back to Login -->
                <p class="mt-6 text-center text-sm" style="color: hsl(220,15%,45%);">
                    Ingat password Anda?
                    <a href="<?= base_url('auth/login') ?>"
                        class="font-semibold transition-colors"
                        style="color: hsl(220,54%,20%);"
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'">
                        Kembali ke Login
                    </a>
                </p>

            </div>
        </div>

        <!-- ============ RIGHT: DECORATIVE PANEL ============ -->
        <div class="hidden lg:flex lg:flex-1 relative overflow-hidden" style="background: hsl(220,54%,20%);">
            <div class="absolute inset-0 opacity-10 hero-pattern"></div>

            <div class="flex items-center justify-center w-full p-12 relative">
                <div class="text-center">
                    <div class="w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6"
                        style="background: hsl(43,70%,47%,0.15); border: 3px solid hsl(43,70%,47%,0.3);">
                        <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS"
                            class="w-24 h-24 rounded-full object-cover">
                    </div>
                    <h2 class="text-3xl font-bold font-serif mb-4" style="color: hsl(45,70%,95%);">
                        SMK Al-Munawwir IIBS
                    </h2>
                    <p style="color: rgba(255,255,255,0.75);" class="max-w-sm">
                        International Islamic Boarding School
                        <br>Banyuwangi
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>

</html>