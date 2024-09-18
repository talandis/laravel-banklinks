# Laravel Bundle for Lithuanian/Latvian Banks internet services

### Banks implemented
Lithuania: Danske, DNB, Nordea, SEB, Šiaulių bankas, Swedbank, Kevin, Everypay, Neopay  
Latvian: Citadele, SEB

### Installation
Require this package with composer:

```
composer require talandis/laravel-banklinks
```

#### Provider specific installation
#### Kevin.EU

If you want to use `Kevin` payments you need to add [getkevin/kevin-php](https://packagist.org/packages/getkevin/kevin-php) dependency to your project:
```bash
composer require getkevin/kevin-php
```

### Configuration


After updating composer, add the ServiceProvider to the providers array in config/app.php

```
Talandis\LaravelBanklinks\LaravelBanklinksServiceProvider::class,
```

Copy the package config to your local config with the publish command:

```
php artisan vendor:publish --provider="Talandis\LaravelBanklinks\LaravelBanklinksServiceProvider"
```

Don't forget to enter your certificates and other details into configuration files.

### Usage

#### Payment requests

Below is a simple sample of payment request.

```php
$bank = new \Talandis\LaravelBanklinks\Lithuania\SEB();
$bank->setConfiguration( config('banklinks.lithuania-seb') );   // This line is optional. Same configuration is read automatically
$bank->setCallbackUrl( url( 'callback/seb' ) );
$bank->setCancelUrl( url('cancel/seb' ) );

$requestData = $bank->getPaymentRequest(1, 25, 'Beer + Movie');
$requestUrl = $bank->getRequestUrl();
```

Sample form

```html
<form action="{{$requestUrl}}" method="post">
    @foreach ( $requestData as $fieldName => $value ):
      <input type="hidden" name="{{$fieldName}}" value="{{$value}}" />
    @endforeach
    <button type="submit">Make payment</button>
</form>
```

#### Succesful payment callback

```blade
$bank = new \Talandis\Banklinks\Lithuania\SEB();
$bank->setConfiguration( config('banklinks.lithuania-seb') );   // This line is optional. Same configuration is read automatically

if ( $bank->isPaidResponse( Input::all() ) ) {

    echo $bank->getOrderId();

} else if ( $bank->isReturnResponse( Input::all() ) ) {

}
```

#### Cancelled payment callback

```php
$bank = new \Talandis\Banklinks\Lithuania\SEB();
$bank->setConfiguration( config('banklinks.lithuania-seb') );   // This line is optional. Same configuration is read automatically

if ( $bank->isCancelResponse( Input::all() ) ) {

}
```
