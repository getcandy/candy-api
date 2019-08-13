# 0.2.94

- [changed] Regional shipping calculator math is now `>=` for `min_basket`

# 0.2.93

- [improved] Improved the way config is fetched from API.

# 0.2.92

- [added] Added `PaymentFailedEvent` to SagePay driver

# 0.2.91

- [fixed] Fixes to asset copying when duplicating a product
- [added] Added `CategoryStoredEvent` when editing categories

# 0.2.90

- [fixed] Fixed issue where asset thumbnails weren't being generated on a product if they already had an image.

# 0.2.89

- [fixed] Fixed issue when calculating the amount of products are rewarding for a discount

# 0.2.88

- [added] Added #161

# 0.2.87

- [improved] Pull out a `getRegionFromZip` method

# 0.2.86

- [added] Added ability to specify pipelines when retrieving shipping methods

# 0.2.85

- [added] Added duplicate product functionality

# 0.2.84

- [fixed] Fixed job handling of class on reindex
- [improved] Allow reindex command to be queued

You can now queue the reindex job by doing the following:

```
php artisan candy:search:index --queue
```

# 0.2.83

- [changed] Assets now order by `position` by default
- [changed] When uploading a YouTube video, make sure OEM data is always fetched.

# 0.2.81

- [changed] PayPal driver records proper Transaction ID from PayPal
- [fixed] Fix reindexer job
- [fixed] Fix undefined index in `AttributeService`

# 0.2.80

- [fixed] Only sync basket with order if it exists and is active.

# 0.2.79

- [added] Added local `placed` scope for `Order`, returns all orders that have a `placed_at` value in the DB.
- [changed] Order listing will now only show `placed` orders if no status is passed.
- [fixed] Fixed order status never ordering by `placed_at` date

# 0.2.78

- [fixed] Fixed issue with wrong global scopes being taken off when indexing

# 0.2.77

- [changed] Changed the way PayPal handles config

If you use the PayPal provider for payments, you need to update your config in `config/services.php` to the following:

```
    'paypal' => [
        'live' => [
            'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
            'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
        ],
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
            'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
        ],
        'settings' => [
            'mode' => env('PAYPAL_ENV'),
        ]
    ],
```

# 0.2.76

- [changed] If no alt provided on asset upload, use parents name
- [changed] Move `option_data` into payload on product

# 0.2.75

- [added] Allow addresses to be marked as default #152

# 0.2.74

- [added] Added Partial SKU search
- [added] Added custom fields to user creation
- [added] Added endpoint to get user fields
- [changed] When creating a product, all channels will be synced as `published_at = null` as opposed to just the default channel
- [changed] Changed reporting metric timelines for the dashboard

# 0.2.73

- [changed] Tax rounds down in TaxCalculator

# 0.2.72

- [fixed] Fixed SagePay integration

# 0.2.71

- [improved] Allow includes to be passed to route fetch

# 0.2.70

- [changed] When uploading external asset from service such as YouTube, only the ID is stored
- [fixed] Fixed asset transform location, was using old path generation when asset was external
- [improved] Order confirmation emails are now queuable, just add config (see below)
- [changed] `RouteResource` is now returned when fetching a route
- [changed] Changed `primaryAsset` relation to `morphOne`
- [added] Added `primary_asset` to ProductTransformer
- [added] Added `primary_asset` to category resource

To queue mailers from orders add the below to the root of `config/getcandy.php`

```
    'mail' => [
        'queue' => 'default',
    ],
```

# 0.2.69

- [added] Added support for SKU attribute in shipping method when adding shipping line.

# 0.2.68

- [changed] Changed logic for sending out order emails

# 0.2.67

- [fixed] Fixed products being associated to a category not reindexing on search

# 0.2.66

- [improved] Improved handling for duplicate saved cards on SagePay

# 0.2.65

- [changed] Add country check when getting shipping methods via a catch all zip
- [changed] Zip code is no longer required
- [changed] Updated logic on order address saving to allow emptying fields
- [changed] `getDetails($type)` is now a public method on orders
- [changed] Stopped automatic assignment of values when creating an order for a user as lead to unexpected values.
- [changed] Simplified the way order references get incremented.

