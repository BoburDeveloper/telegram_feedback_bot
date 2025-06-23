<?php
namespace App\Http\Middleware;

use Closure;

class BasicAuth
{
    public function handle($request, Closure $next)
    {
        $user = env('BASIC_AUTH_USER');
        $pass = env('BASIC_AUTH_PASS');

        if ($request->getUser() !== $user || $request->getPassword() !== $pass and strpos($_SERVER['REQUEST_URI'], 'telegraph')===false) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
