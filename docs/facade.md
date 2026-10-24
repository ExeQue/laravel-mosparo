# The facade

The `Mosparo` facade talks to mosparo from anywhere in the backend. It reaches the default project, and `project()` reaches any other:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::passes($formData, $submitToken, $validationToken);
Mosparo::passes($formData, $submitToken, $validationToken, fields: ['message']);

Mosparo::project('newsletter')->passes($formData, $submitToken, $validationToken);

Mosparo::enabled();
```

## passes()

`passes()` answers `true` or `false`, and is the one to use:

- It answers `true` while mosparo is disabled.
- It fails when a token is missing.
- It fails, and reports the exception, when mosparo cannot be reached or the project misses a value.
- It only passes when mosparo's answer is signed with the project's private key.
- It fails unless mosparo checked each of `fields` and found it valid. See [Fields mosparo must check](plain-forms.md#fields-mosparo-must-check).

`$formData` holds the submitted fields, keyed like the form's inputs. Leave the two `_mosparo_` tokens in or take them out - either works.

## verify()

For mosparo's full answer, use `verify()`. It throws `Mosparo\ApiClient\Exception` when mosparo cannot be reached:

```php
$result = Mosparo::verify($formData, $submitToken, $validationToken);

$result->isSubmittable(); // valid, and signed by mosparo
$result->getVerifiedFields();
$result->getIssues();
```

## client()

`Mosparo::client()` returns mosparo's own [API client](https://github.com/mosparo/php-api-client) for the project, for statistics and rule packages.

## Exceptions

Every exception the package throws implements `ExeQue\Mosparo\Exceptions\MosparoException`:

| Exception | When |
| --- | --- |
| `UnknownProject` | A project name that is not configured. |
| `IncompleteProject` | An enabled project without its host, uuid or a key. |
