# Uso e customizzazione di Fortify

Installare Fortify con `composer require laravel/fortify`.   
Pubblicare i file di Fortify con `php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"  `.   
Registrare il provider FortifyServiceProvider appena pubblicato in config/app.php.  
Eseguire `php artisan migrate`.
Agire su questo provider per la personalizzazione.

# 1. Login

## 1.1 Login view
- Per impostare la view da usare per renderizzare il form di login:
```php
Fortify::loginView(function () {
    return view('account.login');
});
```
Questa view deve contenere il form di login.

- Per esigenze particolari, è possibile agire registrando un responso custom (questo secondo approccio è alternativo al primo, uno esclude l'altro):
```php
use Laravel\Fortify\Contracts\LoginViewResponse;

$this->app->instance(LoginViewResponse::class, new class () implements LoginViewResponse {
    public function toResponse($request)
    {
        
        // codice custom...
    
        return view(...);
    }
});
```

## 1.2 Submit login
La route di submit del form di login è POST 'login', gestita da `Laravel\Fortify › AuthenticatedSessionController@store`.
L'autenticazione è personalizzabile attraverso l'impostazione di un callback dedicato:
```php
Fortify::authenticateUsing(function (Request $request) {
        // codice di autenticazione...
        $user = User::where('email', $request->email)->first();
 
        if ($user &&
            Hash::check($request->password, $user->password)) {
            return $user;
        }
    });
```

## 1.3 Redirect dopo login
Registrare un binding sull'interfaccia usata dal controller vendor che gestisce il login.
```php
$this->app->instance(LoginResponse::class, new class () implements LoginResponse {
    public function toResponse($request)
    {
        return redirect()->intended(route('user'));
    }
});
```
# 2. Registrazione

## 2.1 Custom view per la pagina di registrazione
```php
Fortify::registerView(function () {
    return view('account.register');
});
```
Questa vista deve contenere il form di registrazione, che va puntato alla route POST 'register' .

## 2.2 Elaborazione della registrazione

- la registrazione viene gestita da  `/vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php`, che utilizza la action `Actions\Fortify\CreateNewUser`(viene pubblicata all'installazione di Fortify).   
Intervenire su questa action per personalizzare validazione e creazione dell'utente.      
Nel caso questo non sia sufficiente, procedere oltre.

- per personalizzare responso DOPO invio del form di registrazione (ed esecuzione della action sopra indicata), registrare un custom response corrispondente. Questo va a sovrascrivere il binding sull'interfaccia viene utilizzata dal controller di Fortify per eseguire il redirect.
Il controller che gestisce la route di registrazione è  `Laravel\Fortify\Http\Controllers\RegisteredUserController`
```php
use Laravel\Fortify\Contracts\RegisterResponse;

$this->app->instance(RegisterResponse::class, new class implements RegisterResponse
{
    public function toResponse($request)
    {
        // annulla autologin effettuato da controller di default, se serve (v. sotto 3.1)
        $this->guard->logout($request->user());
        
        return redirect(route({my_custom_route}));
    }
});
```

- per sovrascrivere in toto la gestione del submit della registrazione, sovrascrivere la route che la gestisce

## 2.3 Invio mail registrazione

### 2.3.1 invio notifica di registrazione di default (attivazione email)
- user deve implementare mustverifyemail e la feature deve essere abilitata in fortify
- la route di submit della registrazione è Laravel\Fortify\Http\Controllers\RegisteredUserController@store che emette evento Registered
- l'invio viene eseguito con /Illuminate/Auth/Listeners/SendEmailVerificationNotification.php (nel core di laravel) registrato come listener
- la mail che viene inviata è vendor/laravel/framework/src/Illuminate/Auth/Notifications/VerifyEmail.php

### 2.3.2 invio custom

#### opz. A
- registrare listener di evento su evento Registered (normalmente registrato a SendEmailVerificationNotification, v. sopra) e fare ciò che si vuole  nel listener stesso

#### opz. B
- user deve implementare mustverifyemail e la feature deve essere abilitata in fortify
- sovrascrivere nella classe User il metodo sendEmailVerificationNotification() fornito dal trait Illuminate/Auth/MustVerifyEmail.php
- con fortify, per customizzare redirect dopo che utente ha cliccato sul link di verifica email impostarlo nella chiave di cofnigurazione `fortify.redirects.email-verification`

#### opz. C (personalizzazione email e link di attivazione)

- user deve implementare mustverifyemail e la feature deve essere abilitata in fortify

- per customizzare creazione della mail, impostare un callback di creazione mail per la notifica di default vendor/laravel/framework/src/Illuminate/Auth/Notifications/VerifyEmail.php in FortifyServiceProvider
```php
use Illuminate\Auth\Notifications\VerifyEmail;

VerifyEmail::toMailUsing(function ($notifiable, $verificationUrl) {
    // ...codice custom...
    // è possibile creare una mail da una classe standard
});
```

- per modificare il link di verifica contenuto nella mail di conferma, registrare un callback per la creazione dell'url.
Assicurarsi nel controller che gestira questa url, di eseguire le istruzioni per impostare la mail dell'utente come verificata
(v. `Laravel\Fortify › VerifyEmailController`). 
In questo modo è possibile registrare un url di verifica che non sia protetta dal middleware auth (v. "Personalizzazione procedura di registrazione")

```php
use Illuminate\Auth\Notifications\VerifyEmail;

VerifyEmail::createUrlUsing(function ($notifiable, $verificationUrl) {
    // ...codice custom...
    
});
```
# 3. Verifica della mail

## 3.1 Route di verifica della mail

- per modificare il redirect / il responso DOPO che utente ha cliccato sul link di verifica email, registrare un custom response corrispondente
La route di verifica email è gestita da  `Laravel\Fortify › VerifyEmailController`.
In questo caso la procedura di verifica email sarà gestita automaticamente.
Attenzione che la route di verifica email è protetta dal middleware auth: dopo la registrazione l'utente è automaticamente loggato ma non è detto che apra il link di verifica sul medesimo browser.
```php
use Laravel\Fortify\Contracts\VerifyEmailResponse;

$this->app->instance(VerifyEmailResponse::class, new class implements VerifyEmailResponse
{
    public function toResponse($request)
    {   
        // imposta un redirect custom...
        return redirect(route({my_custom_route}));

        // .. o imposta una vista custom
        return view({my_custom_view});

    }
});
```

- se è stato modificato il link di attivazione contenuto nella mail (v. invio custom, opz. c), assicurarsi di impostare un controller che gestisca l'url (e che proceda alla corretta attivazione della mail qualora sia necessario). Personalizzando il link di attivazione e registrando un controller custom è possibile, ad es, mostrare all'utente la pagina di impostazione password, per permettergli di fare il login (in questo caso andrà disabilitato anche l'autologin dopo l'invio del form di registrazione)

# 4. Reset della password

## 4.1 pagina di richiesta password reset, impostare una view che renderizzi il form:

Vedi https://laravel.com/docs/9.x/fortify#requesting-a-password-reset-link

Pagina con form di inserimento mail, la route è GET password.request, gestita da vendor/laravel/fortify/src/Http/Controllers/PasswordResetLinkController.php

Il form deve fare un POST alla route "password.email"

```php
Fortify::requestPasswordResetLinkView({custom_view});
```

La vista viene usata anche per renderizzare la risposta.
In caso di risposta, sarà presente la chiave status in sessione.
```php
@if(session('status'))
<div class="uk-alert uk-alert-success">{{ session('status') }}</div>
@else
    // mostra il form,...
```

## 4.2 elaborazione richiesta password reset, invio link di reset password

Vedi https://laravel.com/docs/9.x/fortify#handling-the-password-reset-link-request-response

La route è  POST password.email, gestita da vendor/laravel/fortify/src/Http/Controllers/PasswordResetLinkController.php.

Attraverso il password broker, chiama sull'utente interessato il metodo sendPasswordResetNotification().

Dopo l'elaborazione, si viene reindirizzati nuovamente alla route GET password.request (la vista dovrà quindi mostrare un messaggio di conferma) con la chiave di sessione status contenente il messaggio di conferma.

## 4.3 personalizzazione della mail
Ci sono due possibilità:
- sovrascrivere il metodo sendPasswordResetNotification sul modello User

- customizzare la creazione della mail impostando un callback sulla notifica che viene inviata
Se si vuole usare la route di default di laravel per l'inserimento della nuova password, inserirla nella mail.
```php
use Illuminate\Auth\Notifications\ResetPassword;

ResetPassword::toMailUsing(function($user, string $token) {
    
    // custom mail..
    $mail = new MailResetPassword([
        // link creato secondo la logica di default
        'reset_link' => url(route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ], false))
    ], $user);
    
});
```

## 4.4 pagina di inserimento nuova password

Vedi https://laravel.com/docs/9.x/fortify#resetting-the-password 

Pagina con form di inserimento e conferma nuova password, dopo che utente ha cliccato sul link di reset ricevuto via mail.
La route è GET  password.reset (a meno che non sia stata modificata nella mail).

- impostare custom view con il metodo dedicato.
Nel form va inserita la mail e il token dell'url, per permettere la corretta elaborazine della richiesta.
Il form deve fare un POST alla route "password.update".

```php
Fortify::resetPasswordView(function ($request) {
    return view('account.reset_password_form_2', [
        'email' => $request->email,
        'token' => $request->token,
    ]);
});
```

La vista viene usata anche per renderizzare la risposta.
In caso di risposta, sarà presente la chiave status in sessione.
```php
@if(session('status'))
<div class="uk-alert uk-alert-success">{{ session('status') }}</div>
@else
    // mostra il form,...
```

- in alternativa è possibile customizzare in toto il responso facendone un binding
Valgono le stesse considerazioni sul form fatte prima.
```php
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
$this->app->instance(ResetPasswordViewResponse::class, new class implements ResetPasswordViewResponse
{
    public function toResponse($request)
    {   
        // imposta un redirect custom...
        return redirect(route({my_custom_route}));

        // .. o imposta una vista custom
        return view({my_custom_view});

    }
});
```

## 4.5 elaborazione inserimento nuova password
Vedi https://laravel.com/docs/9.x/fortify#customizing-password-resets

La route è post password.update, gestita da  `Laravel\Fortify › NewPasswordController@store.`
Viene validato anche il token della richiesta.

Se c'è qualche errore, il controller rimanda alla pagina password.reset (quella del form) inserendo l'errore nella chiave 'email' (quindi controllare con `@error('email)` )

Dopo che il controller ha validato la richiesta,

- chiama la classe Actions/Fortify/ResetUserPassword per l'aggiornamento.
Intervenire su questa classe se necessario.

- costruisce un responso sulla base del binding eseguito sull'interfaccia `Laravel\Fortify\Contracts\PasswordResetResponse`
Di default si viene reindirizzati alla route GET 'login' con la chiave di sessione 'status', che si può quindi utilizzare per visualizzare (nella pagina di login) il messaggio di avvenuto aggiornamento password, anzichè il testo di default.

Se si vuole un comportamento custom:

```php
use Laravel\Fortify\Contracts\PasswordResetResponse;

$this->app->instance(PasswordResetResponse::class, new class implements PasswordResetResponse
{
    public function toResponse($request)
    {   
        // esegue operazioni...

        // imposta una vista custom
        return view({my_custom_view});

    }
});
```

# 5. Varie

## 5.1 Validazione della password
- fortify mette a disposizione una custom rule su cui agire per impostare i requisiti della password. E' possibile usarla onvunque.
```php
use Laravel\Fortify\Rules\Password;

$request->validate([
    'password' => [
        'required',
        'confirmed',
        (new Password)->length(10)->requireUppercase()->requireNumeric()->requireSpecialCharacter()
    ],
])
```
