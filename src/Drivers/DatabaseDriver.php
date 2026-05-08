<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Drivers;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Database\Connection;

final readonly class DatabaseDriver implements Driver
{
    /**
     * Constructor method to initialize the class with required dependencies.
     *
     * @param  Connection  $connection  The database connection instance.
     * @param  Parser  $parser  The parser instance for processing data.
     * @param  string  $table  The name of the database table to be used.
     *
     * @return void
     */
    public function __construct(private Connection $connection, private Parser $parser, private string $table) {}

    /**
     * Finds and parses front matter for the given name.
     *
     * @param  string  $name  The name of the entry to find.
     * @return ParsedFrontMatter|null Returns the parsed front matter object if found, or null if not found.
     */
    public function find(string $name): ?ParsedFrontMatter
    {
        /** @var object{content: string}|null $row */
        $row = $this->connection->table($this->table)->where('name', $name)->first();

        if ($row === null) {
            return null;
        }

        return $this->parser->parse($row->content);
    }

    public function all(): array
    {
        /** @var list<string> */
        return $this->connection->table($this->table)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}