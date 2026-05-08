<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Models;

use Baconfy\Prompt\Database\Factories\PromptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    /** @use HasFactory<PromptFactory> */
    use HasFactory;

    protected $table = 'prompts';

    /**
     * @var list<string>  Mass-assignable attributes.
     */
    protected $fillable = [
        'name',
        'content',
    ];

    protected static function newFactory(): PromptFactory
    {
        return PromptFactory::new();
    }
}