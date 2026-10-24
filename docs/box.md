# The box

## The page head

Load mosparo's frontend in the `<head>` of every page with a form:

```blade
<head>
    ...
    @mosparoScript
</head>
```

## The form

Put the box inside the form. mosparo checks the fields of the form around it, so the box must be inside the `<form>` element:

```blade
<form method="post" action="/newsletter">
    @csrf
    <input name="email" type="email">

    @mosparo
    @mosparoError <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Subscribe</button>
</form>
```

`@mosparo` and `<x-mosparo />` render the same box. `@mosparoError` works like `@error`, with the spam check's message in `$message`. It finds the error both in a Livewire component and in a plain form post, and it closes with Laravel's own `@enderror`.

## Options

The component takes a few attributes:

```blade
<x-mosparo
    project="newsletter"
    id="newsletter-box"
    :options="['language' => 'de']"
    class="my-4"
/>
```

| Attribute | Default | |
| --- | --- | --- |
| `project` | the default project | Which [project](projects.md) the box belongs to. |
| `id` | a random id | The box element's id. |
| `options` | `[]` | [Options for mosparo's frontend](https://documentation.mosparo.io/docs/integration/custom/#options), such as `language`, `forceInvisible` or `inputFieldSelector`. Plain values only - callbacks cannot be passed. |
| anything else | | Added to the box element. |

`@mosparo` takes only the project, as in `@mosparo('newsletter')`. Use the component for the other attributes.

## Visible and invisible

Whether the box shows a checkbox or checks the form on submit without one is part of the project's design in mosparo. `'forceInvisible' => true` hides the checkbox of a visible project.

The box loads its project's stylesheet itself, so one `@mosparoScript` in the head serves boxes of every project on the same mosparo host.

## Pages that change

Boxes start wherever they appear:

- on the first page load;
- in parts of the page that Livewire adds later;
- on pages reached with `wire:navigate`;
- on pages restored with the back button.

The box shows nothing while mosparo is [disabled](switching-off.md).
