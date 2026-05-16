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

    public function test_it_allows_internal_lesson_media_images_and_figures(): void
    {
        $sanitized = RichTextSanitizer::sanitize(
            '<figure style="padding:1rem"><img src="/lesson-media/42" alt="Diagram" onerror="alert(1)" srcset="/lesson-media/99 2x"><figcaption onclick="evil()">Example caption</figcaption></figure>'
        );

        $this->assertStringContainsString('<figure>', $sanitized);
        $this->assertStringContainsString('<img src="/lesson-media/42" alt="Diagram">', $sanitized);
        $this->assertStringContainsString('<figcaption>Example caption</figcaption>', $sanitized);
        $this->assertStringNotContainsString('style=', $sanitized);
        $this->assertStringNotContainsString('onerror=', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('srcset=', $sanitized);
    }
}
