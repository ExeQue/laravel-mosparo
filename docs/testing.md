# Testing your application

## Disable mosparo

Disable mosparo in `phpunit.xml`, and your forms pass without a box or tokens:

```xml
<env name="MOSPARO_ENABLED" value="false"/>
```

Livewire forms are still validated, so the rest of your tests behave as before.

## Testing a rejection

Mock the facade's `passes()` for a backend check:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::shouldReceive('passes')->andReturn(false);
```

A Livewire component calls the project, not the facade. Enable mosparo for the test, send the component a pair of tokens, and point the project at a mosparo that cannot be reached - the check then fails as it would against a rejecting mosparo:

```php
use ExeQue\Mosparo\Mosparo as MosparoManager;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;

config([
    'mosparo.enabled' => true,
    'mosparo.host' => 'https://mosparo.invalid',
    'mosparo.projects.default' => ['uuid' => 'uuid', 'public_key' => 'public', 'private_key' => 'private'],
    'mosparo.client_arguments.handler' => fn ($request) => Create::rejectionFor(new ConnectException('Unreachable', $request)),
]);

Livewire::test(SignIn::class)
    ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
    ->set('form.email', 'ada@example.com')
    ->set('form.password', 'password')
    ->call('signIn')
    ->assertHasErrors('mosparo.submitToken');
```
