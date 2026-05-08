<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Drivers;

use Baconfy\Prompt\Contracts\Driver;
use Baconfy\Prompt\FrontMatter\ParsedFrontMatter;
use Baconfy\Prompt\FrontMatter\Parser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

final readonly class FileDriver implements Driver
{
    /**
     * Constructor method for the class.
     *
     * @param  Filesystem  $files  An instance of the Filesystem class used for file operations.
     * @param  Parser  $parser  An instance of the Parser class used for parsing operations.
     * @param  string  $folder  The folder path to be used by the class.
     *
     * @return void
     */
    public function __construct(private Filesystem $files, private Parser $parser, private string $folder) {}

    /**
     * Finds and parses front matter for the specified file name.
     *
     * @param  string  $name  The name of the file to find and parse.
     * @return ParsedFrontMatter|null Returns the parsed front matter if the file exists, or null if it does not exist.
     * @throws FileNotFoundException
     */
    public function find(string $name): ?ParsedFrontMatter
    {
        $path = $this->pathFor($name);

        if (! $this->files->exists($path)) {
            return null;
        }

        return $this->parser->parse($this->files->get($path));
    }

    /** @return list<string> */
    public function all(): array
    {
        if (! $this->files->isDirectory($this->folder)) {
            return [];
        }

        $names = [];

        foreach ($this->files->allFiles($this->folder) as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $names[] = str_replace('/', '.', substr($relative, 0, -3));
        }

        sort($names);

        return $names;
    }

    /**
     * Generates the file system path for the specified file name.
     *
     * @param  string  $name  The name of the file to generate the path for, using dot notation.
     * @return string  The full path to the file including its extension.
     */
    private function pathFor(string $name): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $name);

        return $this->folder.DIRECTORY_SEPARATOR.$relative.'.md';
    }
}