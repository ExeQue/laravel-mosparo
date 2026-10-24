@props([
    'project' => null,
    'id' => null,
    'options' => [],
])
@php($mosparo = app(\ExeQue\Mosparo\Mosparo::class)->project($project))
@if($mosparo->enabled())
<div
    wire:ignore
    id="{{ $id ?? 'mosparo-'.\Illuminate\Support\Str::random(12) }}"
    data-mosparo="{{ json_encode($mosparo->frontend($options), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) }}"
    {{ $attributes }}
></div>
@endif
