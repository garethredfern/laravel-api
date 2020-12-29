<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## Authentication Using Laravel Sanctum & Fortify for an SPA

Previously I wrote about using Laravel Sanctum to build an API for a Vue SPA to consume. [The article](/articles/vuejs-auth-using-laravel-sanctum), was a very basic intro using API tokens and local storage to maintain authentication state. While there’s nothing wrong with that method for testing out an idea, the preferred and more secure method is to use cookies and sessions. In this article we will dive into using Sanctum with Fortify in a Laravel API, consumed by a separate Vue SPA.

The project files for this article can be found on Github:

- [Larvel API](https://github.com/garethredfern/laravel-api)
- [VueJS SPA](https://github.com/garethredfern/laravel-vue)

### Laravel & Package Install

First, set up the Laravel API as you normally would. My preferred option is to use Laravel [Sail](https://laravel.com/docs/8.x/sail), which I have written about [here](/articles/switching-to-laravel-sail). If you choose to run Laravel via Sail, your API will be accessible via http://localhost.

Next install [Sanctum](https://laravel.com/docs/8.x/sanctum#installation) & [Fortify](https://laravel.com/docs/8.x/fortify).

```bash
sail composer require laravel/sanctum

sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

```bash
sail composer require laravel/fortify

sail artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

> Note: I am using the sail command to enable artisan commands to run within the Docker container.

Next add Sanctum's middleware to your api middleware group within your application's app/Http/Kernel.php file:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

Ensure the FortifyServiceProvider class is registered within the providers array of your application's config/app.php file.

```php
/*
 * Application Service Providers...
 */

App\Providers\FortifyServiceProvider::class,
```

Set up a seed for adding a test user, in the DatabaseSeeder.php file add the following:

```php
\App\Models\User::factory(1)->create(
	[
		'name' => 'Luke Skywalker',
		'email' => 'luke@jedi.com',
		'email_verified_at' => null,
	]
);
```

Run the migrations. If you get an error using Sail, checkout my notes [here](/articles/switching-to-laravel-sail#a-couple-of-gotchas):

```bash
sail artisan migrate --seed
```

Don’t forget to add a sender address in the `.env` so that an email can be sent.

```bash
MAIL_FROM_ADDRESS=test@test.com
```

### Setting Up Sanctum

Sanctum needs some specific set up to enable it to work with a separate SPA. First lets add the following in your .env file:

```bash
SANCTUM_STATEFUL_DOMAINS=localhost:8080
SPA_URL=http://localhost:8080
SESSION_DOMAIN=localhost
```

The stateful domain tells Sanctum which domain you are using for the SPA. You can find the full notes and config for this in the config/sanctum.php file. As we are using cookies and sessions for authentication you need to add a session domain. This determines which domain the cookie is available to in your application. Full notes can be found in the config/session.php file and the [official documentation](https://laravel.com/docs/8.x/sanctum#spa-authentication).

Add the following to `app/Http/Kernel`

```php
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

'api' => [
    EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### Setting Up CORS

If you don’t get CORS set up correctly, it can be the cause (pardon the pun) of great frustration. The first thing to remember is that your SPA and API need to be running on the same top-level domain. However, they may be placed on different subdomains. Running locally (using Sail) the API will run on http://localhost and the SPA using the Vue CLI will normally run on http://localhost:8080 (the port may vary but that is OK).

With this in place we just need to add the routes which will be allowed via CORS. Most of the API endpoints will be via `api/*` but Fortify has a number of endpoints you need to add along with the fetching of `'sanctum/csrf-cookie'` add the following in your config/cors.php file:

```php
'paths' => [
  'api/*',
  'login',
  'logout',
  'register',
  'user/password',
  'forgot-password',
  'reset-password',
  'sanctum/csrf-cookie',
  'email/verification-notification',
],
```

While you are in the config/cors.php file set the following:

```php
'supports_credentials' => true,
```

The above ensures you have the `Access-Control-Allow-Credentials` header with a value of `True` set. You can read more about this in the [MDN documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials). We will be passing this header via the SPA but more on that when we move to set it up.

### Setting Up Fortify

Fortify also has a config file (config/fortify.php) which will need some changes. First set the `home` variable to point at the SPA URL, this can be done via the .env variable. This is where the API redirects to during authentication or password reset when the operations are successful and the user is authenticated.

```php
'home' => env('SPA_URL') . '/dashboard',
```

Next switch off using any Laravel views for the authentication features, the SPA is handling all of this.

```php
'views' => false,
```

Finally, turn on the authentication features you would like to use:

```php
'features' => [
  Features::registration(),
  Features::resetPasswords(),
  Features::emailVerification(),
  Features::updateProfileInformation(),
  Features::updatePasswords(),
],
```

### Redirecting If Authenticated

Laravel provides a `RedirectIfAuthenticated` middleware which out of the box will try and redirect you to the home view if you are already authenticated. For the SPA to work you can add the following which will simply send back a 200 success message in a JSON response. We will then handle redirecting to the home page of the SPA using VueJS routing.

```php
foreach ($guards as $guard) {
    if (Auth::guard($guard)->check()) {
      if ($request->expectsJson()) {
        return response()->json(['error' => 'Already authenticated.'], 200);
      }
      return redirect(RouteServiceProvider::HOME);
    }
}
```

### Email Verification

Laravel can handle email verification as it normally would but with one small adjustment to the `Authenticate` middleware. First. Let’s make sure your `App\Models\User` implements the `MustVerifyEmail` contract:

```php
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    // ...
}
```

In the `Authenticate` Middleware change the `redirectTo` method to redirect to the SPA URL rather than a Laravel view:

```php
protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        return url(env('SPA_URL') . '/login');
    }
}
```

With this is in place Laravel will now send out the verification email and when a user clicks on the verification link it will do the necessary security checks and redirect back to your SPA’s URL.

### Reset Password

Setting up the reset password functionality in the API is as simple as following the [official docs](https://laravel.com/docs/8.x/passwords#reset-link-customization). For reference here is what you need to do.

Add the following at the top of `App\Providers\AuthServiceProvider`

```php
use Illuminate\Auth\Notifications\ResetPassword;
```

Add the following in the `AuthServiceProvider` boot method, this will create the URL which is used in the SPA with a generated token:

```php
ResetPassword::createUrlUsing(function ($user, string $token) {
	return env('SPA_URL') . '/reset-password?token=' . $token;
});
```

To make this all work we will need to have a reset-password view in the SPA which handles the token and passes back the users new password. This will be explained fully in the creating of the SPA post which will follow, you can review the code on [Github](https://github.com/garethredfern/laravel-vue/blob/main/src/views/ResetPassword.vue).

### API Routes

Once you have all the authentication in place, any protected routes will need to use the `auth:sanctum` middleware guard. This will ensure that the user has been authenticated before they can view the requested data from the API. Here is a simple example of what those endpoints would look like.

```php
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/users/{user}', function (Request $request) {
    return $request->user();
});
```

### Conclusion

If you are wanting/needing to go down the route of having a completely separate SPA that consumes a Laravel API then hopefully this post has given you all the reference you need to get things set up for the API. In the next article we will focus on setting up the SPA.

If you would like to hear an excellent explanation from Taylor on the how these packages came about I highly recommend listening to his [podcast episode](https://blog.laravel.com/laravel-snippet-25-ecosystem-discussion-auth-recap-passport-sanctum).

The project files for this article can be found on Github:

- [Larvel API](https://github.com/garethredfern/laravel-api)
- [VueJS SPA](https://github.com/garethredfern/laravel-vue)

