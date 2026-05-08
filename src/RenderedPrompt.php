<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Stringable;

final readonly class RenderedPrompt implements Stringable
{
    /**
     * Initializes a new instance of the class.
     *
     * @param  string  $content  The main content to be stored in the instance.
     * @param  array  $metadata  An optional associative array of metadata related to the content.
     * @return void
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