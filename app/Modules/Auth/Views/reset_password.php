<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Reset Password') ?> — SMK Al-Munawwir IIBS</title>
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

        .form-input.has-right { padding-right: 2.75rem; }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Password strength meter */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: width 0.3s, background-color 0.3s;
        }
    </style>
</head>

<body class="min-h-screen flex" x-data="resetPage()">

    <div class="min-h-screen flex w-full">

        <!-- ============ LEFT: FORM PANEL ============ -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-20 overflow-y-auto">
            <div class="max-w-md w-full mx-auto">

                <!-- Breadcrumb -->
                <nav class="mb-8">
                    <ol class="flex items-center gap-2 text-sm" style="color: hsl(220,15%,45%);">
                        <li>
                            <a href="<?= base_url('/') ?>"
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
                        <li class="font-medium" style="color: hsl(220,54%,15%);">Reset Password</li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="flex items-center gap-3 mb-8">
                    <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS"
                        class="w-12 h-12 rounded-full flex-shrink-0 object-cover"
                        style="background: hsl(220,54%,20%);">
                    <div>
                        <h1 class="text-2xl font-bold font-serif">RESET PASSWORD</h1>
                        <p class="text-sm" style="color: hsl(220,15%,45%);">Buat password baru yang kuat untuk akun Anda</p>
                    </div>
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
                <form action="<?= base_url('auth/reset-password') ?>" method="POST" class="space-y-5"
                    onsubmit="document.getElementById('resetBtn').disabled=true; document.getElementById('resetBtn').style.opacity='0.7'; document.getElementById('resetBtn').style.cursor='not-allowed'; document.getElementById('resetSpinner').style.display='inline-block'; document.getElementById('resetBtnText').textContent='Menyimpan...';">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                    <!-- New Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium">Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </span>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                id="password"
                                placeholder="Min. 8 karakter, huruf besar & angka"
                                class="form-input has-right"
                                x-model="password"
                                @input="checkStrength()"
                                required>
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                style="color: hsl(220,15%,55%);"
                                onmouseover="this.style.color='hsl(220,54%,15%)'"
                                onmouseout="this.style.color='hsl(220,15%,55%)'">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Strength meter -->
                        <div x-show="password.length > 0" x-cloak class="space-y-1">
                            <div class="flex gap-1">
                                <div class="flex-1 h-1 rounded-full" :style="strengthColor(1)"></div>
                                <div class="flex-1 h-1 rounded-full" :style="strengthColor(2)"></div>
                                <div class="flex-1 h-1 rounded-full" :style="strengthColor(3)"></div>
                                <div class="flex-1 h-1 rounded-full" :style="strengthColor(4)"></div>
                            </div>
                            <p class="text-xs" :style="'color:' + strengthTextColor()">
                                <span x-text="strengthLabel()"></span>
                            </p>
                        </div>

                        <!-- Requirements -->
                        <ul class="text-xs space-y-1 mt-1" style="color: hsl(220,15%,50%);">
                            <li class="flex items-center gap-1.5" :style="password.length >= 8 ? 'color: hsl(142,71%,30%)' : ''">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" x-show="password.length >= 8" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    <circle x-show="password.length < 8" cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                Minimal 8 karakter
                            </li>
                            <li class="flex items-center gap-1.5" :style="/[A-Z]/.test(password) ? 'color: hsl(142,71%,30%)' : ''">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" x-show="/[A-Z]/.test(password)" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    <circle x-show="!/[A-Z]/.test(password)" cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                Mengandung huruf besar
                            </li>
                            <li class="flex items-center gap-1.5" :style="/[0-9]/.test(password) ? 'color: hsl(142,71%,30%)' : ''">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" x-show="/[0-9]/.test(password)" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    <circle x-show="!/[0-9]/.test(password)" cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                Mengandung angka
                            </li>
                        </ul>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirm" class="block text-sm font-medium">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            <input
                                :type="showConfirm ? 'text' : 'password'"
                                name="password_confirm"
                                id="password_confirm"
                                placeholder="Ulangi password baru"
                                class="form-input has-right"
                                x-model="confirm"
                                required>
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                                style="color: hsl(220,15%,55%);"
                                onmouseover="this.style.color='hsl(220,54%,15%)'"
                                onmouseout="this.style.color='hsl(220,15%,55%)'">
                                <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Match indicator -->
                        <p x-show="confirm.length > 0" x-cloak class="text-xs flex items-center gap-1"
                            :style="password === confirm ? 'color: hsl(142,71%,30%)' : 'color: hsl(0,72%,50%)'">
                            <span x-text="password === confirm ? '✓ Password cocok' : '✗ Password tidak cocok'"></span>
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        id="resetBtn"
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 px-6 font-semibold text-white rounded-xl transition-all text-sm"
                        style="background: hsl(220,54%,20%);"
                        onmouseover="if(!this.disabled) this.style.background='hsl(220,54%,30%)'"
                        onmouseout="if(!this.disabled) this.style.background='hsl(220,54%,20%)'">
                        <svg id="resetSpinner" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="resetBtnText">Simpan Password Baru</span>
                    </button>
                </form>

                <!-- Back to Login -->
                <p class="mt-6 text-center text-sm" style="color: hsl(220,15%,45%);">
                    <a href="<?= base_url('auth/login') ?>"
                        class="font-semibold transition-colors"
                        style="color: hsl(220,54%,20%);"
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'">
                        ← Kembali ke Login
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

    <script>
        function resetPage() {
            return {
                showPassword: false,
                showConfirm: false,
                password: '',
                confirm: '',
                strength: 0,

                checkStrength() {
                    let score = 0;
                    if (this.password.length >= 8) score++;
                    if (/[A-Z]/.test(this.password)) score++;
                    if (/[0-9]/.test(this.password)) score++;
                    if (/[^A-Za-z0-9]/.test(this.password)) score++;
                    this.strength = score;
                },

                strengthColor(level) {
                    if (this.strength === 0) return 'background: hsl(220,20%,88%)';
                    const colors = {
                        1: 'hsl(0,72%,51%)',
                        2: 'hsl(30,90%,50%)',
                        3: 'hsl(43,90%,45%)',
                        4: 'hsl(142,71%,45%)',
                    };
                    const active = colors[Math.min(this.strength, 4)] || 'hsl(220,20%,88%)';
                    return level <= this.strength
                        ? `background: ${active}`
                        : 'background: hsl(220,20%,88%)';
                },

                strengthLabel() {
                    return ['', 'Sangat Lemah', 'Lemah', 'Cukup', 'Kuat'][this.strength] || '';
                },

                strengthTextColor() {
                    return ['', 'hsl(0,72%,45%)', 'hsl(30,85%,45%)', 'hsl(43,85%,40%)', 'hsl(142,60%,35%)'][this.strength] || 'inherit';
                },
            };
        }
    </script>

</body>

</html>