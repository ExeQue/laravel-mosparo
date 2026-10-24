@props([
    'project' => null,
    'id' => 'mosparo-box',
    'options' => [],
])
@php($mosparo = app(\ExeQue\Mosparo\Mosparo::class)->project($project))
@if($mosparo->enabled())
{{-- The box sits inside a <form> and checks that form's fields. In a Livewire component it hands its tokens to updateMosparo() (InteractsWithMosparo). --}}
<div wire:ignore id="{{ $id }}" data-mosparo-box {{ $attributes }}></div>
<script>
    (() => {
        const box = document.getElementById(@js($id));

        {{-- One listener per page. After a check has spent the tokens of a Livewire component, the boxes inside that component reset and request a new submit token. --}}
        if (!window.mosparoResetBound) {
            window.mosparoResetBound = true;
            window.addEventListener(@js(\ExeQue\Mosparo\Mosparo::RESET_EVENT), (event) => {
                document.querySelectorAll('[data-mosparo-box]').forEach((element) => {
                    const component = element.closest('[wire\\:id]')?.getAttribute('wire:id');

                    if (event.detail?.id && event.detail.id !== component) {
                        return;
                    }

                    element.mosparo?.resetState();
                    element.mosparo?.requestSubmitToken();
                });
            });
        }

        {{-- Each box keeps its mosparo instance on its own element, so a box on a page reached with wire:navigate starts afresh. --}}
        function start() {
            if (!box || box.mosparo || typeof mosparo === 'undefined') {
                return;
            }

            box.mosparo = new mosparo(box.id, @js($mosparo->host()), @js($mosparo->uuid()), @js($mosparo->publicKey()), {
                ...@js((object) $options),
                onCheckForm: () => {
                    const component = box.closest('[wire\\:id]')?.getAttribute('wire:id');

                    if (!component) {
                        return;
                    }

                    window.Livewire.find(component).call('updateMosparo', {
                        submitToken: box.mosparo.getSubmitToken(),
                        validationToken: box.mosparo.getValidationToken(),
                    });
                },
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }

        window.addEventListener('load', start);
    })();
</script>
@endif
