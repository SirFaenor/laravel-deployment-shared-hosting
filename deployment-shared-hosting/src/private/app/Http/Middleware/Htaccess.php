<?php
namespace App\Http\Middleware;
 
use Auth;
use Closure;
use Illuminate\Support\Str;

class Htaccess
{
    
    /**
    * Elaborazione iniziale delle richieste
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {

        if (!app()->environment("local") && !Str::startsWith($request->getHttpHost(), 'www.')) {
            return redirect(config("app.url"));
        }

        if (!$request->secure() && env("FORCE_HTTPS") == true) {
            return redirect()->secure($request->getRequestUri());
        }

        // forbidden cartella public per chiamata diretta
        if (Str::startsWith($request->getRequestUri(), '/public')) {
            abort(404);
        }
        
        // rimuovo trailing slash da url sostituendo manulamente la regola nell'htaccess di 
        // laravel per evitare che questa aggiunga "public" all'url (v. punto precedente)
        if ($request->getRequestUri() !== '/' && Str::endsWith($request->getRequestUri(), '/')) {
            return redirect(rtrim($request->getRequestUri(), "/"));
        }

        return $next($request);
    }
}