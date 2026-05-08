<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $table = 'prompts';

    /**
     * @var list<string>  Mass-assignable attributes.
     */
    protected $fillable = [
        'name',
        'content',
    ];
}