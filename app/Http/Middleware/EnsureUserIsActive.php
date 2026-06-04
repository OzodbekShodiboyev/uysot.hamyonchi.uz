<?php
// app/Http/Middleware/EnsureUserIsActive.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['email' => 'Hisobingiz admin tomonidan bloklangan.']);
        }
        return $next($request);
    }
}
