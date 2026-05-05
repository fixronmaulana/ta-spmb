<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Registrasi') ?> — SMK Al-Munawwir IIBS</title>
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
                            from: {
                                opacity: '0',
                                transform: 'scale(0.95)'
                            },
                            to: {
                                opacity: '1',
                                transform: 'scale(1)'
                            }
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
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: hsl(220, 54%, 15%);
            background: hsl(45, 30%, 98%);
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

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

        .form-input::placeholder {
            color: hsl(220, 15%, 65%);
        }

        .form-input.has-error {
            border-color: hsl(0, 72%, 51%);
            box-shadow: 0 0 0 2px hsl(0, 72%, 51%, 0.15);
        }

        .form-input.has-success {
            border-color: hsl(142, 71%, 45%);
            box-shadow: 0 0 0 2px hsl(142, 71%, 45%, 0.15);
        }

        .form-input.has-right {
            padding-right: 2.75rem;
        }

        .form-input.no-icon {
            padding-left: 0.75rem;
        }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Progress bar animation */
        .strength-bar {
            transition: width 0.3s ease, background-color 0.3s ease;
        }
    </style>
</head>

<body class="min-h-screen flex" x-data="registerPage()">

    <div class="min-h-screen flex w-full">

        <!-- ============ LEFT: DECORATIVE PANEL ============ -->
        <div class="hidden lg:flex lg:flex-1 relative overflow-hidden" style="background: hsl(220,54%,20%);">
            <!-- Pattern overlay -->
            <div class="absolute inset-0 opacity-10 hero-pattern"></div>

            <div class="flex items-center justify-center w-full p-12 relative">
                <div class="text-center">
                    <div class="w-32 h-32 rounded-full flex items-center justify-center text-5xl font-bold font-serif mx-auto mb-6"
                        style="background: hsl(43,70%,47%,0.15); color: hsl(43,70%,57%); border: 3px solid hsl(43,70%,47%,0.3);">
                        M
                    </div>
                    <h2 class="text-3xl font-bold font-serif mb-4" style="color: hsl(45,70%,95%);">
                        SPMB 2026/2027
                    </h2>
                    <p class="max-w-sm" style="color: rgba(255,255,255,0.75);">
                        Daftarkan diri Anda sekarang dan mulai perjalanan pendidikan di SMK Al-Munawwir IIBS
                    </p>
                </div>
            </div>
        </div>

        <!-- ============ RIGHT: FORM PANEL ============ -->
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
                        <li class="font-medium" style="color: hsl(220,54%,15%);">Registrasi</li>
                    </ol>
                </nav>

                <!-- Header (logo visible only on mobile) -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold font-serif flex-shrink-0 lg:hidden"
                        style="background: hsl(220,54%,20%);">M</div>
                    <div>
                        <h1 class="text-2xl font-bold font-serif">REGISTRASI AKUN</h1>
                        <p class="text-sm" style="color: hsl(220,15%,45%);">Buat akun baru untuk pendaftaran SPMB</p>
                    </div>
                </div>

                <!-- Server-side errors -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="flex items-center gap-3 p-4 rounded-lg mb-6 animate-scale-in text-sm"
                        style="background: hsl(0,72%,51%,0.08); border: 1px solid hsl(0,72%,51%,0.25); color: hsl(0,72%,40%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                        </svg>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="p-4 rounded-lg mb-6 animate-scale-in text-sm"
                        style="background: hsl(0,72%,51%,0.08); border: 1px solid hsl(0,72%,51%,0.25); color: hsl(0,72%,40%);">
                        <div class="flex items-center gap-2 font-semibold mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                            </svg>
                            Terdapat kesalahan pada input:
                        </div>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                <li><?= esc($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form id="registerForm" action="<?= base_url('auth/register') ?>" method="POST" @submit.prevent="handleSubmit">
                    <?= csrf_field() ?>

                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5 mb-5">
                        <label for="name" class="block text-sm font-medium" style="color: hsl(220,54%,15%);">
                            Nama Lengkap
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Masukkan nama lengkap Anda"
                                class="form-input"
                                :class="{ 'has-error': clientErrors.name, 'has-success': form.name && !clientErrors.name }"
                                x-model="form.name"
                                @input="clearError('name')"
                                value="<?= old('name') ?>"
                                required>
                        </div>
                        <template x-if="clientErrors.name">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.name"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5 mb-5">
                        <label for="email" class="block text-sm font-medium" style="color: hsl(220,54%,15%);">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="nama@email.com"
                                class="form-input"
                                :class="{ 'has-error': clientErrors.email, 'has-success': form.email && !clientErrors.email }"
                                x-model="form.email"
                                @input="clearError('email')"
                                value="<?= old('email') ?>"
                                required>
                        </div>
                        <template x-if="clientErrors.email">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.email"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Nomor Telepon (Optional) -->
                    <div class="space-y-1.5 mb-5">
                        <label for="phone" class="block text-sm font-medium" style="color: hsl(220,54%,15%);">
                            Nomor Telepon
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                placeholder="085231491879"
                                class="form-input"
                                :class="{ 'has-error': clientErrors.phone, 'has-success': form.phone && !clientErrors.phone }"
                                x-model="form.phone"
                                @input="clearError('phone')"
                                value="<?= old('phone') ?>">
                        </div>
                        <template x-if="clientErrors.phone">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.phone"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5 mb-5">
                        <label for="password" class="block text-sm font-medium" style="color: hsl(220,54%,15%);">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                placeholder="••••••••••••"
                                class="form-input has-right"
                                :class="{ 'has-error': clientErrors.password, 'has-success': form.password && !clientErrors.password }"
                                x-model="form.password"
                                @input="clearError('password')"
                                required>
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                                @click="showPassword = !showPassword"
                                style="color: hsl(220,15%,55%);">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <template x-if="form.password">
                            <div class="mt-2">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span style="color: hsl(220,15%,50%);">Kekuatan Password:</span>
                                    <span x-text="passwordStrength.level" :style="{ color: passwordStrength.textColor }"></span>
                                </div>
                                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="strength-bar h-full transition-all rounded-full"
                                        :style="{ width: passwordStrength.width, backgroundColor: passwordStrength.color }">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="clientErrors.password">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.password"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-1.5 mb-6">
                        <label for="confirmPassword" class="block text-sm font-medium" style="color: hsl(220,54%,15%);">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5" style="color: hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                :type="showConfirm ? 'text' : 'password'"
                                id="confirmPassword"
                                name="password_confirm"
                                placeholder="••••••••••••"
                                class="form-input has-right"
                                :class="{ 'has-error': clientErrors.confirmPassword, 'has-success': form.confirmPassword && !clientErrors.confirmPassword && form.password === form.confirmPassword }"
                                x-model="form.confirmPassword"
                                @input="clearError('confirmPassword')"
                                required>
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                                @click="showConfirm = !showConfirm"
                                style="color: hsl(220,15%,55%);">
                                <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <template x-if="form.confirmPassword && form.password === form.confirmPassword">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(142,71%,45%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Password cocok</span>
                            </p>
                        </template>
                        <template x-if="clientErrors.confirmPassword">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.confirmPassword"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="space-y-1.5">
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                id="agree"
                                name="agree"
                                value="1"
                                class="w-4 h-4 rounded border flex-shrink-0 mt-0.5"
                                style="accent-color: hsl(220,54%,20%);"
                                x-model="agreedToTerms"
                                @change="clearError('terms')">
                            <label for="agree" class="text-sm leading-relaxed cursor-pointer" style="color: hsl(220,15%,40%);">
                                Saya menyetujui
                                <a href="#" class="font-medium transition-colors" style="color: hsl(220,54%,20%);"
                                    onmouseover="this.style.textDecoration='underline'"
                                    onmouseout="this.style.textDecoration='none'">
                                    Syarat dan Ketentuan
                                </a>
                                serta
                                <a href="#" class="font-medium transition-colors" style="color: hsl(220,54%,20%);"
                                    onmouseover="this.style.textDecoration='underline'"
                                    onmouseout="this.style.textDecoration='none'">
                                    Kebijakan Privasi
                                </a>
                            </label>
                        </div>
                        <template x-if="clientErrors.terms">
                            <p class="flex items-center gap-1 text-sm" style="color: hsl(0,72%,51%);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                </svg>
                                <span x-text="clientErrors.terms"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 px-6 font-semibold text-white rounded-xl transition-all text-sm mt-6"
                        style="background: hsl(220,54%,20%);"
                        :style="submitting ? 'opacity:0.7; cursor:not-allowed;' : ''"
                        onmouseover="if(!this.disabled) this.style.background='hsl(220,54%,30%)'"
                        onmouseout="this.style.background='hsl(220,54%,20%)'"
                        :disabled="submitting">
                        <template x-if="submitting">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </template>
                        <template x-if="submitting"><span>Memproses...</span></template>
                        <template x-if="!submitting"><span>Daftar</span></template>
                    </button>
                </form>

                <!-- Login Link -->
                <p class="mt-6 text-center text-sm" style="color: hsl(220,15%,45%);">
                    Sudah punya akun?
                    <a href="<?= base_url('auth/login') ?>"
                        class="font-semibold transition-colors"
                        style="color: hsl(220,54%,20%);"
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'">
                        Login di sini
                    </a>
                </p>

            </div>
        </div>

    </div>

    <script>
        function registerPage() {
            return {
                showPassword: false,
                showConfirm: false,
                agreedToTerms: false,
                submitting: false,

                form: {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    confirmPassword: '',
                },

                clientErrors: {},

                get passwordStrength() {
                    const p = this.form.password;
                    if (!p) return {
                        level: '',
                        width: '0%',
                        color: '',
                        textColor: ''
                    };
                    if (p.length < 6)
                        return {
                            level: 'Lemah',
                            width: '33%',
                            color: 'hsl(0,72%,51%)',
                            textColor: 'hsl(0,72%,45%)'
                        };
                    if (p.length < 8 || !/[0-9]/.test(p))
                        return {
                            level: 'Sedang',
                            width: '66%',
                            color: 'hsl(38,92%,50%)',
                            textColor: 'hsl(38,60%,40%)'
                        };
                    return {
                        level: 'Kuat',
                        width: '100%',
                        color: 'hsl(142,71%,45%)',
                        textColor: 'hsl(142,71%,35%)'
                    };
                },

                clearError(field) {
                    this.clientErrors = {
                        ...this.clientErrors,
                        [field]: ''
                    };
                },

                validate() {
                    const e = {};

                    if (!this.form.name || this.form.name.length < 3)
                        e.name = 'Nama lengkap minimal 3 karakter';

                    if (!this.form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email))
                        e.email = 'Format email tidak valid';

                    if (this.form.phone && !/^08[0-9]{8,11}$/.test(this.form.phone))
                        e.phone = 'Format nomor HP tidak valid (08xxxxxxxxxx)';

                    if (!this.form.password || this.form.password.length < 8)
                        e.password = 'Password minimal 8 karakter';

                    if (this.form.password !== this.form.confirmPassword)
                        e.confirmPassword = 'Password tidak cocok';

                    if (!this.agreedToTerms)
                        e.terms = 'Anda harus menyetujui syarat dan ketentuan';

                    this.clientErrors = e;
                    return Object.keys(e).length === 0;
                },

                handleSubmit(e) {
                    e.preventDefault();

                    // Validasi client-side terlebih dahulu
                    if (!this.validate()) {
                        this.submitting = false;
                        return;
                    }

                    // Set submitting state
                    this.submitting = true;

                    // Submit form secara native
                    const form = document.getElementById('registerForm');
                    if (form) {
                        form.submit();
                    }
                },
            };
        }
    </script>
</body>

</html>