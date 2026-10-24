<x-layout>
    <form method="post" action="/form">
        @csrf
        <h1>Plain form</h1>

        <label>Email <input name="email" type="email" value="{{ old('email') }}"></label>

        @mosparo

        @error('_mosparo_submitToken') <p class="error">{{ $message }}</p> @enderror
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Subscribe</button>

        @if(session('result'))
            <p class="result">{{ session('result') }}</p>
        @endif
    </form>
</x-layout>
