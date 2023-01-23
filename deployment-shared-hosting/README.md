
# Laravel deployment on shared hosting
Todo list to deploy laravel on shared hosting environment, if you cannot change default document root for a domain. Tested on Laravel 5.8

### move files in private folder
- create a directory named `private` in your web server document root and move all laravel source files inside it, except the `public` folder

### edit front controller
- edit `public/index.php` to require `vendor` and `app` folders from the new source directory
```
require __DIR__.'/../private/vendor/autoload.php';
  
$app = require_once __DIR__.'/../private/bootstrap/app.php';
```
 - set the new public path for Laravel in the "Turn On The Lights" section in `public/index.php`
 ```
// set the public path to this directory
$app->bind('path.public', function() {
	return __DIR__;
});
 ```
 ### .htaccess in the document root folder
- create .htaccess file in root folder

```
RewriteEngine On

# this will hide the "private" folder (as it will be on your web server public document root)
RewriteRule ^(private)$ - [R=404]

# avoid redirect for matching directory in /public (adapt to your needs)
RewriteRule ^(imgs|css|js|fonts)$ - [F]

# forwards requests to `/public` subfolder
RewriteCond %{REQUEST_URI} !public/
RewriteRule (.*) /public/$1 [L]
```
### .htaccess in public folder
- edit .htaccess file in `/public` directory
  This is the htaccess file coming with laravel's default installation package.
```
# comment these lines to avoid 'public' in url (after redirect) for matching directory
#RewriteCond %{REQUEST_FILENAME} !-d
#RewriteCond %{REQUEST_URI} (.+)/$
#RewriteRule ^ %1 [L,R=301]
```

### emulate htaccess behaviour through a middleware
- es `App\Http\Middleware\Htaccess`
```
// hide public folder from direct requests
if (Str::startsWith($request->getRequestUri(), '/public')) {
	abort(404);
}
// remove trailing slashes
if ($request->getRequestUri() !== '/' && Str::endsWith($request->getRequestUri(), '/')) {
	return redirect(rtrim($request->getRequestUri(), "/"));
}
```

### update artisan
- `/private/artisan` 
```
// set the public path 
$app->bind('path.public', function() {
    return __DIR__.'/../public';
});
```
before
```
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
``` 