# Testing your application

## Disable mosparo

Disable mosparo in `phpunit.xml`, and your forms pass without a box or tokens:

```xml
<env name="MOSPARO_ENABLED" value="false"/>
```

Livewire forms are still validated, so the rest of your tests behave as before.

## Fake mosparo

To test what happens when mosparo accepts or rejects a submission, fake it. Every project then renders a working box and answers checks without asking mosparo - accepting them, until you tell it to reject:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::fake();            // accepts every check
Mosparo::fake()->reject();  // rejects every check, as mosparo does with spam
```

`accept()` and `reject()` can be called on the fake at any point in a test. Give either a callback to decide check by check, from the submitted fields and the project:

```php
// Rejects what looks like spam, accepts everything else.
Mosparo::fake()->reject(fn (array $formData) => str_contains($formData['message'] ?? '', 'pills'));

// Accepts the newsletter project only.
Mosparo::fake()->accept(fn (array $formData, string $project) => $project === 'newsletter');
```

A number decides that many checks, and the ones after go the other way. A sequence decides the next checks in order - `true` accepts, `false` rejects - and the checks after it are decided as before:

```php
Mosparo::fake()->reject(1);                          // the first check is rejected, the rest accepted
Mosparo::fake()->accept(2);                          // two checks are accepted, the rest rejected
Mosparo::fake()->reject()->sequence(true, false);    // accepted, rejected, then rejected from there on
Mosparo::fake()->accept()->sequence(false, false);   // rejected, rejected, then accepted from there on
```

`sequence()` follows `accept()` or `reject()`, which decide the checks once the sequence is used up. On its own, `Mosparo::fake()->sequence(...)` falls back to accepting, the fake's default.

A check without both tokens fails without using up the sequence.

The callback receives the fields the check was given: for `ValidMosparo`, everything that was posted; for `verify()` in Livewire, the form's fields.

A check still needs both tokens. A plain form post sends them as fields:

```php
Mosparo::fake()->reject();

$this->post('/newsletter', [
    'email' => 'ada@example.com',
    '_mosparo_submitToken' => 'submit',
    '_mosparo_validationToken' => 'validation',
])->assertSessionHasErrors('_mosparo_submitToken');
```

A Livewire component receives them the way the box sends them:

```php
use ExeQue\Mosparo\Mosparo as MosparoManager;

Mosparo::fake()->reject();

Livewire::test(SignIn::class)
    ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
    ->set('form.email', 'ada@example.com')
    ->set('form.password', 'password')
    ->call('signIn')
    ->assertHasErrors('mosparo.submitToken');
```

## Asserting

```php
$fake = Mosparo::fake();

// ...

$fake->assertChecked();
$fake->assertChecked(fn (array $check) => $check['formData']['email'] === 'ada@example.com');
$fake->assertNotChecked();
$fake->assertMetadataStored(['user' => 42]);
```

Every check, stored metadata and rule package change is also kept on the fake, in `$fake->checks`, `$fake->metadata` and `$fake->rulePackageChanges`. A check holds `project`, `formData`, `fields` and whether it `passed`.

## Data

The fake answers [data calls](data.md) without asking mosparo: empty statistics and rule packages, unless you give it others:

```php
use ExeQue\Mosparo\Data\Statistics;

Mosparo::fake()->withStatistics(new Statistics(validSubmissions: 12, spamSubmissions: 3, lastSubmissionAt: null, days: []));
```

`withRulePackageRules()` and `withRulePackageRuleItems()` do the same for rule packages.
