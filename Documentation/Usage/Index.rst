=====
Usage
=====

Editors can use TipTap fields like standard TYPO3 rich text fields.

Workspace usage
===============

When ``EXT:workspaces`` is enabled, TipTap fields can be edited in draft workspaces. The custom link browser forwards the current record metadata so TYPO3 resolves the editor preset consistently for draft records as well.

..  warning::
    Files are not versioned per workspace. Do not overwrite files in place when preparing unpublished changes.

Abbreviations
=============

The default preset contains an abbreviation command. Select text, click :guilabel:`Abbreviation`, and enter the explanation text. The editor writes semantic ``<abbr>`` markup.

This is useful when editors need to explain acronyms and shortened terms without switching to source editing. It improves semantic output, supports assistive technology, and provides a clean basis for frontend tooltip rendering.

If the website should render styled hover tooltips, include the shipped stylesheet:

..  code-block:: typoscript

    page.includeCSS.tiptapAbbreviationTooltip = EXT:typo3_tiptap/Resources/Public/Css/AbbreviationTooltip.css

Language spans
==============

The default preset also contains a language command. Select a word or sentence, click :guilabel:`Language`, and enter a BCP 47 language tag such as ``en``, ``fr`` or ``de-AT``.

The editor writes semantic ``<span lang="...">`` markup and mirrors the value to ``xml:lang``.

This is useful for multilingual editorial content where single words, phrases, or quotations need a different language than the surrounding text. It improves semantic quality and helps screen readers pronounce those passages more accurately.
