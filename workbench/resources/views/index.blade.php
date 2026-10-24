<x-layout>
    <h1>laravel-mosparo</h1>
    <p class="lead">Forms against a local mosparo. Most use an invisible box: the check runs when the form is sent.</p>

    <div class="cards">
        <a class="card" href="/livewire" wire:navigate>
            <h2>Livewire</h2>
            <p>A sign-in form. Send a wrong password, then the right one, without reloading.</p>
        </a>
        <a class="card" href="/form">
            <h2>Plain form</h2>
            <p>A newsletter form posted to a controller and checked by a validation rule.</p>
        </a>
        <a class="card" href="/visible">
            <h2>Visible</h2>
            <p>A second project with the checkbox, and the same project forced invisible.</p>
        </a>
        <a class="card" href="/rejected" wire:navigate>
            <h2>Rejected</h2>
            <p>A form mosparo turns away, and the box recovering for the next try.</p>
        </a>
    </div>
</x-layout>
