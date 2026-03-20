=============
Configuration
=============

The shipped preset is registered in :file:`ext_localconf.php`:

..  code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['default']
        = 'EXT:typo3_tiptap/Configuration/RTE/Full.yaml';

To use a project specific preset, register your own YAML file:

..  code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['my_custom_preset']
        = 'EXT:sitepackage/Configuration/RTE/TipTap.yaml';

..  code-block:: typoscript

    RTE.default.preset = my_custom_preset

The TipTap configuration lives below ``editor.tiptap.config`` and accepts plugin definitions and editor options such as ``contentCss``.

The shipped preset includes an abbreviation plugin:

..  code-block:: yaml

    editor:
      tiptap:
        config:
          plugins:
            - path: "@typo3-tiptap/tiptap/plugins/abbreviation.js"

The plugin stores semantic ``<abbr>`` markup with ``title`` and ``data-tooltip`` attributes.
