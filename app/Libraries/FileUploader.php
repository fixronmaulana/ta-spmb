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

    protected int $maxSizeKb = 2048; // 2MB

    protected array $uploadDirs = [
        'dokumen' => FCPATH . 'uploads/dokumen/',
        'foto'    => FCPATH . 'uploads/foto/',
        'bukti'   => FCPATH . 'uploads/bukti/',
    ];

    /**
     * Upload file dengan validasi keamanan
     */
    public function upload(UploadedFile $file, string $type = 'dokumen'): array
    {
        // 1. Cek file valid
        if (! $file->isValid()) {
            return ['success' => false, 'message' => 'File tidak valid: ' . $file->getErrorString()];
        }

        // 2. Cek MIME type (berdasarkan magic bytes, bukan ekstensi)
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, $this->allowedMimeTypes)) {
            return [
                'success' => false,
                'message' => 'Tipe file tidak diizinkan. Hanya PDF, JPG, PNG yang diterima.',
            ];
        }

        // 3. Cek ukuran file
        if ($file->getSizeByUnit('kb') > $this->maxSizeKb) {
            return [
                'success' => false,
                'message' => 'Ukuran file terlalu besar. Maksimal ' . ($this->maxSizeKb / 1024) . 'MB.',
            ];
        }

        // 4. Tentukan direktori upload
        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 5. Generate nama file aman (UUID-based)
        $ext      = $this->getSafeExtension($mimeType);
        $safeName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;

        // 6. Pindahkan file
        if (! $file->move($uploadDir, $safeName)) {
            return ['success' => false, 'message' => 'Gagal menyimpan file. Coba lagi.'];
        }

        // 7. Verifikasi file setelah upload
        $savedPath = $uploadDir . $safeName;
        if (! file_exists($savedPath)) {
            return ['success' => false, 'message' => 'Verifikasi file gagal.'];
        }

        return [
            'success'       => true,
            'original_name' => $file->getClientName(),
            'saved_name'    => $safeName,
            'path'          => 'uploads/' . $type . '/' . $safeName,
            'mime_type'     => $mimeType,
            'size'          => $file->getSize(),
        ];
    }

    /**
     * Hapus file
     */
    public function delete(string $savedName, string $type = 'dokumen'): bool
    {
        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];
        $path      = $uploadDir . $savedName;

        if (file_exists($path)) {
            return unlink($path);
        }

        return true; // File tidak ada, dianggap sudah terhapus
    }

    /**
     * Stream file ke browser (bukan direct URL)
     */
    public function stream(string $savedName, string $type = 'dokumen', string $mimeType = 'application/pdf'): void
    {
        $uploadDir = $this->uploadDirs[$type] ?? $this->uploadDirs['dokumen'];
        $path      = $uploadDir . $savedName;

        if (! file_exists($path)) {
            throw new \RuntimeException("File tidak ditemukan: {$savedName}");
        }

        $mimeType = mime_content_type($path) ?: $mimeType;

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    /**
     * Get safe extension dari MIME type
     */
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
