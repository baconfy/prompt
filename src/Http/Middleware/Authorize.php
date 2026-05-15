<?php

declare(strict_types=1);

namespace Baconfy\Prompt\Http\Middleware;

use Baconfy\Prompt\Panel;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class Authorize
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Authenticatable|null $user */
        $user = $request->user();

        abort_unless(Panel::check($user), 403);

        return $next($request);
    }
}
