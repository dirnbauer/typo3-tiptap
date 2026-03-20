=========
Developer
=========

Frontend build
==============

The web component source lives in :file:`frontend/`.

..  code-block:: bash

    cd frontend
    npm ci
    npm run build

Quality checks
==============

Run the project checks inside DDEV:

..  code-block:: bash

    ddev composer phpstan
    ddev composer test:unit

Custom TipTap plugins should be shipped by your site package and registered through its JavaScript module configuration.
