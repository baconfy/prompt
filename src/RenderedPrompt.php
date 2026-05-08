<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Stringable;

final readonly class RenderedPrompt implements Stringable
{
    /**
     * @param  array<string, mixed>  $metadata  Front matter metadata carried over from the source prompt.
     */
    public function __construct(public string $content, public array $metadata = []) {}

    /**
     * Converts the object to its string representation.
     *
     * @return string The string representation of the object.
     */
    public function __toString(): string
    {
        return $this->content;
    }
}