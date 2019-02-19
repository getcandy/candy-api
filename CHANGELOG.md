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
