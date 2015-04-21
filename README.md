[![Deployment status from dploy.io](https://helmar.dploy.io/badge/23779029970145/26313.png)](http://helmar.dploy.io)

## Checking for subscription

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

### msg

```php
$user->subscription['msg']
```
This will only be there is the status is "error", and can be used for debugging purposes, or error messages.

### plan_type

```php
$user->subscription['plan_type']
```
Possible values:

* sub-paper
* sub-digital
* sub-digital+paper

This can be used to find out what type of subscription they have, but should not be used for things like displaying the digital mag...

### digital

```php
$user->subscription['digital']
```
Possible values:

* true
* false
* error

This is the one that can be used to for display the digital mag, and related things.

### paper

```php
$user->subscription['paper']
```
Possible values:

* true
* false
* error
 
Used to find out if they receive the paper mag.

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
