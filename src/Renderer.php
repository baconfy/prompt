<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\Validators\RequiredVariablesValidator;
use Illuminate\Support\Facades\Blade;

final readonly class Renderer
{
    /**
     * Constructor method.
     *
     * @param  RequiredVariablesValidator  $validator  An instance of RequiredVariablesValidator used for validation purposes.
     * @return void
     */
    public function __construct(private RequiredVariablesValidator $validator) {}

    /**
     * Renders the content and metadata into a RenderedPrompt object.
     *
     * @param  ParsedFrontMatter  $source  An object containing the front matter metadata and content to be rendered.
     * @param  array<string, mixed>  $data  Variables provided to render the prompt.
     * @return RenderedPrompt The result of rendering the content and metadata into a prompt.
     */
    public function render(ParsedFrontMatter $source, array $data = []): RenderedPrompt
    {
        $this->validator->validate($source->metadata, $data);

        return new RenderedPrompt(
            content: Blade::render($source->content, $data),
            metadata: $source->metadata,
        );
    }
}
