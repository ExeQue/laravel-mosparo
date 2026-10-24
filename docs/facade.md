<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# The facade

The `Mosparo` facade reaches the default project, and `project()` reaches any other:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::passes($formData, $submitToken, $validationToken); // bool
Mosparo::project('newsletter')->passes($formData, $submitToken, $validationToken);

Mosparo::enabled(); // false when switched off or not configured
```

`passes()` is the one to use. It answers `true` while mosparo is switched off. It fails closed when a token is missing or mosparo cannot be reached, and reports the exception. It only passes when mosparo's answer carries the right signature.

For the full answer, use `verify()`. It throws `Mosparo\ApiClient\Exception` when mosparo cannot be reached:

```php
$result = Mosparo::verify($formData, $submitToken, $validationToken);

$result->isSubmittable(); // valid, and signed by mosparo
$result->getVerifiedFields();
$result->getIssues();
```

`Mosparo::client()` returns mosparo's own [API client](https://github.com/mosparo/php-api-client) for the project, for statistics and rule packages.
