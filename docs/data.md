# Reading data

Beyond checking submissions, mosparo's API gives statistics, rule packages and metadata. All of it is on the facade for the default project, and on `Mosparo::project('newsletter')` for any other. Every call returns a read-only data object from `ExeQue\Mosparo\Data`.

## Statistics

```php
use ExeQue\Mosparo\Facades\Mosparo;

$statistics = Mosparo::statistics(days: 30);

$statistics->validSubmissions;  // int
$statistics->spamSubmissions;   // int
$statistics->lastSubmissionAt;  // ?CarbonImmutable

foreach ($statistics->days as $date => $day) {
    $day->date;               // CarbonImmutable
    $day->validSubmissions;   // int
    $day->spamSubmissions;    // int
    $day->delayedRequests;    // int
    $day->blockedRequests;    // int
}
```

Leave `days` out for every day mosparo keeps, or start at a date with `from: now()->subWeek()`.

## Rule packages

Rule packages are read a page at a time:

```php
$page = Mosparo::rulePackageRules(rulePackageId: 3, page: 1);

foreach ($page->rules as $rule) {
    $rule->name;              // string
    $rule->type;              // string
    $rule->numberOfItems;     // int
    $rule->spamRatingFactor;  // float
    $rule->updatedAt;         // ?CarbonImmutable
}

$page->hasMorePages();
```

The items of one rule:

```php
$items = Mosparo::rulePackageRuleItems(rulePackageId: 3, ruleId: 7);

foreach ($items->items as $item) {
    $item->type;    // string
    $item->value;   // string
    $item->rating;  // float
}
```

Changing a package clears the project's [cache](caching.md):

```php
Mosparo::importRulePackage(3, $content, $hash);  // RulePackageImport: successful, verifiedHash
Mosparo::updateRulePackage(3, $tasks);           // RulePackageUpdate: successful, errors
```

The hash index of a package streams into a file, and is never cached:

```php
Mosparo::streamRulePackageHashIndex(3, new SplFileObject($path, 'w'), fn (int $bytes) => $this->info("{$bytes} bytes"));
```

## Metadata

mosparo can keep metadata with a verified submission, such as the id of the user who sent it. Store it after the check, with the same tokens:

```php
Mosparo::storeMetadata($submitToken, $validationToken, ['user' => $user->id]);
```

In Livewire, pass it to `verify()`, which stores it when the check passes:

```php
$this->mosparo->verify($this->form, ['message'], metadata: ['user' => auth()->id()]);
```

A failure to store metadata there is reported, and does not stop the form.

## When something goes wrong

Every call throws one of the package's [exceptions](facade.md#exceptions): `ConnectionFailed` when mosparo cannot be reached, `RequestFailed` when it refuses the request or answers something unexpected.
