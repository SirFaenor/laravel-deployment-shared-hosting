# Breeze vs Jetstream
|                         | Breeze                                              | Jetstream                 |
| ----------------------- | --------------------------------------------------- | ------------------------- |
| **Features** |  |  |
| login | :heavy_check_mark: | :heavy_check_mark: |
| registration | :heavy_check_mark: | :heavy_check_mark: |
| password reset | :heavy_check_mark: | :heavy_check_mark: |
| email verification | :heavy_check_mark: | :heavy_check_mark: |
| password confirmation | :heavy_check_mark: | :heavy_check_mark: |
| profile management | :heavy_check_mark: | :heavy_check_mark: |
| 2FA | ❌ | :heavy_check_mark: |
| browser sessions (cross device) | :x: | :heavy_check_mark: |
| teams | :x: | :heavy_check_mark: |
|  |  |  |
| **Backend** |  |  |
| foundation              | custom controllers                                  | Laravel Fortify <sup>1</sup>         |
| behaviour customization | publishes controllers or routes to your application | customization via Fortify and "Actions" classes |
|                         |                                                     |                           |
| **UI** |                                                     |                           |
| default | blade templates + tailwind | blade + livewire + tailwind |
| alternative | react/vue + inertia js | inertia + vue (with laravel routing) + tailwind |
| assets building (default) | vite | vite |
| extra |  | pre-built components ("jetstream-views") available to install: drodown, modal, forms, dialog, .. |
|  |  |  |
| **API** |                                                     |                           |
| auth scaffold | :heavy_check_mark:via [Sanctum](https://laravel.com/docs/9.x/sanctum) ? | :heavy_check_mark: via [Sanctum](https://laravel.com/docs/9.x/sanctum) |

## Notes

1. [Laravel Fortify](https://github.com/laravel/fortify) is a frontend agnostic authentication backend for Laravel
   
     
