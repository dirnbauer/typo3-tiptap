============
Installation
============

Requirements
============

-  TYPO3 CMS 14 LTS
-  PHP 8.3 or newer

Composer
========

..  code-block:: bash

    composer require in2code/typo3-tiptap

The extension does not require ``EXT:rte_ckeditor``. It registers its own rich text resolver, preset, and backend link browser integration.

Optional workspace support
==========================

Install and enable ``EXT:workspaces`` if your editorial workflow uses staged content changes and approvals.
