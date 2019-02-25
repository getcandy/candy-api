#0.2.6

- [added] You can now specify search config for the client.

``` php
    // Rest of getcandy.php config
    'search' => [
        'client_config' => [
            'elastic' => [
                'host' => null,
                'port' => null,
                'path' => null,
                'url' => null,
                'proxy' => null,
                'transport' => null,
                'persistent' => true,
                'timeout' => null,
                'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
                'roundRobin' => false,
                'log' => false,
                'retryOnConflict' => 0,
                'bigintConversion' => false,
                'username' => null,
                'password' => null,
            ]
        ],
    ]
```

#v0.2.5

- [fixed] Fixed order notes and customer reference not saving on order.
- [fixed] Fixed exception when retrieving the current basket if the authenticated user never had one before.
- [changed] Don't round tiered pricing, comes back as three decimal places.
- [changed] The country name column in the database is now a text field, we aim to support translation files instead for this.

#v0.2.4

- [fixed] Fixed issue with order searching caused by changing variant to description on order lines.

#v0.2.3

- [added] Added created_at to the transaction resource

# v0.2.2

- [added] Ability to set custom attributes on resources, just call `$model->setCustomAttribute('foo', 'bar');`.
- [improved] Improved the way basket totals were displaying when discounts were applied.
- [fixed] Fixed issue where basket wasn't acknowledging free products in discounts.
- [fixed] Braintree payment driver is more up to date.
- [added] Added group helper for users and customer groups, you can call `$user->inGroup('retail')`.
- [changed] Changed the country column to a text column in the countries table.
- [improved] The shipping method calculator for an order now takes into account zones.
- [fixed] Fixed error where orders tripped over when no settings were present in the database.
- [fixed] Regional shipping calculator now takes into account no minimum basket values.
- [fixed] Fixed issue where an order wouldn't allow a zone not to have a name.
- [changed] Resolve the Guzzle client out of the container on the SagePay payment driver.
