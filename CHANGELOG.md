
## 0.12.0
### Upgrading

Update the composer package

```bash
$ composer update @getcandy/candy-api
```

```bash
$ php artisan migrate
```

### High Impact Changes

#### Maintenance Migrations

Some columns have been added/removed from the database. The tables/columns affected are:

- `orders`
    - Removed `company_name` column as it wasn't being used and we have other columns for that now
    - Added `billing_company_name` and `shipping_company_name` columns.
- `countries`
    - Remove `country` column in favour of a `country_id` relationship

#### Eager loading relations for the current user

Previously when returning the current user via `users/current` there was some hard coded includes, this has been replaced to allow the `include` query parameter.
You should update any calls to this endpoint if you rely on included resources. The previous default includes were:

```php
['addresses.country', 'roles.permissions', 'customer', 'savedBaskets.basket.lines']
```

### Drafting has changed

The way drafting previously worked has now been refactored to be less destructive. You should reindex your products before going back into the hub to get everything in sync.

You can do this by running `php artisan candy:products:reindex` and `php artisan candy:categories:reindex`H

### Route searching

The way you search for routes has changed on the API. We have removed the `path` column and also the `locale` column in favour of a `language_id` relation.

When you search for a route, previously you would do something like:
```javascript
const { data } = await axios('routes/search', {
    params: {
        slug: 'slug-for-the-product',
        path: null,
        include: 'element'
    }
})
```
This should now be changed to:
```javascript
const { data } = await axios('routes/search', {
    params: {
        slug: 'slug-for-the-product',
        language_id: '6z8m9gmj',
        element_type: 'product',
        include: 'element'
    }
})
```

### 🐞 Fixes
- Fixed an issue that was causing a indefinite wildcard search on products
- Allow certain fields to be nullable on a customer address (`company_name`, `address_two`, `address_three`)
- Fixed some issues on route creation
- Fixed issue where shipping method relationships were not having their timestamps updated
- Fixes to some migrations
- Fixed an issue where the recycle bin item wasn't returned on the relationship
- Fixed and issue where the indexable event wasn't being triggered when publishing a resource
- Fixes to drafting and publishing of resources
- Fixed an issue where `path` wasn't updating when updating a route

### ⭐ Improvements

- Slight optimisation for Elasticsearch and the fields it returns
- Drafting and Publishing of a draft will now run in a transaction, you can also extend the drafting functionality in your plugins.
- SKU uses `trim` when being saved
- Languages have been refactored and simplified so now we only rely on `code`. The `lang` column has been replaced by `code` and the `iso` column has been removed.
- When detecting the language to use for API responses, we now parse the `accept-language` header properly.
### 🏗️ Additions

- Added endpoint to get a payment provider via it's given ID
- Added Stripe Payment Intents provider
- Added a `RebuildTree` action and command for categories, so if your category tree is messed up you can run `candy:categories:rebuild`
- Added `user/addresses` endpoint to get the current users saved addresses
- Added initial report exporting logic, this will now run and exporter in the background and email you when ready to download.
- Add some additional reports
    - Average spending across customer groups
    - Total spending across customer groups

---

# 0.3.8

- [added] Added CategoryStoredEvent when editing categories

# 0.3.7

- [changed] Changed the `TextFilter` field to use the `filter` field in the mapping, seemed to give more accurate results.

# 0.3.6

Update PHP version

# 0.3.5

Add lockfile to .gitignore

# 0.3.4

- [changed] Changed `type` to `search_type` to be more explicit. Also `search_type` is no longer a required field and will default to a product search
- [changed] Changed `includes` on search to `include`
