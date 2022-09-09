# Localization

## Install additional locales for laravel (fortify, nova, etc..)
- install LaravelLang package https://github.com/Laravel-Lang/lang 
```bash
composer require laravel-lang/lang --dev
````
- install a publisher for previous package https://github.com/Laravel-Lang/publisher.

```bash
composer require laravel-lang/publisher laravel-lang/lang laravel-lang/attributes --dev
```
This will automate the process of updating lang files.
- via publisher, install additional locales 
```bash
php artisan lang:add it
```
This will add a {locale}.json files in your locales directory. 
See https://publisher.laravel-lang.com/using/ for all available commands.
