<?php

declare(strict_types=1);

use Baconfy\Prompt\PromptManager;
use Baconfy\Prompt\RenderedPrompt;

if (! function_exists('prompt')) {
    /**
     * Render a prompt by name.
     *
     * @param  array<string, mixed>  $data  Variables provided to render the prompt.
     */
    function prompt(string $name, array $data = []): RenderedPrompt
    {
        /** @var PromptManager $manager */
        $manager = app('prompt');

        return $manager->get($name, $data);
    }
}