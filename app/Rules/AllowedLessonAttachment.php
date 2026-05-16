<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AllowedLessonAttachment implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_EXTENSIONS = [
        'pdf',
        'zip',
        'txt',
        'md',
        'doc',
        'docx',
        'xlsx',
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    /**
     * @var array<int, string>
     */
    private const BLOCKED_MIME_TYPES = [
        'application/javascript',
        'application/x-httpd-php',
        'application/x-msdownload',
        'application/x-php',
        'application/x-sh',
        'application/xhtml+xml',
        'image/svg+xml',
        'text/html',
        'text/javascript',
        'text/php',
        'text/x-php',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('The uploaded attachment is invalid.');

            return;
        }

        $extension = strtolower((string) $value->getClientOriginalExtension());
        $mimeType = strtolower((string) ($value->getMimeType() ?: ''));

        if ($extension === '' || ! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $fail('Only PDF, ZIP, TXT, MD, DOC, DOCX, XLSX, PNG, JPG, JPEG, and WEBP files are allowed.');

            return;
        }

        if ($mimeType !== '' && in_array($mimeType, self::BLOCKED_MIME_TYPES, true)) {
            $fail('This file type is not allowed for lesson attachments.');
        }
    }
}
