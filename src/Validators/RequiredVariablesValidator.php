<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Validators;

use Baconfy\Prompt\Exceptions\MissingRequiredVariablesException;

final class RequiredVariablesValidator
{
    /**
     * @param  array<string, mixed>  $metadata  Front matter metadata of the prompt.
     * @param  array<string, mixed>  $data  Variables provided to render the prompt.
     *
     * @throws MissingRequiredVariablesException
     */
    public function validate(array $metadata, array $data): void
    {
        $required = $metadata['required'] ?? [];

        if (! is_array($required)) {
            return;
        }

        $missing = [];
        foreach ($required as $key) {
            if (! is_string($key)) {
                continue;
            }
            if (! array_key_exists($key, $data)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new MissingRequiredVariablesException($missing);
        }
    }
}
