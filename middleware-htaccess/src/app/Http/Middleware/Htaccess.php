<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Support\Str;

class Htaccess
{
    
    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {

        // force canonical url (eg. "www")
        if (!app()->environment("local") && $request->getSchemeAndHttpHost() !== config("app.url")) {
            return redirect(config("app.url").$request->getRequestUri());
        }
        
        // force https
        if (!$request->secure() && env("FORCE_HTTPS") == true) {
            return redirect()->secure($request->getRequestUri());
        }

        // hide public folder (on shared hosting)
        if (Str::startsWith($request->getRequestUri(), '/public')) {
            abort(404);
        }
        
        // trim trailing slash 
        if ($request->getRequestUri() !== '/' && Str::endsWith($request->getRequestUri(), '/')) {
            return redirect(rtrim($request->getRequestUri(), "/"));
        }

        // redirect to localized folder
        $uric = parse_url($request->getRequestUri());
        if($uric["path"] == '/') {
            
            $qst = !empty($uric["query"]) ? $uric["query"] : '';
            $qst = $qst ? '?'.$qst : '';
        
            $LOCALES = [
                "it_IT" => 'it'
                ,"en_EN" => 'en'
            ];
            
            $userLangs = preg_split('/,|;/', $request->getPreferredLanguage());
            foreach ($userLangs as $locale) {
                if (array_key_exists($locale, $LOCALES)) {
                    return redirect()->to($LOCALES[$locale].$qst);
                }
            }
        
            // default
            return redirect()->to("/it".$qst);

        }
        

        return $next($request);
    }
}