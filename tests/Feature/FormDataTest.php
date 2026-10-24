<?php

declare(strict_types=1);

use ExeQue\Mosparo\Support\FormData;
use Illuminate\Http\UploadedFile;

it('trims strings', function () {
    expect(FormData::prepare(['name' => "  Ada \n", 'age' => 36, 'note' => null]))
        ->toBe(['name' => 'Ada', 'age' => 36, 'note' => null]);
});

it('names nested fields the way the form does', function () {
    expect(FormData::prepare(['address' => ['street' => 'Main St', 'city' => ['name' => 'Aarhus']]]))
        ->toBe(['address[street]' => 'Main St', 'address[city][name]' => 'Aarhus']);
});

it('keeps lists as lists', function () {
    expect(FormData::prepare(['tags' => [' one ', 'two']]))->toBe(['tags' => ['one', 'two']]);
});

it('leaves files out', function () {
    expect(FormData::prepare([
        'name' => 'Ada',
        'avatar' => UploadedFile::fake()->create('avatar.png'),
        'tags' => ['one', UploadedFile::fake()->create('other.png'), ['nested']],
    ]))->toBe(['name' => 'Ada', 'tags' => ['one']]);
});
