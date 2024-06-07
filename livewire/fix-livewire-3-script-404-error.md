This fixes the issue with nginx server, where livewire.js is not found.
Removing the dot forces nginx to follow the route and to not serve it as static file
(as livewire script is actually generated)

```php
Livewire::setScriptRoute(fn($handle) => Route::get('/livewire/livewirejs', $handle));
```