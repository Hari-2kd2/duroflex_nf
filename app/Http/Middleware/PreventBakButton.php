<?php

namespace App\Http\Middleware;

use Closure;

class PreventBakButton
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // $response = $next($request);
        // return $response->header('Cache-Control','nocache,no-store,max-age=0,must-revalidate')
        //     ->header('Pragma','no-cache')
        //     ->header('Expires','Sat,01 Jan 1990 00:00:00 GMT');

        $headers = [
            'Cache-Control' => 'nocache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Sun,02 Jan 1990 00:00:00 GMT',
        ];
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
