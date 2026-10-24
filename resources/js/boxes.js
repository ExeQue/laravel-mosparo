// Starts every mosparo box on the page: those in the first page, those a
// Livewire update or wire:navigate brings in later, and those restored by the
// back button. A box is a <div data-mosparo="{...}"> inside a <form>.
(() => {
    if (window.laravelMosparo) {
        window.laravelMosparo.start();

        return;
    }

    const componentOf = (box) => box.closest('[wire\\:id]')?.getAttribute('wire:id');

    function startBox(box) {
        if (box.mosparo || typeof mosparo === 'undefined') {
            return;
        }

        const config = JSON.parse(box.dataset.mosparo);

        // A page restored from the back-button cache keeps the old box's markup.
        box.replaceChildren();

        box.mosparo = new mosparo(box.id, config.host, config.uuid, config.publicKey, {
            loadCssResource: true,
            ...config.options,
            // Laravel trims submitted strings, so the box checks them trimmed too.
            onGetFieldValue: (field, value) => (typeof value === 'string' ? value.trim() : value),
            onCheckForm: (valid) => {
                const component = componentOf(box);

                if (!valid || !component) {
                    return;
                }

                window.Livewire?.find(component)?.dispatchSelf('mosparo-checked', {
                    submitToken: box.mosparo.getSubmitToken(),
                    validationToken: box.mosparo.getValidationToken(),
                });
            },
        });

        // Check before any other submit handler, such as Livewire's wire:submit, sends the form.
        const form = box.mosparo.formElement;

        if (form) {
            form.removeEventListener('submit', box.mosparo.onSubmit);
            form.addEventListener('submit', box.mosparo.onSubmit, true);
        }
    }

    function start() {
        document.querySelectorAll('[data-mosparo]').forEach(startBox);
    }

    // After a Livewire check the tokens are spent: the boxes of that component start over.
    window.addEventListener('mosparo-reset', (event) => {
        document.querySelectorAll('[data-mosparo]').forEach((box) => {
            if (!box.mosparo || (event.detail?.id && event.detail.id !== componentOf(box))) {
                return;
            }

            box.querySelectorAll('.mosparo__loader-container').forEach((loader) => loader.remove());
            box.mosparo.resetState();
            box.mosparo.requestSubmitToken();
        });
    });

    let scheduled = false;

    new MutationObserver(() => {
        if (scheduled) {
            return;
        }

        scheduled = true;
        queueMicrotask(() => {
            scheduled = false;
            start();
        });
    }).observe(document.documentElement, { childList: true, subtree: true });

    document.addEventListener('DOMContentLoaded', start);
    document.addEventListener('livewire:navigated', start);

    window.laravelMosparo = { start };
})();
