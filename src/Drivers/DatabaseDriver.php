<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Drivers;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Illuminate\Database\Connection;

final readonly class DatabaseDriver implements Driver
{
    public function __construct(private Connection $connection, private string $table) {}

    public function find(string $name): ?ParsedFrontMatter
    {
        $row = $this->connection->table($this->table)->where('name', $name)->first();

        if ($row === null) {
            return null;
        }

        return new ParsedFrontMatter(
            metadata: $this->decodeMetadata($row->metadata ?? null),
            content: (string) $row->content,
        );
    }

    /**
     * @return array<string, mixed>  Decoded metadata, or empty array when null.
     */
    private function decodeMetadata(mixed $raw): array
    {
        if ($raw === null) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $raw, true) ?? [];

        return $decoded;
    }
}