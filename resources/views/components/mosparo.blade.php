@props([
    'project' => null,
    'id' => null,
    'options' => [],
])
@php
    $mosparo = app(\ExeQue\Mosparo\Mosparo::class)->project($project);

    // Inside a Livewire component the id stays the same between renders, so
    // Livewire keeps the box rather than replacing it on every update.
    $id ??= isset($__livewire)
        ? "mosparo-{$__livewire->getId()}-{$mosparo->name}"
        : 'mosparo-'.\Illuminate\Support\Str::random(12);
@endphp
@if($mosparo->enabled())
<div
    wire:ignore
    id="{{ $id }}"
    data-mosparo="{{ json_encode($mosparo->frontend($options), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) }}"
    {{ $attributes }}
></div>
@endif
