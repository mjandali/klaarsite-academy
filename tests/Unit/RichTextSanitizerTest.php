<?php

namespace Tests\Unit;

use App\Support\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_it_removes_unsafe_tags_and_attributes(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<p onclick="alert(1)" style="color:red">Safe<img src="x" onerror="alert(1)"><script>alert(1)</script><iframe src="https://example.com"></iframe></p>'
        );

        $this->assertSame('<p>Safe</p>', $sanitized);
    }

    public function test_it_rejects_unsafe_and_protocol_relative_links(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<p><a href="javascript:alert(1)">bad</a><a href="data:text/plain;base64,SGk=">data</a><a href="//example.com">protocol</a><a href="https://example.com" onclick="evil()">good</a></p>'
        );

        $this->assertStringContainsString('<a>bad</a>', $sanitized);
        $this->assertStringContainsString('<a>data</a>', $sanitized);
        $this->assertStringContainsString('<a>protocol</a>', $sanitized);
        $this->assertStringContainsString('href="https://example.com"', $sanitized);
        $this->assertStringContainsString('target="_blank"', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringNotContainsString('data:text', $sanitized);
        $this->assertStringNotContainsString('href="//example.com"', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
    }

    public function test_it_preserves_safe_code_blocks(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<pre style="background:black"><code class="language-php">$value = 1;</code></pre>'
        );

        $this->assertSame('<pre><code>$value = 1;</code></pre>', $sanitized);
    }

    public function test_it_preserves_safe_tiptap_html(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<h2>Overview</h2><p><strong>Important</strong> <em>details</em></p><ul><li>First item</li><li>Second item</li></ul><blockquote>Keep this in mind.</blockquote><figure class="media-center media-w-50"><img src="/lesson-media/12" alt="Diagram"><figcaption>Architecture diagram</figcaption></figure><pre><code>const answer = 42;</code></pre>'
        );

        $this->assertStringContainsString('<h2>Overview</h2>', $sanitized);
        $this->assertStringContainsString('<strong>Important</strong>', $sanitized);
        $this->assertStringContainsString('<em>details</em>', $sanitized);
        $this->assertStringContainsString('<ul><li>First item</li><li>Second item</li></ul>', $sanitized);
        $this->assertStringContainsString('<blockquote>Keep this in mind.</blockquote>', $sanitized);
        $this->assertStringContainsString('<figure class="media-center media-w-50">', $sanitized);
        $this->assertStringContainsString('<figcaption>Architecture diagram</figcaption>', $sanitized);
        $this->assertStringContainsString('<pre><code>const answer = 42;</code></pre>', $sanitized);
    }

    public function test_it_removes_unsafe_external_images(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<p>Intro</p><img src="https://example.com/image.png" alt="External"><img src="//cdn.example.com/image.png" alt="Protocol relative"><img src="data:image/png;base64,AAAA" alt="Inline">'
        );

        $this->assertStringContainsString('<p>Intro</p>', $sanitized);
        $this->assertStringNotContainsString('<img', $sanitized);
        $this->assertStringNotContainsString('example.com', $sanitized);
        $this->assertStringNotContainsString('data:image', $sanitized);
    }

    public function test_it_keeps_only_allowed_internal_media_layout_classes(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<figure class="media-start media-w-33 media-wrap arbitrary-class"><img class="media-center media-w-33 media-wrap nope" src="/lesson-media/42" alt="Diagram"></figure>'
        );

        $this->assertStringContainsString('<figure class="media-start media-w-33 media-wrap">', $sanitized);
        $this->assertStringContainsString('<img src="/lesson-media/42" alt="Diagram" class="media-center media-w-33 media-wrap">', $sanitized);
        $this->assertStringNotContainsString('arbitrary-class', $sanitized);
        $this->assertStringNotContainsString('nope', $sanitized);
    }

    public function test_it_allows_internal_lesson_media_images_and_figures(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<figure class="media-center media-w-50 arbitrary-class" style="padding:1rem"><img class="media-w-25 evil-class" src="/lesson-media/42" alt="Diagram" onerror="alert(1)" srcset="/lesson-media/99 2x"><figcaption onclick="evil()">Example caption</figcaption></figure>'
        );

        $this->assertStringContainsString('<figure class="media-center media-w-50">', $sanitized);
        $this->assertStringContainsString('<img src="/lesson-media/42" alt="Diagram" class="media-w-25">', $sanitized);
        $this->assertStringContainsString('<figcaption>Example caption</figcaption>', $sanitized);
        $this->assertStringNotContainsString('style=', $sanitized);
        $this->assertStringNotContainsString('onerror=', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('srcset=', $sanitized);
        $this->assertStringNotContainsString('arbitrary-class', $sanitized);
        $this->assertStringNotContainsString('evil-class', $sanitized);
    }
    public function test_it_preserves_safe_text_direction_and_alignment_attributes(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<p dir="ltr" class="text-align-left random" style="color:red">English paragraph</p><h2 dir="rtl" class="text-align-center bad">عنوان</h2><p dir="evil" class="unknown">Bad</p>'
        );

        $this->assertStringContainsString('<p dir="ltr" class="text-align-left">English paragraph</p>', $sanitized);
        $this->assertStringContainsString('<h2 dir="rtl" class="text-align-center">عنوان</h2>', $sanitized);
        $this->assertStringContainsString('<p>Bad</p>', $sanitized);
        $this->assertStringNotContainsString('style=', $sanitized);
        $this->assertStringNotContainsString('random', $sanitized);
        $this->assertStringNotContainsString('dir="evil"', $sanitized);
    }

}
