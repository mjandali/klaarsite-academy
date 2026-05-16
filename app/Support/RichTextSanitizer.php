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

        if (! $element->hasAttributes()) {
            if ($tag === 'a' && $href !== '' && self::isSafeUrl($href)) {
                $element->setAttribute('href', $href);

                if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
                    $element->setAttribute('target', '_blank');
                    $element->setAttribute('rel', 'noopener noreferrer nofollow');
                }
            }

            if ($tag === 'img' && $src !== '' && self::isSafeImageUrl($src)) {
                $element->setAttribute('src', $src);
                $element->setAttribute('alt', $alt);
            }

            return;
        }

        $attributesToRemove = [];

        foreach ($element->attributes as $attribute) {
            $attributesToRemove[] = $attribute->name;
        }

        foreach ($attributesToRemove as $attributeName) {
            $element->removeAttribute($attributeName);
        }

        if ($tag === 'img') {
            if ($src === '' || ! self::isSafeImageUrl($src)) {
                $element->parentNode?->removeChild($element);

                return;
            }

            $element->setAttribute('src', $src);
            $element->setAttribute('alt', $alt);

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
