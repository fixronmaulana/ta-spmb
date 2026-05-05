<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | SPMB SMK Al-Munawwir IIBS</title>
    <!-- Sesuaikan path CSS dengan proyek Anda -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        /* ── OTP input boxes ── */
        .otp-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0;
        }

        .otp-input {
            width: 52px;
            height: 60px;
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            border: 2px solid #d1d5db;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #f9fafb;
            color: #111827;
            -moz-appearance: textfield;
        }

        .otp-input::-webkit-outer-spin-button,
        .otp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .otp-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: #fff;
        }

        .otp-input.filled {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .otp-input.error {
            border-color: #ef4444;
            background: #fef2f2;
        }

        /* ── Countdown ── */
        #resend-countdown {
            color: #6b7280;
            font-size: 14px;
            margin-top: 12px;
        }

        #btn-resend {
            background: none;
            border: none;
            color: #2563eb;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            display: none;
        }

        #btn-resend:disabled {
            color: #9ca3af;
            cursor: not-allowed;
            text-decoration: none;
        }

        /* ── Card ── */
        .verify-card {
            max-width: 440px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 40px 36px 32px;
            text-align: center;
        }

        .verify-card .icon-wrap {
            width: 64px;
            height: 64px;
            background: #eff6ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .verify-card h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 8px;
            color: #111827;
        }

        .verify-card p {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 4px;
        }

        .verify-card .email-highlight {
            font-weight: 600;
            color: #1d4ed8;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
            text-align: left;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }

        .btn-verify:hover {
            background: #1d4ed8;
        }

        .btn-verify:active {
            transform: scale(0.98);
        }

        .btn-verify:disabled {
            background: #93c5fd;
            cursor: not-allowed;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 13px;
            color: #6b7280;
            text-decoration: none;
        }

        .back-link:hover {
            color: #374151;
            text-decoration: underline;
        }
    </style>
</head>

<body style="background: #f3f4f6; min-height: 100vh;">

    <div class="verify-card">

        <!-- Icon -->
        <div class="icon-wrap">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="M2 7l10 7 10-7" />
            </svg>
        </div>

        <h1>Verifikasi Email</h1>
        <p>Kode OTP dikirim ke</p>
        <p class="email-highlight"><?= esc($email ?? 'email Anda') ?></p>

        <!-- Alerts -->
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->has('info')): ?>
            <div class="alert alert-info"><?= esc(session('info')) ?></div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
            <div class="alert alert-error" id="alert-error"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <div class="alert alert-error" id="alert-error-js" style="display:none;"></div>
        <div class="alert alert-success" id="alert-success-js" style="display:none;"></div>

        <!-- Form OTP -->
        <form action="<?= base_url('auth/verify-otp') ?>" method="POST" id="form-otp">
            <?= csrf_field() ?>

            <div class="otp-container">
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" autocomplete="one-time-code"
                    data-index="0" autofocus>
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" data-index="1">
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" data-index="2">
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" data-index="3">
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" data-index="4">
                <input class="otp-input" type="number" maxlength="1" min="0" max="9"
                    inputmode="numeric" data-index="5">
            </div>

            <!-- Hidden input yang dikirim ke server -->
            <input type="hidden" name="otp" id="otp-value">

            <button type="submit" class="btn-verify" id="btn-verify" disabled>
                Verifikasi
            </button>
        </form>

        <!-- Resend -->
        <div style="margin-top: 20px;">
            <p id="resend-countdown">Kirim ulang dalam <span id="countdown">60</span> detik</p>
            <button id="btn-resend" onclick="resendOtp()">Kirim ulang kode</button>
        </div>

        <a href="<?= base_url('auth/login') ?>" class="back-link">← Kembali ke halaman login</a>
    </div>

    <script>
        (function() {
            'use strict';

            const inputs = Array.from(document.querySelectorAll('.otp-input'));
            const btnVerify = document.getElementById('btn-verify');
            const otpValue = document.getElementById('otp-value');

            // ── Focus & navigation ──
            inputs.forEach((input, idx) => {
                input.addEventListener('input', (e) => {
                    let val = e.target.value.replace(/\D/g, '').slice(-1);
                    e.target.value = val;

                    if (val) {
                        e.target.classList.add('filled');
                        if (idx < inputs.length - 1) inputs[idx + 1].focus();
                    } else {
                        e.target.classList.remove('filled');
                    }

                    syncOtp();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                        inputs[idx - 1].value = '';
                        inputs[idx - 1].classList.remove('filled');
                        inputs[idx - 1].focus();
                        syncOtp();
                    }
                    if (e.key === 'ArrowLeft' && idx > 0) inputs[idx - 1].focus();
                    if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
                });

                // Paste handler — tempel 6 digit sekaligus
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData)
                        .getData('text').replace(/\D/g, '').slice(0, 6);
                    pasted.split('').forEach((ch, i) => {
                        if (inputs[i]) {
                            inputs[i].value = ch;
                            inputs[i].classList.add('filled');
                        }
                    });
                    const next = inputs[Math.min(pasted.length, inputs.length - 1)];
                    if (next) next.focus();
                    syncOtp();
                });
            });

            function syncOtp() {
                const val = inputs.map(i => i.value).join('');
                otpValue.value = val;
                btnVerify.disabled = val.length < 6;
            }

            // ── Tandai error box bila ada flash error ──
            const alertErr = document.getElementById('alert-error');
            if (alertErr && alertErr.textContent.trim()) {
                inputs.forEach(i => i.classList.add('error'));
            }

            // ── Countdown resend ──
            let countdownSec = 60;
            const countdownEl = document.getElementById('countdown');
            const countdownWrap = document.getElementById('resend-countdown');
            const btnResend = document.getElementById('btn-resend');

            const timer = setInterval(() => {
                countdownSec--;
                countdownEl.textContent = countdownSec;
                if (countdownSec <= 0) {
                    clearInterval(timer);
                    countdownWrap.style.display = 'none';
                    btnResend.style.display = 'inline';
                }
            }, 1000);

            // ── Resend via fetch ──
            window.resendOtp = function() {
                btnResend.disabled = true;
                btnResend.textContent = 'Mengirim...';

                const alertErrJs = document.getElementById('alert-error-js');
                const alertOkJs = document.getElementById('alert-success-js');
                alertErrJs.style.display = 'none';
                alertOkJs.style.display = 'none';

                fetch('<?= base_url('auth/resend-otp') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                        },
                        body: JSON.stringify({}),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            alertOkJs.textContent = data.message;
                            alertOkJs.style.display = 'block';
                            // Reset countdown
                            countdownSec = 60;
                            countdownEl.textContent = countdownSec;
                            btnResend.style.display = 'none';
                            countdownWrap.style.display = '';
                            const t2 = setInterval(() => {
                                countdownSec--;
                                countdownEl.textContent = countdownSec;
                                if (countdownSec <= 0) {
                                    clearInterval(t2);
                                    countdownWrap.style.display = 'none';
                                    btnResend.style.display = 'inline';
                                    btnResend.disabled = false;
                                    btnResend.textContent = 'Kirim ulang kode';
                                }
                            }, 1000);
                        } else {
                            alertErrJs.textContent = data.message;
                            alertErrJs.style.display = 'block';
                            btnResend.disabled = false;
                            btnResend.textContent = 'Kirim ulang kode';
                        }
                    })
                    .catch(() => {
                        alertErrJs.textContent = 'Gagal terhubung. Coba lagi.';
                        alertErrJs.style.display = 'block';
                        btnResend.disabled = false;
                        btnResend.textContent = 'Kirim ulang kode';
                    });
            };
        })();
    </script>

</body>

</html>