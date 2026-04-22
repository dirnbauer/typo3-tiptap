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

Abbreviation plugin
===================

The shipped preset includes an abbreviation plugin:

..  code-block:: yaml

    editor:
      tiptap:
        config:
          plugins:
            - path: "@typo3-tiptap/tiptap/plugins/abbreviation.js"

The abbreviation plugin stores semantic ``<abbr>`` markup with ``title`` and ``data-tooltip`` attributes.

This plugin is useful when projects want semantic abbreviations in the output HTML instead of ad-hoc inline markup. It also gives integrators a stable way to attach frontend tooltip styling.

Language span plugin
====================

The shipped preset also includes a language span plugin:

..  code-block:: yaml

    editor:
      tiptap:
        config:
          plugins:
            - path: "@typo3-tiptap/tiptap/plugins/language.js"

The language plugin stores semantic ``<span lang="...">`` markup and mirrors the value to ``xml:lang``.

This plugin is useful for multilingual content models because it lets editors mark passages whose language differs from the surrounding text. That improves semantics and accessibility without requiring manual HTML editing.
