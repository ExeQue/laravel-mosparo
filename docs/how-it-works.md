<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# How it works

1. `@mosparoScript` loads mosparo's frontend. The box asks mosparo for a submit token as soon as it starts.
2. On submit, the box sends the form to mosparo, which checks it and hands back a validation token.
   - For a plain form, both tokens go along as `_mosparo_submitToken` and `_mosparo_validationToken`.
   - In Livewire, the box calls `updateMosparo()` with them.
3. The server sends the tokens, with hashes of the submitted fields, to mosparo and checks the signature of the answer.
4. The tokens are now spent. After a page reload the box starts over by itself. In Livewire, the check dispatches `mosparo-reset`, and the boxes on that component request a new submit token.
