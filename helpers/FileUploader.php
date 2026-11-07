<?php
require_once __DIR__ . '/../config/config.php';

class FileUploader {
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;

    public static function upload(array $file, string $targetDir = UPLOAD_DIR): string {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new InvalidArgumentException('Invalid file upload parameter.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file was uploaded.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('File exceeds server maximum upload size.');
            default:
                throw new RuntimeException('Unknown file upload error.');
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new RuntimeException('File size exceeds the 2MB limit.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new RuntimeException('Invalid image format. Only JPG, PNG, and WEBP are permitted.');
        }

        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $fileName = sprintf('prod_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Failed to create upload destination directory.');
        }

        $destination = $targetDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to move uploaded file to target directory.');
        }

        return 'uploads/products/' . $fileName;
    }

    public static function delete(string $relativePath): bool {
        $basePath = realpath(__DIR__ . '/../');
        $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $relativePath);

        if ($fullPath && str_starts_with($fullPath, $basePath . DIRECTORY_SEPARATOR . 'uploads') && file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}