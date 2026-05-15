<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Models;

use Baconfy\Prompt\Database\Factories\PromptFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $root_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static self create(array<string, mixed> $attributes = [])
 * @method static Builder<self> query()
 * @method static Builder<self> where(string|array<string, mixed> $column, mixed $operator = null, mixed $value = null)
 */
class Prompt extends Model
{
    /** @use HasFactory<PromptFactory> */
    use HasFactory;

    protected $table = 'prompts';

    /**
     * @var list<string> Mass-assignable attributes.
     */
    protected $fillable = [
        'name',
        'root_id',
        'content',
    ];

    protected static function newFactory(): PromptFactory
    {
        return PromptFactory::new();
    }

    /**
     * The root (first) version of this prompt. Null when this row is itself the root.
     *
     * @return BelongsTo<self, $this>
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id');
    }

    /**
     * Subsequent versions branching from this root.
     *
     * @return HasMany<self, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'root_id');
    }

    /**
     * Scope query to the latest (highest-id) rows for the given name.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLatestForName(Builder $query, string $name): Builder
    {
        $query->where('name', $name)->orderBy('id', 'desc');

        return $query;
    }
}
