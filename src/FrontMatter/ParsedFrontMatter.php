<?php

declare(strict_types=1);

namespace Baconfy\Prompt\FrontMatter;

final readonly class ParsedFrontMatter
{
    /**
     * Constructor method.
     *
     * @param  array<string, mixed>  $metadata  An array containing metadata information.
     * @param  string  $content  The content to be managed by the class.
     * @return void
     */
    public function __construct(public array $metadata, public string $content) {}
}
