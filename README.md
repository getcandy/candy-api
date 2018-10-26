
> **Please Note**  
> Whilst every effort has been made to make this software ready for a release, it is provided as is, this is very much still in early alpha stages and you use it at your own risk.  

> **This is not production ready software**

# GetCandy E-Commerce API
A laravel way to build an API driven store online.


## Requirements

- Laravel >= 5.5
- Elasticsearch >= 6

## Server setup
We think the easiest way to get up and running is to use [Valet+](https://github.com/weprovide/valet-plus) with Elasticsearch. But really, it's up to you :)

## Installing via composer

You can either install just the API or the API and Hub.

#### Just the API

This will contain everything you need to manage your store through the API endpoints.
```
composer require getcandy/candy-api
```

#### API and Hub (recommended)

This will install the API and the Hub, so you have a nice interface to manage your store.
```
composer require getcandy/candy-hub
```

#### Laravel 5.6
GetCandy supports package auto discovery

#### Laravel 5.5
Add the three required service providers to your `config/app.php` file

```php
'providers' => [
  // ...
  
  GetCandy\Api\Providers\ApiServiceProvider::class,
  GetCandy\Api\Providers\EventServiceProvider::class,
  
  // If you have installed the hub, add this one
  GetCandy\Hub\Providers\HubServiceProvider::class
],
```

## Publish the config / resources
The API needs to publish some config items and the hub needs to publish some resources (if you're using it)

```
php artisan vendor:publish --tag=config

// If using the hub
php artisan vendor:publish --tag=public
```

## Set up your users
GetCandy doesn't have it's own User model, we figured you'd want your own, so we just use a trait:

```php
use GetCandy\Api\Core\Traits\HasCandy;

class User extends Authenticatable
{
    use HasCandy;
```

The API uses passport for authentication, so make sure your `config/auth.php` config uses this:
> This will probably be refactored into it's own gate going forward, hangover from non package days...

```php
'guards' => [
  // ...
  
  'api' => [
     'driver' => 'passport',
     'provider' => 'users',
   ]
]
```


## Get things up and running
The API needs some _bare minimum_ data to get going, for now we've just made a console command to get going:

```
php artisan candy:install
```

Follow the installation steps and you'll be able to log in and start adding products!
