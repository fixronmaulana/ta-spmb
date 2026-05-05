<header class="sticky top-0 z-20"
    style="background:white;border-bottom:1px solid hsl(220,20%,88%);box-shadow:0 1px 2px 0 hsl(220 54% 20%/.05);font-family:'Plus Jakarta Sans',sans-serif;">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">

        <!-- Mobile hamburger -->
        <button @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-xl transition-colors"
            style="color:hsl(220,15%,45%);"
            onmouseover="this.style.background='hsl(220,20%,94%)'"
            onmouseout="this.style.background='transparent'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Page title (desktop) -->
        <div class="hidden lg:block">
            <h1 class="text-base font-semibold" style="color:hsl(220,54%,15%);">
                <?= esc($pageTitle ?? $title ?? 'Dashboard') ?>
            </h1>
        </div>

        <!-- Right: notif + user -->
        <div class="flex items-center gap-2">

            <!-- Notification Bell -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="relative p-2 rounded-xl transition-colors"
                    style="color:hsl(220,15%,45%);"
                    onmouseover="this.style.background='hsl(220,20%,94%)'"
                    onmouseout="this.style.background='transparent'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
                    </svg>
                    <!-- Unread badge -->
                    <span x-show="notifCount > 0"
                        x-text="notifCount > 99 ? '99+' : notifCount"
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] text-white text-xs rounded-full flex items-center justify-center px-1 font-bold leading-none"
                        style="background:hsl(0,72%,51%);font-size:.6rem;">
                    </span>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-[min(20rem,calc(100vw-1rem))] rounded-xl overflow-hidden z-50"
                    style="background:white;border:1px solid hsl(220,20%,88%);box-shadow:0 10px 15px -3px hsl(220 54% 20%/.12),0 4px 6px -4px hsl(220 54% 20%/.08);">

                    <div class="flex items-center justify-between px-4 py-3"
                        style="border-bottom:1px solid hsl(220,20%,92%);">
                        <h3 class="font-semibold text-sm" style="color:hsl(220,54%,15%);">Notifikasi</h3>
                        <?php
                        $notifUrl = (session()->get('user_role') === 'calon_siswa')
                            ? base_url('dashboard/notifikasi')
                            : base_url('admin/notifikasi');
                        ?>
                        <a href="<?= $notifUrl ?>" class="text-xs font-medium"
                            style="color:hsl(220,54%,20%);"
                            onmouseover="this.style.textDecoration='underline'"
                            onmouseout="this.style.textDecoration='none'">
                            Lihat Semua
                        </a>
                    </div>

                    <div id="notif-dropdown-list" class="max-h-72 overflow-y-auto">
                        <div class="p-6 text-center" style="color:hsl(220,15%,55%);">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
                            </svg>
                            <p class="text-sm">Tidak ada notifikasi baru</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center gap-2 pl-1 pr-2 py-1.5 rounded-xl transition-colors"
                    onmouseover="this.style.background='hsl(220,20%,94%)'"
                    onmouseout="this.style.background='transparent'">
                    <!-- Avatar -->
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm"
                        style="background:hsl(220,54%,20%);color:hsl(43,70%,57%);">
                        <?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?>
                    </div>
                    <!-- Name (desktop) -->
                    <span class="hidden sm:block text-sm font-medium max-w-[8rem] truncate"
                        style="color:hsl(220,54%,15%);">
                        <?= esc(explode(' ', session()->get('user_name') ?? '')[0]) ?>
                    </span>
                    <!-- Chevron -->
                    <svg class="hidden sm:block w-3.5 h-3.5" style="color:hsl(220,15%,55%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-56 rounded-xl overflow-hidden z-50"
                    style="background:white;border:1px solid hsl(220,20%,88%);box-shadow:0 10px 15px -3px hsl(220 54% 20%/.12);">

                    <div class="px-4 py-3" style="border-bottom:1px solid hsl(220,20%,92%);">
                        <p class="text-sm font-semibold truncate" style="color:hsl(220,54%,15%);">
                            <?= esc(session()->get('user_name')) ?>
                        </p>
                        <p class="text-xs truncate mt-0.5" style="color:hsl(220,15%,55%);">
                            <?= esc(session()->get('user_email')) ?>
                        </p>
                    </div>

                    <div class="py-1">
                        <form action="<?= base_url('auth/logout') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-left transition-colors"
                                style="color:hsl(0,72%,51%);"
                                onmouseover="this.style.background='hsl(0,72%,51%,.08)'"
                                onmouseout="this.style.background='transparent'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>