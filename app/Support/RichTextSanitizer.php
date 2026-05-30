<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'a',
        'blockquote',
        'br',
        'code',
        'div',
        'em',
        'figcaption',
        'figure',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'img',
        'i',
        'li',
        'ol',
        'p',
        'pre',
        'span',
        'strong',
        'u',
        'ul',
    ];

    /**
     * @var array<int, string>
     */
    private const REMOVE_WITH_CONTENT = [
        'button',
        'embed',
        'form',
        'iframe',
        'input',
        'link',
        'meta',
        'object',
        'script',
        'style',
        'svg',
        'textarea',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_MEDIA_CLASSES = [
        'media-block',
        'media-center',
        'media-start',
        'media-end',
        'media-w-25',
        'media-w-33',
        'media-w-50',
        'media-w-66',
        'media-w-100',
        'media-wrap',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_TEXT_CLASSES = [
        'text-align-start',
        'text-align-center',
        'text-align-end',
        'text-align-left',
        'text-align-right',
    ];

    /**
     * @var array<int, string>
     */
    private const TEXT_BLOCK_TAGS = [
        'blockquote',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'li',
        'p',
    ];

    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);

        if ($html === '') {
            return null;
        }

        if (! class_exists(DOMDocument::class)) {
            return self::escapeHtml($html);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (! $loaded) {
            return self::escapeHtml($html);
        }

        /** @var DOMElement|null $root */
        $root = $document->documentElement;

        if (! $root) {
            return null;
        }

        self::sanitizeNode($root);

        $sanitized = '';

        foreach ($root->childNodes as $childNode) {
            $sanitized .= $document->saveHTML($childNode);
        }

        return trim($sanitized) !== '' ? trim($sanitized) : null;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if (! $child) {
                continue;
            }

            if ($child instanceof DOMElement) {
                self::sanitizeElement($child);
                continue;
            }

            self::sanitizeNode($child);
        }
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            self::sanitizeNode($element);
            self::unwrap($element);

            return;
        }

        self::sanitizeAttributes($element, $tag);
        self::sanitizeNode($element);
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $href = $tag === 'a' ? trim((string) $element->getAttribute('href')) : '';
        $src = $tag === 'img' ? trim((string) $element->getAttribute('src')) : '';
        $alt = $tag === 'img' ? trim((string) $element->getAttribute('alt')) : '';
        $dir = in_array($tag, self::TEXT_BLOCK_TAGS, true)
            ? self::sanitizeDirection((string) $element->getAttribute('dir'))
            : null;
        $classes = in_array($tag, ['figure', 'img'], true)
            ? self::sanitizeClasses((string) $element->getAttribute('class'), self::ALLOWED_MEDIA_CLASSES)
            : (in_array($tag, self::TEXT_BLOCK_TAGS, true)
                ? self::sanitizeClasses((string) $element->getAttribute('class'), self::ALLOWED_TEXT_CLASSES)
                : []);

        $attributesToRemove = [];

        foreach ($element->attributes as $attribute) {
            $attributesToRemove[] = $attribute->name;
        }

        foreach ($attributesToRemove as $attributeName) {
            $element->removeAttribute($attributeName);
        }

        if (in_array($tag, self::TEXT_BLOCK_TAGS, true)) {
            if ($dir !== null) {
                $element->setAttribute('dir', $dir);
            }

            if ($classes !== []) {
                $element->setAttribute('class', implode(' ', $classes));
            }

            return;
        }

        if ($tag === 'img') {
            if ($src === '' || ! self::isSafeImageUrl($src)) {
                $element->parentNode?->removeChild($element);

                return;
            }

            $element->setAttribute('src', $src);
            $element->setAttribute('alt', $alt);

            if ($classes !== []) {
                $element->setAttribute('class', implode(' ', $classes));
            }

            return;
        }

        if ($tag === 'figure') {
            if ($classes !== []) {
                $element->setAttribute('class', implode(' ', $classes));
            }

            return;
        }

        if ($tag !== 'a') {
            return;
        }

        if ($href === '' || ! self::isSafeUrl($href)) {
            $element->removeAttribute('href');

            return;
        }

        $element->setAttribute('href', $href);

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $lower = strtolower($url);

        if (str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($lower, 'javascript:')
            || str_starts_with($lower, 'data:')
            || str_starts_with($lower, 'vbscript:')
            || str_starts_with($lower, 'file:')
        ) {
            return false;
        }

        if (str_starts_with($lower, 'http://')
            || str_starts_with($lower, 'https://')
            || str_starts_with($lower, 'mailto:')
            || str_starts_with($lower, 'tel:')
            || str_starts_with($url, '/')
            || str_starts_with($url, '#')
        ) {
            return true;
        }

        return ! preg_match('/^[a-z][a-z0-9+\-.]*:/i', $url);
    }

    private static function isSafeImageUrl(string $url): bool
    {
        return str_starts_with($url, '/lesson-media/');
    }

    /**
     * @param array<int, string> $allowedClasses
     * @return array<int, string>
     */
    private static function sanitizeClasses(string $classList, array $allowedClasses): array
    {
        $tokens = preg_split('/\s+/', trim($classList)) ?: [];
        $safe = [];

        foreach ($tokens as $token) {
            if ($token !== '' && in_array($token, $allowedClasses, true) && ! in_array($token, $safe, true)) {
                $safe[] = $token;
            }
        }

        return $safe;
    }

    private static function sanitizeDirection(string $direction): ?string
    {
        return in_array($direction, ['rtl', 'ltr', 'auto'], true) ? $direction : null;
    }

    private static function escapeHtml(string $html): ?string
    {
        $escaped = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return trim($escaped) !== '' ? $escaped : null;
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
