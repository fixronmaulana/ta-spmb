<!-- 
    File: app/Modules/Notifikasi/Views/index.php
    Halaman notifikasi calon siswa — disesuaikan dengan mockup React
-->

<div class="max-w-3xl mx-auto space-y-6" x-data="notifikasiPage()">

    <!-- ── Header ────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifikasi
                <span x-show="unreadCount > 0"
                    x-text="unreadCount"
                    class="ml-2 px-2 py-0.5 bg-red-500 text-white text-sm font-bold rounded-full">
                </span>
            </h1>
            <p class="text-gray-500 text-sm mt-1">Pantau perkembangan pendaftaran Anda</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <!-- Filter -->
            <select x-model="filter"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="all">Semua Notifikasi</option>
                <option value="unread">Belum Dibaca</option>
                <option value="read">Sudah Dibaca</option>
            </select>

            <!-- Tandai semua dibaca -->
            <button x-show="unreadCount > 0"
                @click="markAllRead()"
                :disabled="markingAll"
                class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white hover:bg-gray-50 disabled:opacity-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tandai Semua Dibaca
            </button>
        </div>
    </div>

    <!-- ── Notification List ──────────────────────────────────── -->
    <div class="space-y-3">

        <!-- Empty State -->
        <template x-if="filteredNotifs.length === 0">
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-400 text-sm"
                    x-text="filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca' : 'Belum ada notifikasi'">
                </p>
            </div>
        </template>

        <!-- Notification Items -->
        <template x-for="notif in filteredNotifs" :key="notif.id">
            <div :class="[
                    'bg-white rounded-2xl border overflow-hidden transition-all',
                    !notif.is_read ? 'border-l-4 border-l-blue-400 border-t-gray-200 border-r-gray-200 border-b-gray-200' : 'border-gray-200'
                 ]"
                :style="!notif.is_read ? 'background-color: rgba(59,130,246,0.03)' : ''">

                <!-- Main Row (clickable) -->
                <button class="w-full p-4 text-left hover:bg-gray-50/60 transition-colors"
                    @click="toggle(notif)">
                    <div class="flex items-start gap-4">

                        <!-- Icon + unread dot -->
                        <div class="relative shrink-0">
                            <!-- Success icon -->
                            <template x-if="notif.type === 'success'">
                                <span class="flex h-5 w-5 items-center justify-center text-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </template>
                            <!-- Error icon -->
                            <template x-if="notif.type === 'error'">
                                <span class="flex h-5 w-5 items-center justify-center text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </template>
                            <!-- Warning icon -->
                            <template x-if="notif.type === 'warning'">
                                <span class="flex h-5 w-5 items-center justify-center text-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </template>
                            <!-- Info / default icon -->
                            <template x-if="notif.type === 'info' || !notif.type">
                                <span class="flex h-5 w-5 items-center justify-center text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </template>
                            <!-- Unread dot -->
                            <span x-show="!notif.is_read"
                                class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-500 rounded-full"></span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p :class="['font-medium text-sm', !notif.is_read ? 'text-gray-900' : 'text-gray-500']"
                                x-text="notif.title"></p>
                            <p class="text-sm text-gray-500 mt-1 line-clamp-1"
                                x-text="notif.expanded ? notif.message : notif.preview"></p>
                            <p class="text-xs text-gray-400 mt-2" x-text="notif.time"></p>
                        </div>

                        <!-- Chevron -->
                        <svg xmlns="http://www.w3.org/2000/svg"
                            :class="['h-5 w-5 text-gray-400 shrink-0 transition-transform duration-200', notif.expanded ? 'rotate-90' : '']"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>

                <!-- Expanded Detail -->
                <div x-show="notif.expanded"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="px-4 pb-4">
                    <div :class="[
                            'p-4 rounded-xl text-sm',
                            notif.type === 'error'   ? 'bg-red-50 text-red-800'    :
                            notif.type === 'success' ? 'bg-green-50 text-green-800':
                            notif.type === 'warning' ? 'bg-yellow-50 text-yellow-800':
                            'bg-blue-50 text-blue-800'
                        ]"
                        x-text="notif.message">
                    </div>
                    <!-- Action link if available -->
                    <template x-if="notif.action_url && notif.action_url !== '#'">
                        <a :href="notif.action_url"
                            class="text-xs text-blue-600 hover:underline mt-2 inline-block font-medium">
                            Lihat Detail →
                        </a>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    function notifikasiPage() {
        // Data notifikasi dari PHP — di-encode sebagai JSON agar Alpine dapat membacanya
        const rawData = <?= json_encode(array_map(function ($n) {
                            $data = json_decode($n->data ?? '{}', true);
                            $words = explode(' ', $n->message ?? '');
                            $preview = implode(' ', array_slice($words, 0, 12)) . (count($words) > 12 ? '...' : '');

                            // Determine type from title/message keywords
                            $type = 'info';
                            $titleLower = strtolower($n->title ?? '');
                            $msgLower = strtolower($n->message ?? '');
                            if (str_contains($titleLower, 'valid') || str_contains($titleLower, 'berhasil') || str_contains($titleLower, 'diterima')) {
                                $type = 'success';
                            } elseif (str_contains($titleLower, 'perbaikan') || str_contains($titleLower, 'ditolak') || str_contains($titleLower, 'gagal') || str_contains($msgLower, 'perbaikan')) {
                                $type = 'error';
                            } elseif (str_contains($titleLower, 'perhatian') || str_contains($titleLower, 'peringatan')) {
                                $type = 'warning';
                            }

                            return [
                                'id'         => $n->id,
                                'title'      => $n->title ?? '',
                                'message'    => $n->message ?? '',
                                'preview'    => $preview,
                                'action_url' => $n->action_url ?? ($data['url'] ?? '#'),
                                'is_read'    => (bool)$n->is_read,
                                'type'       => $type,
                                'time'       => date('d M Y, H:i', strtotime($n->created_at)),
                                'expanded'   => false,
                            ];
                        }, $notifikasis ?? [])) ?>;

        return {
            filter: 'all',
            markingAll: false,
            notifications: rawData,

            get unreadCount() {
                return this.notifications.filter(n => !n.is_read).length;
            },

            get filteredNotifs() {
                return this.notifications.filter(n => {
                    if (this.filter === 'unread') return !n.is_read;
                    if (this.filter === 'read') return n.is_read;
                    return true;
                });
            },

            toggle(notif) {
                notif.expanded = !notif.expanded;
                if (!notif.is_read) {
                    this.markRead(notif);
                }
            },

            markRead(notif) {
                notif.is_read = true;
                fetch('<?= base_url('notifikasi/mark-read') ?>/' + notif.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                }).catch(() => {});
            },

            markAllRead() {
                this.markingAll = true;
                fetch('<?= base_url('api/notifikasi/mark-all-read') ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                }).then(() => {
                    this.notifications.forEach(n => n.is_read = true);
                }).finally(() => {
                    this.markingAll = false;
                });
            }
        }
    }
</script>