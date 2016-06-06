## Checking for subscription

Make sure to call include the stripe library on the page
```php
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);
```


You have to add the check subscription line right below the check login line.
```php
$user->checklogin(1);
$user->checksub();
```

Then the following variables will be available in `$user`:

### status
```php
$user->subscription['status']
```
Possible values:

* error
* none
* active
* trialing
* past_due
* unpaid
* canceled

### id

```php
$user->subscription['id']
```
The id of the selected plan. Currently, it should always be 'helmar16'

### msg

```php
$user->subscription['msg']
```
This will only be there is the status is "error", and can be used for debugging purposes, or error messages.

### cancel_at_period_end

```php
$user->subscription['cancel_at_period_end']
```
Possible values:

* true
* false

This lets us know if their subscription will renew or not at the end of their period. `true` means it will cancel, `false` means it will renew

### current_period_end

```php
$user->subscription['current_period_end']
```
This is a unix time stamp of when the users subscription period ends

### next_payment

```php
$user->subscription['next_payment']
```
The amount the credit card will be charges when the plan renews.
