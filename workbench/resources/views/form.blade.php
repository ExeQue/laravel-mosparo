<x-layout title="Plain form">
    <h1>Plain form</h1>
    <p class="lead">An ordinary post to a controller. <code>new ValidMosparo</code> in the form request checks it, and the page reloads with fresh tokens.</p>

    <form class="card" method="post" action="/form">
        @csrf

        <label>Email <input name="email" type="email" value="{{ old('email') }}" autocomplete="email"></label>

        @mosparo

        @mosparoError <p class="notice error">{{ $message }}</p> @enderror
        @error('email') <p class="notice error">{{ $message }}</p> @enderror

        @if(session('result'))
            <p class="notice success">{{ session('result') }}</p>
        @endif

        <button type="submit">Subscribe</button>
    </form>
</x-layout>