# 0.2.63

- [fixed] #147
- [added] Added custom query builder for category tree*
- [fixed] Fixed shipping preference not resetting when no preference was passed through

The was an issue when using nested set on alot of categories. The way category depth was calculated, combined with the channel scope caused indexes to not be honoured on MySQL 5.7, causing a HUGE query, which most of the time crashed the site, we're taking off global scopes when calculating the depth as it should have zero negative effect on what gets returned, but should drastically speed things up.

# 0.2.62

- [added] Added attributes include to shipping methods
- [added] You can now pass through `is_manual` when creating an order line.
- [added] Tax rate indicator when saving order lines
- [changed] Order calculator now handles multiple shipping lines

# 0.2.61

- [fixed] Fixed phone/email not saving on orders when using address ID

# 0.2.60

- [improved] Assets saving uses timebased folder structure
- [fixed] Fixed incorrect invoice reference increment when multiple `-` in the reference
- [added] Added channels middleware to API routes
- [improved] Make sure currency converted amounts get passed on PriceCalculator

# 0.2.59

- [added] #143

# 0.2.58

- [fixed] #142

# 0.2.57

- [fixed] Fixed `MissingValue` check on Eloquent Resources
- [improved] Added default customer groups when creating a product if none specified
- [added] Method to get encodedIds by an array of "real" IDs
- [fixed] Fix position being set correctly when adding attribute group

# 0.2.56

- [improved] Set up sensible defaults when creating a category for customer groups and channels

# 0.2.55

- [added] Added Alt contact number to user details

# 0.2.54

- [added] Added ability to restrict products by ids

# 0.2.53

- [fixed] Fixed product association loading when a product didn't exist
- [fixed] Fixed shipping/billing fields being overwritten on order create.

# 0.2.52

- [added] Support for invoice reference prefix `getcandystore.orders.invoice_prefix`

# 0.2.51

- [added] Added some initial reporting endpoints

# 0.2.50

- [improved] Improved the way the regional provider checked delivery postcodes
- [fixed] Stopped data override when saving a new users address in checkout

# 0.2.49

- [added] Added product importer
- [fixed] Added missing channel scope to products

# 0.2.48

- [improved] Added support for custom order invoices from the hub

To use your own custom template for invoices downloaded from the hub, simply add this to your `config/getcandy.php`:

```
    'invoicing' => [
        'pdf' => 'your.invoice.view',
    ],
```

# 0.2.47

- [improved] Eager load `channels` and `customerGroups` when loading Categories into a tree

# 0.2.46

- [changed] Added hub request detect on ProductVariantFactory

# 0.2.45

- [added] Add customer group pricing resources
- [changed] Basket routes now go through set channel middleware

# 0.2.44

- [added] Added support for channel to be passed as a header

# 0.2.43

- [added] Added product images on basket update

# 0.2.42

- [fixed] CreateRequest for basket handles no variants better
- [improved] If asset is external, just return the url
- [improved] Get default channel when getting an attribute

# 0.2.41

- [fixed] Firstname and lastname on order address store isn't required with Address ID.
- [changed] Set channel on middleware only if it's not already set.
- [fixed] Fixed setting order address from address id

# 0.2.40

- [improved] Added earlier exception if an order is already placed on checkout

# 0.2.39

- [fixed] Fixed global scopes not honouring a users groups
- [improved] Product customer group pricing now uses global scope
- [added] You can now pass through order meta at more points in the checkout lifecycle
- [added] Added a basket claim endpoint

# 0.2.38

- [added] Added bool for if a basket was changed during merge.

# 0.2.37

- [fixed] Various fixes for global scopes and indexing

# 0.2.36

- [changed] Initial work on moving Collections to resources
- [improved] Hashids validator now accepts class reference
- [changed] Changed way some global scopes are added
- [improved] Improved product association resources

# 0.2.35

- [fixed] Fixed handling of non existent includes when getting a users basket
- [fixed] Fixed handling of SagePay charge when rejected for fraud reasons

# 0.2.34

- [fixed] Fixed issue where indexer wasn't getting categories

