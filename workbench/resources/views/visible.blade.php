<x-layout title="Visible">
    @mosparoScript('visible')

    <h1>Visible</h1>
    <p class="lead">A second mosparo project, <code>visible</code>, shows the box as a checkbox. Both forms below use it; each is its own Livewire component with its own box.</p>

    <h2 class="section">The checkbox</h2>
    <p class="hint">Tick the box, then send. <code>&lt;x-mosparo project="visible" /&gt;</code></p>
    <livewire:comment />

    <h2 class="section">Forced invisible</h2>
    <p class="hint">The same project, without the checkbox: the check runs on send. <code>:options="['forceInvisible' => true]"</code></p>
    <livewire:comment :force-invisible="true" />
</x-layout>
