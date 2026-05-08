<?php

declare(strict_types=1);

namespace Baconfy\Prompt\FrontMatter;

use Symfony\Component\Yaml\Yaml;

final class Parser
{
    private const string PATTERN = '/\A---\R(?<yaml>(?:.*?\R)?)---(?:\R(?<content>.*))?\z/s';

    /**
     * Parses the given raw string and extracts front matter and content.
     *
     * @param  string  $raw  The raw input string containing potential front matter and content.
     * @return ParsedFrontMatter The parsed front matter and content.
     */
    public function parse(string $raw): ParsedFrontMatter
    {
        if (preg_match(self::PATTERN, $raw, $matches) !== 1) {
            return new ParsedFrontMatter([], $raw);
        }

        /** @var array<string, mixed>|null $metadata */
        $metadata = Yaml::parse($matches['yaml']);

        return new ParsedFrontMatter(
            metadata: $metadata ?? [],
            content: $matches['content'] ?? '',
        );
    }
}