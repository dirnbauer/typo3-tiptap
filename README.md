# TYPO3 TipTap Editor

TipTap integration for TYPO3 CMS 14. The extension ships a default preset, a custom backend FormEngine element, and a link browser integration that preserves the active record context for staged editing workflows.

The development of this extension was funded by the [TYPO3 Association](https://typo3.org) through the [Community Ideas program](https://typo3.org/article/members-have-selected-five-ideas-to-be-funded-in-quarter-3-2025).

![Screenshot of the TipTap Editor Integration in TYPO3](docs/images/example-1.webp)

## Requirements

- TYPO3 CMS `14 LTS`
- PHP `8.3+`
- `EXT:workspaces` if you want staged editorial workflows

## Installation

```bash
composer require in2code/typo3-tiptap
```

The extension registers the shipped preset automatically:

```php
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['default'] = 'EXT:typo3_tiptap/Configuration/RTE/Full.yaml';
```

If your project already uses custom presets, point the preset to your own YAML file instead.

You do not need `EXT:rte_ckeditor` for this extension itself. TipTap provides its own FormEngine integration, preset, and link browser route.

## TYPO3 14 and Workspaces

This release targets TYPO3 v14 only. The backend integration forwards the current record metadata, including the configured RTE preset, to the custom link browser so edited draft records behave consistently in workspace-enabled editing flows.

TipTap fields work inside TYPO3 workspaces, but TYPO3's standard file handling limitation still applies: physical FAL files are not versioned per workspace. When editors prepare unpublished changes, they should upload new files instead of overwriting existing assets.

## Configuration

### Understanding the Architecture

TipTap uses a plugin-based architecture where functionality is added through plugins. Like CKEditor, TipTap is configured using YAML files placed in your site package at `Configuration/RTE/`.

The extension ships with a default configuration that includes commonly used features. You only need to create a custom configuration if you want to modify the toolbar, add custom plugins, or adjust the editor behavior.

### Configuring the Toolbar

Each plugin can have its own configuration options that are validated in the frontend. Below is a complete example showing all available plugins. You can enable or disable any plugin by adding or removing its entry:

```yaml
editor:
    tiptap:
        config:
            plugins:
                - path: '@typo3-tiptap/tiptap/plugins/headings.js'
                  config: { levels: [1, 2, 3, 4, 5, 6] }
                - path: '@typo3-tiptap/tiptap/plugins/history.js'
                  config: { types: [ 'undo', 'redo' ] }
                - path: '@typo3-tiptap/tiptap/plugins/bold.js'
                - path: '@typo3-tiptap/tiptap/plugins/underline.js'
                - path: '@typo3-tiptap/tiptap/plugins/italic.js'
                - path: '@typo3-tiptap/tiptap/plugins/strikethrough.js'
                - path: '@typo3-tiptap/tiptap/plugins/link.js'
                - path: '@typo3-tiptap/tiptap/plugins/list.js'
                  config:
                      types: [ 'ordered', 'bullet' ]
                - path: '@typo3-tiptap/tiptap/plugins/blockquote.js'
                - path: '@typo3-tiptap/tiptap/plugins/table.js'
                  config: { defaultRows: 3, defaultCols: 3, withHeaderRow: false }
                - path: '@typo3-tiptap/tiptap/plugins/justify.js'
                - path: '@typo3-tiptap/tiptap/plugins/source.js'
                - path: '@typo3-tiptap/tiptap/plugins/styles.js'
                  config:
                      styles:
                          - { name: 'Orange Title H2', element: 'h2', classes: 'orange' }
                          - { name: 'Orange Title H3', element: 'h3', classes: 'orange' }
                          - { name: 'Paragraph Blue', element: 'p', classes: 'paragraph-blue' }
                          - { name: 'Link Green', element: 'a', classes: 'link-green' }

```

### Loading Custom CSS

You can load additional CSS files for the editor content using the `contentCss` option:

```yaml
editor:
  tiptap:
    config:
      contentCss:
        - 'EXT:sitepackage/Resources/Public/Css/content.css'
      plugins:
        - path: '@typo3-tiptap/tiptap/plugins/headings.js'
          config: { levels: [1, 2, 3, 4, 5, 6] }
        - path: '@typo3-tiptap/tiptap/plugins/history.js'
          config: { types: ['undo', 'redo'] }
        - path: '@typo3-tiptap/tiptap/plugins/bold.js'
        - path: '@typo3-tiptap/tiptap/plugins/underline.js'
        - path: '@typo3-tiptap/tiptap/plugins/italic.js'
        - path: '@typo3-tiptap/tiptap/plugins/strikethrough.js'
        - path: '@typo3-tiptap/tiptap/plugins/link.js'
        - path: '@typo3-tiptap/tiptap/plugins/abbreviation.js'
        - path: '@typo3-tiptap/tiptap/plugins/language.js'
        - path: '@typo3-tiptap/tiptap/plugins/list.js'
          config:
            types: ['ordered', 'bullet']
```

To use a custom preset:

```php
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['my_custom_preset']
    = 'EXT:sitepackage/Configuration/RTE/TipTap.yaml';
```

```typoscript
RTE.default.preset = my_custom_preset
```

## Custom Plugins

Register your site package JavaScript module path in the site package's `Configuration/JavaScriptModules.php`, then reference the plugin in the YAML preset:

```php
return [
    'imports' => [
        '@site-package/tiptap/' => 'EXT:site_package/Resources/Public/JavaScript/TipTap/',
    ],
];
```

```yaml
editor:
  tiptap:
    config:
      plugins:
        - path: '@site-package/tiptap/example.js'
          config:
            additionalClass: 'my-example-class'
```

Example plugin:

```js
import {
  defineTipTapPlugin,
  parseTipTapPluginYamlConfiguration,
} from '@typo3-tiptap/tiptap/index.js'

export default function (unsafeConfig) {
  const config = parseTipTapPluginYamlConfiguration({
    pluginId: 'cookie',
    config: unsafeConfig,
    getValidationSchema: z => z.object({
      additionalClass: z.string(),
    }),
  })

  return defineTipTapPlugin({
    // Optional: Add custom TipTap extensions here if needed
    extensions: [],

    // Commands define buttons added to the toolbar or bubble menu
    commands: [
      {
        id: 'cookie',
        label: 'Add cookie',
        iconIdentifier: 'icon-cookie',
        position: {
          toolbarGroupId: 'general',
          bubbleMenuGroupId: false,
        },
        onExecute: ({ editor }) => {
          editor.commands.setCookieButton({
            text: 'Accept Cookies',
            class: config.additionalClass,
          })
        },
      },
    ],
  })
}
```

## FAQ

### `#1755159351 MissingEditorConfigurationException`

The selected RTE preset does not provide `editor.tiptap.config`. Point the preset either to the shipped configuration or to a site package YAML file that contains the TipTap configuration block.

### Abbreviations and tooltips

The shipped preset now includes an abbreviation button. Select text, trigger `Abbreviation`, and enter the explanation text. The editor stores semantic `<abbr>` markup with both `title` and `data-tooltip` attributes.

For frontend tooltip styling, include:

```typoscript
page.includeCSS.tiptapAbbreviationTooltip = EXT:typo3_tiptap/Resources/Public/Css/AbbreviationTooltip.css
```

### Language spans

The shipped preset also includes a `Language` button for marking words or sentences with a language tag. It stores semantic `<span lang="…">` markup and also writes `xml:lang` for XHTML-oriented consumers.

## Documentation

- Project documentation: [Documentation/Index.rst](Documentation/Index.rst)
- Local development: [docs/local-setup.md](docs/local-setup.md)

## Credits

The extension is maintained by [in2code](https://www.in2code.de/). Their sustained open source engagement in the TYPO3 ecosystem, especially around practical editor integrations and community-funded work, deserves explicit professional recognition and thanks.
