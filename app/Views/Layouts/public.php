<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SPMB') ?> — SMK Al-Munawwir IIBS</title>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">

    <!-- Google Fonts: Plus Jakarta Sans + Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'hsl(220, 54%, 20%)',
                            light: 'hsl(220, 54%, 30%)',
                            dark: 'hsl(220, 54%, 14%)',
                            fg: 'hsl(45, 70%, 95%)',
                        },
                        secondary: {
                            DEFAULT: 'hsl(43, 70%, 47%)',
                            light: 'hsl(43, 80%, 55%)',
                            fg: 'hsl(220, 54%, 15%)',
                        },
                        accent: {
                            DEFAULT: 'hsl(160, 60%, 40%)',
                        },
                        gold: 'hsl(43, 70%, 47%)',
                        navy: 'hsl(220, 54%, 20%)',
                        info: 'hsl(199, 89%, 48%)',
                        success: 'hsl(142, 71%, 45%)',
                        warning: 'hsl(38, 92%, 50%)',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                        'slide-down': 'slideDown 0.3s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            from: {
                                opacity: '0'
                            },
                            to: {
                                opacity: '1'
                            }
                        },
                        slideUp: {
                            from: {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            to: {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        slideDown: {
                            from: {
                                opacity: '0',
                                transform: 'translateY(-10px)'
                            },
                            to: {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
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
            background-color: hsl(45, 30%, 98%);
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        /* Card elevated */
        .card-elevated {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid hsl(220, 20%, 88%);
            box-shadow: 0 4px 6px -1px hsl(220 54% 20% / 0.1), 0 2px 4px -2px hsl(220 54% 20% / 0.1);
            transition: all 0.3s ease;
        }

        .card-elevated:hover {
            box-shadow: 0 10px 15px -3px hsl(220 54% 20% / 0.1), 0 4px 6px -4px hsl(220 54% 20% / 0.1);
            transform: translateY(-2px);
        }

        /* Nav active state */
        .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.15s;
            color: hsl(220, 15%, 45%);
        }

        .nav-link:hover {
            color: hsl(220, 54%, 15%);
            background: hsl(220, 20%, 95%);
        }

        .nav-link.active {
            background: hsl(220, 54%, 20%, 0.1);
            color: hsl(220, 54%, 20%);
        }

        /* Hero pattern */
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 9999px;
        }

        .badge-secondary {
            background: hsl(43, 70%, 47%, 0.15);
            color: hsl(43, 70%, 35%);
            border: 1px solid hsl(43, 70%, 47%, 0.3);
        }

        .badge-outline {
            background: transparent;
            border: 1px solid hsl(220, 20%, 88%);
            color: hsl(220, 15%, 45%);
        }

        /* Accordion */
        details summary {
            cursor: pointer;
            list-style: none;
        }

        details summary::-webkit-details-marker {
            display: none;
        }

        details[open] .accordion-icon {
            transform: rotate(180deg);
        }

        .accordion-icon {
            transition: transform 0.2s;
        }

        /* Input/Textarea */
        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
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
            box-shadow: 0 0 0 3px hsl(220, 54%, 30%, 0.1);
        }

        .form-input::placeholder {
            color: hsl(220, 15%, 65%);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col" x-data="publicLayout()">

    <!-- ===================== HEADER ===================== -->
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur" style="border-color: hsl(220,20%,88%); backdrop-filter: blur(8px);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <!-- Logo -->
                <a href="<?= base_url('/') ?>" class="flex items-center gap-3">
                    <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS" class="w-10 h-10 rounded-full flex-shrink-0 object-cover" style="background: hsl(220,54%,20%);">
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold" style="color: hsl(220,54%,20%);">SMK Al-Munawwir</p>
                        <p class="text-xs" style="color: hsl(220,15%,45%);">IIBS Banyuwangi</p>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center gap-1">
                    <?php
                    $currentPath = '/' . ltrim(current_url(true)->getPath(), '/');
                    $navLinks = [
                        '/'              => 'Home',
                        '/profil-sekolah' => 'Profil Sekolah',
                        '/jurusan'       => 'Jurusan',
                        '/panduan'       => 'Panduan SPMB',
                        '/kontak'        => 'Kontak',
                    ];
                    foreach ($navLinks as $path => $label):
                        $isActive = ($currentPath === $path) ? 'active' : '';
                    ?>
                        <a href="<?= base_url($path === '/' ? '/' : ltrim($path, '/')) ?>" class="nav-link <?= $isActive ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Auth Area -->
                <div class="flex items-center gap-3">
                    <?php if (session()->get('logged_in')): ?>
                        <a href="<?= base_url('dashboard/notifikasi') ?>" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" style="color: hsl(220,15%,45%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: hsl(220,54%,20%);">
                                    <?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="hidden md:inline text-sm font-medium"><?= esc(explode(' ', session()->get('user_name') ?? '')[0]) ?></span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border py-1 z-50"
                                style="border-color: hsl(220,20%,88%);">
                                <div class="px-4 py-3 border-b" style="border-color: hsl(220,20%,88%);">
                                    <p class="font-semibold text-sm"><?= esc(session()->get('user_name')) ?></p>
                                    <p class="text-xs" style="color: hsl(220,15%,45%);"><?= esc(session()->get('user_email')) ?></p>
                                </div>
                                <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">Dashboard</a>
                                <button type="button"
                                    onclick="document.getElementById('logoutModal').classList.remove('hidden')"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-left"
                                    style="color: hsl(0,72%,51%);">Logout</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('auth/login') ?>"
                            class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors"
                            style="background: hsl(220,54%,20%);"
                            onmouseover="this.style.background='hsl(220,54%,30%)'"
                            onmouseout="this.style.background='hsl(220,54%,20%)'">
                            Login
                        </a>
                    <?php endif; ?>

                    <!-- Mobile hamburger -->
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                        <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div x-show="mobileOpen" x-cloak x-transition:enter="animate-slide-down" class="lg:hidden py-4 border-t" style="border-color: hsl(220,20%,88%);">
                <div class="flex flex-col gap-1">
                    <?php foreach ($navLinks as $path => $label):
                        $isActive = ($currentPath === $path) ? 'active' : '';
                    ?>
                        <a href="<?= base_url($path === '/' ? '/' : ltrim($path, '/')) ?>" class="nav-link <?= $isActive ?>" @click="mobileOpen = false">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ===================== FLASH MESSAGES ===================== -->
    <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <?php if ($msg = session()->getFlashdata('success')): ?>
                <div class="flex items-center gap-3 p-4 rounded-lg text-sm mb-2" style="background: hsl(142,71%,45%,0.1); border: 1px solid hsl(142,71%,45%,0.3); color: hsl(142,71%,30%);">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <?= esc($msg) ?>
                </div>
            <?php endif; ?>
            <?php if ($msg = session()->getFlashdata('error')): ?>
                <div class="flex items-center gap-3 p-4 rounded-lg text-sm mb-2" style="background: hsl(0,72%,51%,0.1); border: 1px solid hsl(0,72%,51%,0.3); color: hsl(0,72%,40%);">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <?= esc($msg) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ===================== PAGE CONTENT ===================== -->
    <main class="flex-1">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- ===================== FOOTER ===================== -->
    <footer style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS" class="w-12 h-12 rounded-full flex-shrink-0 object-cover" style="background: hsl(43,70%,47%);">
                        <div>
                            <p class="font-bold font-serif text-lg">SMK Al-Munawwir</p>
                            <p class="text-sm opacity-80">International Islamic Boarding School</p>
                        </div>
                    </div>
                    <p class="text-sm opacity-80 leading-relaxed">
                        Mencetak generasi yang unggul dalam ilmu pengetahuan, teknologi, dan akhlakul karimah.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-serif font-bold text-lg mb-4">Menu</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= base_url('/') ?>" class="text-sm opacity-80 hover:opacity-100 transition-opacity">Home</a></li>
                        <li><a href="<?= base_url('profil-sekolah') ?>" class="text-sm opacity-80 hover:opacity-100 transition-opacity">Profil Sekolah</a></li>
                        <li><a href="<?= base_url('jurusan') ?>" class="text-sm opacity-80 hover:opacity-100 transition-opacity">Jurusan</a></li>
                        <li><a href="<?= base_url('panduan') ?>" class="text-sm opacity-80 hover:opacity-100 transition-opacity">Panduan SPMB</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-serif font-bold text-lg mb-4">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 opacity-80 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-sm opacity-80">Jl. Kedungliwung No.35, Kemiri, Singojuruh, Banyuwangi</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-sm opacity-80">(0333) xxx-xxxx</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm opacity-80">spmb@smk-almunawwir.sch.id</span>
                        </li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h4 class="font-serif font-bold text-lg mb-4">Media Sosial</h4>
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke-width="2" />
                                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" stroke-width="2" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke-width="2" />
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z" />
                                <polygon fill="white" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="mt-10 pt-6 border-t text-center" style="border-color: rgba(255,255,255,0.1);">
                <p class="text-sm opacity-60">
                    &copy; <?= date('Y') ?> SMK Al-Munawwir IIBS. All rights reserved | Built by Fikron with support from TRPL Poliwangi
                    <img src="<?= base_url('assets/logo/logo-poliwangi.png') ?>"
                        alt="Logo Politeknik Negeri Banyuwangi"
                        class="inline-block align-middle h-6 w-auto object-contain ml-1"
                        loading="lazy">
                </p>
            </div>
        </div>
    </footer>

    <script>
        function publicLayout() {
            return {
                mobileOpen: false,
                init() {
                    this.$watch('mobileOpen', val => {
                        document.body.style.overflow = val ? 'hidden' : '';
                    });
                }
            };
        }
    </script>

    <!-- ═══════════════════════════════════════════════════════════════
         LOGOUT CONFIRMATION MODAL (public layout)
         PERBAIKAN: Modal konfirmasi logout untuk halaman publik
    ═══════════════════════════════════════════════════════════════ -->
    <div id="logoutModal"
        class="hidden fixed inset-0 z-[999] flex items-center justify-center"
        style="background:rgba(0,0,0,0.45);font-family:'Plus Jakarta Sans',sans-serif;"
        onclick="if(event.target===this) this.classList.add('hidden')">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden"
            style="animation: modalFadeIn .2s ease;">

            <!-- Icon -->
            <div class="flex justify-center pt-8 pb-2">
                <div class="w-16 h-16 rounded-full flex items-center justify-center"
                    style="background:hsl(0,72%,51%,.1);">
                    <svg class="w-8 h-8" style="color:hsl(0,72%,51%);" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                </div>
            </div>

            <!-- Text -->
            <div class="text-center px-6 pt-2 pb-6">
                <h3 class="text-lg font-bold mb-2" style="color:hsl(220,54%,15%);">Konfirmasi Logout</h3>
                <p class="text-sm" style="color:hsl(220,15%,45%);line-height:1.7;">
                    Apakah Anda yakin ingin keluar dari sistem SPMB?
                    Anda perlu login kembali untuk mengakses dashboard.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 px-6 pb-6">
                <!-- Batal -->
                <button type="button"
                    onclick="document.getElementById('logoutModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold border-2 transition-colors"
                    style="border-color:hsl(220,54%,20%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,54%,20%,.08)'"
                    onmouseout="this.style.background='white'">
                    Batal
                </button>

                <!-- Ya, Logout -->
                <form action="<?= base_url('auth/logout') ?>" method="post" class="flex-1">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                        style="background:hsl(0,72%,51%);"
                        onmouseover="this.style.background='hsl(0,72%,42%)'"
                        onmouseout="this.style.background='hsl(0,72%,51%)'">
                        Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(.95) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>


</body>

</html>