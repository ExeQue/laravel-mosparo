// Starts every mosparo box on the page: those in the first page, those a
// Livewire update or wire:navigate brings in later, and those restored by the
// back button. A box is a <div data-mosparo="{...}"> inside a <form>.
(() => {
    if (window.laravelMosparo) {
        window.laravelMosparo.start();

        return;
    }

    const started = new Set();
    const componentOf = (box) => box.closest('[wire\\:id]')?.getAttribute('wire:id');

    // Older versions of mosparo's frontend have no getters for the tokens, and a
    // browser may still have one cached; the hidden fields hold them in every version.
    const tokensOf = (instance) => ({
        submitToken: instance.getSubmitToken?.() ?? instance.submitTokenElement?.value ?? '',
        validationToken: instance.getValidationToken?.() ?? instance.validationTokenElement?.value ?? '',
    });

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
            // The server trims submitted strings, so the box checks them trimmed too.
            onGetFieldValue: (field, value) => (typeof value === 'string' ? value.trim() : value),
            onCheckForm: (valid) => {
                const component = componentOf(box);

                if (!valid || !component) {
                    return;
                }

                window.Livewire?.find(component)?.dispatchSelf('mosparo-checked', tokensOf(box.mosparo));
            },
        });

        // mosparo checks the form before any other submit handler, such as
        // Livewire's wire:submit, sends it, and ignores a second submit while
        // a check is running.
        box.onSubmit = (event) => {
            if (box.mosparo.containerElement?.classList.contains('mosparo__loading')) {
                event.preventDefault();
                event.stopImmediatePropagation();

                return;
            }

            box.mosparo.onSubmit(event);
        };

        box.mosparo.formElement?.removeEventListener('submit', box.mosparo.onSubmit);
        box.mosparo.formElement?.addEventListener('submit', box.onSubmit, true);
        started.add(box);
    }

    // A box Livewire took out of the page stops checking its form.
    function stopRemovedBoxes() {
        started.forEach((box) => {
            if (box.isConnected) {
                return;
            }

            box.mosparo.formElement?.removeEventListener('submit', box.onSubmit, true);
            started.delete(box);
        });
    }

    function start() {
        stopRemovedBoxes();
        document.querySelectorAll('[data-mosparo]').forEach(startBox);
    }

    // Spent tokens: the box forgets them and asks mosparo for a new submit token.
    function reset(box) {
        box.querySelectorAll('.mosparo__loader-container').forEach((loader) => loader.remove());
        box.mosparo.resetState();
        box.mosparo.submitTokenElement.value = '';
        box.mosparo.requestSubmitToken();
    }

    // After a Livewire check, the boxes of that component start over.
    window.addEventListener('mosparo-reset', (event) => {
        started.forEach((box) => {
            if (!event.detail?.id || event.detail.id === componentOf(box)) {
                reset(box);
            }
        });
    });

    // A page from the back/forward cache comes back with the tokens it already spent.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            started.forEach(reset);
        }
    });

    // mosparo's frontend script, loaded later on a page reached with wire:navigate.
    document.addEventListener('load', (event) => {
        if (event.target.tagName === 'SCRIPT' && event.target.src.includes('/build/mosparo-frontend.js')) {
            start();
        }
    }, true);

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
