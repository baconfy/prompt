<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Database\Factories;

use Baconfy\Prompt\Models\Prompt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prompt>
 */
final class PromptFactory extends Factory
{
    protected $model = Prompt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(),
            'content' => "---\nmodel: claude-opus-4-5\n---\n".$this->faker->paragraph(),
        ];
    }
}
