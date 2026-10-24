<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Testing your application

Switch mosparo off in `phpunit.xml`, and your forms pass without tokens:

```xml
<env name="MOSPARO_ENABLED" value="false"/>
```

To test the failure path, mock the project:

```php
use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Project;

Mosparo::partialMock()
    ->shouldReceive('project')
    ->andReturn(Mockery::mock(Project::class, [
        'enabled' => true,
        'passes' => false,
    ]));
```

Or answer from a fake mosparo with Guzzle's mock handler, through `client_arguments`:

```php
config()->set('mosparo.projects.default.client_arguments.handler', $handler);
```
