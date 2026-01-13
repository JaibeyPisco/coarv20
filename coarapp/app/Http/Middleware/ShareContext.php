<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AppContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShareContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        AppContext::boot($request->user());

        return tap($next($request), static function (): void {
            AppContext::forget();
        });
    }
}
