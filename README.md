# Laravel Bundle for Lithuanian/Latvian Banks internet services

### Banks implemented

Lithuania: Danske, DNB, Nordea, SEB, Šiaulių bankas, Swedbank
Latvian: Citadele, SEB

### Installation


Require this package with composer:

```
composer require talandis/laravel-banklinks
```

### Configuration


After updating composer, add the ServiceProvider to the providers array in config/app.php

```
'Talandis\LaravelBanklinks\LaravelBanklinksServiceProvider',
```

Copy the package config to your local config with the publish command:

```
php artisan config:publish talandis/laravel-banklinks
```

Don't forget to enter your certificates and other details into configuration files.

### Usage

#### Payment requests

Below is a simple sample of payment request.

```php
$bank = new \Talandis\Banklinks\Lithuania\SEB();
$bank->setCallbackUrl( URL::to( 'callback/seb' ) );
$bank->setCancelUrl( URL::to('cancel/seb' ) );

$requestData = $bank->getPaymentRequest(1, 25, 'Beer + Movie');
$requestUrl = $bank->getRequestUrl();
```

Sample form

```html
<form action="<?php echo $requestUrl ?>" method="post">
    <?php foreach ( $requestData as $fieldName => $value ): ?>
      <input type="hidden" name="<?php echo $fieldName ?>" value="<? echo $value ?>" />
    <?php endforeach; ?>
    <input type="submit" value="Make payment" />
</form>
```

#### Succesful payment callback

```php
$bank = new \Talandis\Banklinks\Lithuania\SEB();

if ( $bank->isPaidResponse( Input::all() ) ) {

    echo $bank->getOrderId();

} else if ( $bank->isReturnResponse( Input::all() ) ) {

}
```

#### Cancelled payment callback

```php
$bank = new \Talandis\Banklinks\Lithuania\SEB();

if ( $bank->isCancelResponse( Input::all() ) ) {

}
```
