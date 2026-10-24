<div>
    <h1>Livewire</h1>
    <p class="lead">Sign in as <code>ada@example.com</code> with <code>password</code>. Try a wrong password first: the box fetches new tokens, and the next attempt goes through.</p>

    <form class="card" wire:submit="signIn">
        <label>Email <input name="email" type="email" wire:model="email" autocomplete="username"></label>
        <label>Password <input name="password" type="password" wire:model="password" autocomplete="current-password"></label>

        @mosparo

        @mosparoError <p class="notice error">{{ $message }}</p> @enderror

        @if($result)
            <p @class(['notice', 'success' => $signedIn, 'error' => ! $signedIn])>{{ $result }}</p>
        @endif

        <button type="submit" wire:loading.attr="disabled">Sign in</button>
    </form>
</div>
