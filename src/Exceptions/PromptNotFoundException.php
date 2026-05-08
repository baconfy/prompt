<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Exceptions;

use RuntimeException;

final class PromptNotFoundException extends RuntimeException
{
    /**
     * Constructor method.
     *
     * @param  string  $name  The name associated with the prompt.
     * @return void
     */
    public function __construct(public readonly string $name)
    {
        parent::__construct(sprintf('Prompt [%s] not found.', $name));
    }
}