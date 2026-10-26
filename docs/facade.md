# The facade

The `Mosparo` facade talks to mosparo from anywhere in the backend. It reaches the default project, and `project()` reaches any other:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::passes($formData, $submitToken, $validationToken, ['email', 'message']);

Mosparo::project('newsletter')->passes($formData, $submitToken, $validationToken, ['email']);

Mosparo::enabled();
```

## passes()

`passes()` answers `true` or `false`, and is the one to use:

- It answers `true` while mosparo is disabled.
- It fails when a token is missing.
- It fails, and reports the exception, when mosparo cannot be reached or the project misses a value.
- It only passes when mosparo's answer is signed with the project's private key.
- It fails unless mosparo checked each of the listed fields and found it valid. See [Fields mosparo must check](plain-forms.md#fields-mosparo-must-check).

`$formData` holds the submitted fields, keyed like the form's inputs. Leave the two `_mosparo_` tokens in or take them out - either works.

## verify()

For mosparo's full answer, use `verify()`. It returns a read-only `Verification`, and throws when mosparo cannot be reached:

```php
use ExeQue\Mosparo\Enums\FieldStatus;

$verification = Mosparo::verify($formData, $submitToken, $validationToken);

$verification->submittable;      // valid, and signed by mosparo
$verification->fields;           // array<string, FieldStatus>
$verification->field('message'); // FieldStatus::Valid, Invalid or NotVerified
$verification->issues;
```

## Everything else

Statistics, rule packages and metadata are on [Reading data](data.md). `Mosparo::client()` returns mosparo's own [API client](https://github.com/mosparo/php-api-client) for anything this package does not wrap.

## Exceptions

Every exception the package throws implements `ExeQue\Mosparo\Exceptions\MosparoException`:

| Exception | When |
| --- | --- |
| `ConnectionFailed` | mosparo cannot be reached. |
| `RequestFailed` | mosparo refuses the request or answers something unexpected. `debugInformation` holds what mosparo adds in its debug mode. |
| `UnknownProject` | A project name that is not configured. |
| `UnknownTokenSet` | A token set that is not configured as two names, given to `ValidMosparo::for()`. |
| `IncompleteProject` | An enabled project without its host, uuid or a key, or with a host that has a path. |
