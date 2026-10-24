<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# The box

Load mosparo's stylesheet and frontend script in the page head:

```blade
<head>
    ...
    @mosparoScript
</head>
```

Then put the box inside the form. mosparo checks the fields of the form around it, so it must be a descendant of the `<form>`:

```blade
<form method="post" action="/newsletter">
    @csrf
    <input name="email" type="email">

    @mosparo

    <button type="submit">Subscribe</button>
</form>
```

`@mosparo` and `<x-mosparo />` render the same thing. The component takes a few options:

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
| `project` | the default project | Which configured project the box belongs to. |
| `id` | `mosparo-box` | The box element's id. Give each box on a page its own. |
| `options` | `[]` | [Options for mosparo's frontend](https://documentation.mosparo.io/docs/integration/custom/#options), such as `language`, `designMode` or `inputFieldSelector`. |
| anything else | | Passed on to the box element. |

The box shows nothing while mosparo is [switched off](switching-off.md).
