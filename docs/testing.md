# Testing your application

## Disable mosparo

Disable mosparo in `phpunit.xml`, and your forms pass without a box or tokens:

```xml
<env name="MOSPARO_ENABLED" value="false"/>
```

Livewire forms are still validated, so the rest of your tests behave as before.

## Fake mosparo

To test what happens when mosparo accepts or rejects a submission, fake it. Every project then renders a working box and answers checks with the result you give, without asking mosparo:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::fake(passes: false);
```

A check still needs both tokens. A plain form post sends them as fields:

```php
$this->post('/newsletter', [
    'email' => 'ada@example.com',
    '_mosparo_submitToken' => 'submit',
    '_mosparo_validationToken' => 'validation',
])->assertSessionHasErrors('_mosparo_submitToken');
```

A Livewire component receives them the way the box sends them:

```php
use ExeQue\Mosparo\Mosparo as MosparoManager;

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

Every check, stored metadata and rule package change is also kept on the fake, in `$fake->checks`, `$fake->metadata` and `$fake->rulePackageChanges`.

## Data

The fake answers [data calls](data.md) without asking mosparo: empty statistics and rule packages, unless you give it others:

```php
use ExeQue\Mosparo\Data\Statistics;

Mosparo::fake()->withStatistics(new Statistics(validSubmissions: 12, spamSubmissions: 3, lastSubmissionAt: null, days: []));
```

`withRulePackageRules()` and `withRulePackageRuleItems()` do the same for rule packages.
