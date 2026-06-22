<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckUserSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->suspended_until && $user->suspended_until->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dalam status suspend hingga '.$user->suspended_until->translatedFormat('d F Y H:i').'.',
            ], 403);
        }

        return $next($request);
    }
}
