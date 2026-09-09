<?php

if (! function_exists('is_image_mime')) {
    function is_image_mime(?string $mime): bool
    {
        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
    }
}

if (! function_exists('file_icon_class')) {
    /**
     * Nucleo icon class + warna buat file non-gambar, berdasarkan mime type.
     */
    function file_icon_class(?string $mime): string
    {
        return match (true) {
            $mime === 'application/pdf' => 'ni ni-single-copy-04 text-danger',
            in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true) => 'ni ni-single-copy-04 text-info',
            in_array($mime, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], true) => 'ni ni-single-copy-04 text-success',
            in_array($mime, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], true) => 'ni ni-single-copy-04 text-warning',
            in_array($mime, ['application/zip', 'application/x-zip-compressed'], true) => 'ni ni-archive-2 text-secondary',
            default => 'ni ni-single-copy-04 text-secondary',
        };
    }
}

if (! function_exists('format_file_size')) {
    function format_file_size(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}

if (! function_exists('allowed_attachment_mimes')) {
    function allowed_attachment_mimes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-zip-compressed',
            'text/plain',
        ];
    }
}
