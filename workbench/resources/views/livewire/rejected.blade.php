<div>
    <h1>Rejected</h1>
    <p class="lead">With the box ticked, the message is changed on the server before mosparo is asked. It no longer matches what the box checked, so mosparo turns it away. Untick it and send again.</p>

    <form class="card" wire:submit="send">
        <label>Message <textarea name="message" wire:model="message"></textarea></label>

        <label class="check"><input type="checkbox" wire:model="tamper"> Change the message on the server</label>

        @mosparo

        @mosparoError <p class="notice error">{{ $message }}</p> @enderror

        @if($result)
            <p class="notice success">{{ $result }}</p>
        @endif

        <button type="submit" wire:loading.attr="disabled">Send</button>
    </form>
</div>
