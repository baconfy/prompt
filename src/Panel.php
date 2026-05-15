<?php

declare(strict_types=1);

namespace Baconfy\Prompt;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

final class Panel
{
    /**
     * Callback resolving whether the given (possibly null) user may access the panel.
     */
    private static ?Closure $authCallback = null;

    /**
     * Register (or clear with null) the callback used to authorize panel access.
     */
    public static function auth(?Closure $callback): void
    {
        self::$authCallback = $callback;
    }

    /**
     * Resolve whether the user may access the panel. The registered callback wins;
     * if no callback is set, fall back to the configured Gate ability.
     */
    public static function check(?Authenticatable $user): bool
    {
        if (self::$authCallback !== null) {
            return (bool) (self::$authCallback)($user);
        }

        /** @var string $ability */
        $ability = config('prompt.panel.gate', 'viewPrompts');

        return Gate::forUser($user)->check($ability);
    }
}