# 0.2.33

- [fixed] Fixed default getcandy config
- [added] Added global channel scope to categories

# 0.2.32

- [added] Added CandyHub detection

# 0.2.31

- [added] Added custom meta field to baskets and orders

# 0.2.30

- [added] Added total tax to `ProductVariantTransformer`

# 0.2.29

- [improved] Added support for adding an address to a customer
- [fixed] Fixed missing `ElasticIndexCommand` being referenced in service provider

# 0.2.28

- [removed] Removed `candy:elastic:index` in favour of `candy:search:index`

# 0.2.27

- [added] Make `ancestor` and `parent`, in `CategoryResource`, available to include.

# 0.2.26

- [changed] Changes to the request when adding items to a basket to prevent recursive queries.

# 0.2.25

- [fixed] Make sure `min_batch` gets passed to the API resource

# 0.2.24

- [improved] `min_quantity` validation now includes the minimum quantity required in the validation message.
- [added] Added a `min_batch` column on `product_variants` which defaults to `1` means you can have a minimum batch size a product can be ordered in, validation message is `You must add this item in multiples of :min_batch`

# 0.2.23

- [added] Added factor tax to product variant transformer
- [added] Added `minQuantity` to `BasketValidator` when adding a variant to the basket
- [fixed] Fixed wrong offline payment type being referenced in base config

# 0.2.22

- [fixed] Fixed issue where 404 wasn't thrown on product endpoint
- [fixed] Fixed issue where you couldn't search on the users endpoint
- [fixed] `ChannelResource` now only includes `published_at` if it exists on relation pivot

# 0.2.21

- [fixed] Fixed contraint that prevented shipping prices from being deleted.
- [added] Added `image` include for `ProductVariantResource`
- [added] Added `groups` include for `ProductTierResource`
- [added] Added a `getFullName` getter for user details

# 0.2.20

- [fixed] Make sure default channel is set on new products

# 0.2.19

- [changed] Changed key check on order `tracking_no` to `array_key_exists` to handle any value
- [improved] Add variant and option data to order line when resolving lines.
- [added] Added a CandyApi util class
- [changed] Change check on `TaxCalculator` to `is_null` to allow for passing `0`
- [changed] Simplified the price calculator logic.

# 0.2.18

- [fixed] Fixed SKU mapping that stopped you from sorting on it in Elastic.
- [fixed] Fixed undefined variable on order processing factory when a closure was used.
- [fixed] Fixed invalid namespace for listener.

# 0.2.17

- [fixed] Fixed exception handling on a route

# 0.2.16

- [fixed] Fixed issue where `published_at` date for category channel wasn't being returned in the resource.

# 0.2.15

- [fixed] Fixed issue where category children weren't honouring the nested set order when loaded via relation.

# 0.2.14

- [fixed] Fixed channel not being set on category query.
- [added] Added new Activity Log for orders initially.
- [added] Added `firstOrder` relation on the User model.

# 0.2.13

- [improved] Improved handling of order reference increment

# 0.2.12

- [fixed] Fixed issue with installer due to country column change
- [added] Added config for order table columns in the hub
- [changed] Order settings now pass through all config

# 0.2.11

- [changed] Changed plugin loader to not require file extension, this was causing some issues on specific nginx servers.
- [fixed] Allow tracking no to be set to null on orders

# 0.2.10

- [fixed] Fixed issue where updating shipping price zeroed out order totals.
- [changed] Updated the set shipping price method for DI.
- [fixed] Fixed offline payment driver, was a bit outdated.

# 0.2.7

- [fix] Remove left over calls to countries json columns

# 0.2.6

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

# 0.2.5

- [fixed] Fixed order notes and customer reference not saving on order.
- [fixed] Fixed exception when retrieving the current basket if the authenticated user never had one before.
- [changed] Don't round tiered pricing, comes back as three decimal places.
- [changed] The country name column in the database is now a text field, we aim to support translation files instead for this.

# 0.2.4

- [fixed] Fixed issue with order searching caused by changing variant to description on order lines.

# 0.2.3

- [added] Added created_at to the transaction resource

# 0.2.2

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
