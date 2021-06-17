# Nordic products

Library for getting products.

```php
$client = new \NordicProducts\Products();
$client->apiKey = 'YOUR_API_KEY';
$products = $client->getProducts();
var_dump($products);
```

## Installing

The recommended way to install is through
[Composer](https://getcomposer.org/).

```bash
composer require rinu/nordic-products
```
