<?php

namespace App\Service;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final class MarkdownSanitizer
{
    public function __construct(
        private HtmlSanitizerInterface $san
    ) {}

    public function toSafeHtml(string $markdown): string
    {
        $md = new CommonMarkConverter();
        $html = $md->convert($markdown)->getContent();

        return $this->san->sanitize($html, [
            'allow_safe_elements' => true,
            'allowed_tags' => [
                'p','h1','h2','h3','h4','em','strong','u','del','code','pre',
                'ul','ol','li','blockquote','a','img','span','div','br',
                'table','thead','tbody','tr','th','td',
                'math','mi','mn','mo','msup','msub','mrow'
            ],
            'allowed_attributes' => [
                'a.href','a.title','a.target','a.rel',
                'img.src','img.alt','img.title','img.width','img.height',
                '*.class','*.style','math.xmlns'
            ],
        ]);
    }
}
