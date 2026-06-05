<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class FileUploader
{
    protected array $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    protected int $maxSizeKb = 2048;

    // UBAH: dari FCPATH (public/) ke WRITEPATH (writable/)
    protected array $uploadDirs = [
        'dokumen' => WRITEPATH . 'uploads/dokumen/',
        'foto'    => WRITEPATH . 'uploads/foto/',
        'bukti'   => WRITEPATH . 'uploads/bukti/',
    ];

    public function upload(UploadedFile $file, string $type = 'dokumen'): array
    {
        if (! $file->isValid()) {
            return ['success' => false, 'message' => 'File tidak valid: ' . $file->getErrorString()];
        }

        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, $this->allowedMimeTypes)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya PDF, JPG, PNG yang diterima.'];
        }

        if ($file->getSizeByUnit('kb') > $this->maxSizeKb) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal ' . ($this->maxSizeKb / 1024) . 'MB.'];
        }

        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = $this->getSafeExtension($mimeType);
        $safeName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;

        if (! $file->move($uploadDir, $safeName)) {
            return ['success' => false, 'message' => 'Gagal menyimpan file. Coba lagi.'];
        }

        $savedPath = $uploadDir . $safeName;
        if (! file_exists($savedPath)) {
            return ['success' => false, 'message' => 'Verifikasi file gagal.'];
        }

        return [
            'success'       => true,
            'original_name' => $file->getClientName(),
            'saved_name'    => $safeName,
            // path yang disimpan ke DB tetap sama formatnya
            'path'          => 'uploads/' . $type . '/' . $safeName,
            'mime_type'     => $mimeType,
            'size'          => $file->getSize(),
        ];
    }

    public function delete(string $savedName, string $type = 'dokumen'): bool
    {
        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];
        $path      = $uploadDir . $savedName;

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    public function stream(string $savedName, string $type = 'dokumen', string $mimeType = 'application/pdf'): void
    {
        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];
        $path      = $uploadDir . $savedName;

        if (! file_exists($path)) {
            throw new \RuntimeException("File tidak ditemukan: {$savedName}");
        }

        // Deteksi MIME dari file asli (lebih aman dari header user)
        $mimeType = mime_content_type($path) ?: $mimeType;

        // Security headers
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="dokumen.' . pathinfo($path, PATHINFO_EXTENSION) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        header('Content-Security-Policy: default-src \'none\'');

        readfile($path);
        exit;
    }

    private function getSafeExtension(string $mimeType): string
    {
        $map = [
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/jpg'       => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
        ];
        return $map[$mimeType] ?? 'bin';
    }
}
