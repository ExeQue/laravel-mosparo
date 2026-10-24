<form wire:submit="signIn">
    <h1>Livewire</h1>

    <label>Email <input name="email" type="email" wire:model="email"></label>
    <label>Password <input name="password" type="password" wire:model="password"></label>

    @mosparo

    @error('mosparo.submitToken') <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Sign in</button>

    @if($result)
        <p class="result">{{ $result }}</p>
    @endif
</form>
