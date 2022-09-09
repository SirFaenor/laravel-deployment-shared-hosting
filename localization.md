# Localization

## Install additional locales for laravel (fortify, nova, etc..)
- install LaravelLang package https://github.com/Laravel-Lang/lang
- install a publisher for previous package https://github.com/Laravel-Lang/publisher. This will automate updating lang files.
- via publisher, install additional locales `php artisan lang:add it`. This will add a {locale}.json files in your locales directory. 
