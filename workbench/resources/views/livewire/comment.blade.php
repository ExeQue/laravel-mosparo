<form class="card" wire:submit="send">
    <label>Comment <textarea name="comment" wire:model="comment"></textarea></label>

    <x-mosparo
        project="visible"
        :id="$forceInvisible ? 'comment-invisible' : 'comment-visible'"
        :options="['forceInvisible' => $forceInvisible]"
    />

    @mosparoError <p class="notice error">{{ $message }}</p> @enderror

    @if($result)
        <p class="notice success">{{ $result }}</p>
    @endif

    <button type="submit" wire:loading.attr="disabled">Send</button>
</form>
