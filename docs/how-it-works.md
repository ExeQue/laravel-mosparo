# How it works

1. `@mosparoScript` loads mosparo's frontend and a small script that starts every box on the page. A box asks mosparo for a submit token as soon as it starts.
2. When the form is sent, the box first sends the form's fields to mosparo, which checks them and hands back a validation token. Strings are trimmed first, as Laravel trims them on the server.
   - In a plain form, both tokens go along in the post as `_mosparo_submitToken` and `_mosparo_validationToken`.
   - In Livewire, the box dispatches `mosparo-checked` with the tokens to its component, before the component's action runs.
3. The server sends the tokens to mosparo, with hashes of the submitted fields, and checks the signature of the answer and the fields that must have been checked.
4. The tokens are now spent.
   - After a plain post, the page reloads and the box starts again.
   - In Livewire, the check dispatches `mosparo-reset`, and the boxes of that component ask for a new submit token.
