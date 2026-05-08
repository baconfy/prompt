<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Exceptions;

use RuntimeException;

final class MissingRequiredVariablesException extends RuntimeException
{
    /**
     * Constructor for the class.
     *
     * @param  array<int, string>  $variables  List of missing required variable names.
     * @return void
     */
    public function __construct(public readonly array $variables)
    {
        parent::__construct(sprintf('The prompt is missing required variables: %s', implode(', ', $variables)));
    }
}