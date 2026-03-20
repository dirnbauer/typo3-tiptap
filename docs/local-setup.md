# Local Setup

## Requirements

- Docker
- DDEV

## Bootstrap

```sh
ddev start
ddev composer update
cd frontend && npm ci && npm run build && cd ..
```

## Quality Checks

```sh
ddev composer phpstan
ddev composer test:unit
```

## Demo Instance

- Backend: https://typo3-tiptap.ddev.site/typo3
- Username: `admin`
- Password: `John3:16`

## Assets and Database

Download project assets:

```sh
ddev pull assets
```

Push local changes back to the asset store:

```sh
ddev push assets
```

When testing workspace scenarios, enable `EXT:workspaces` in the local instance and verify draft editing with newly uploaded files instead of overwriting existing files in place.
