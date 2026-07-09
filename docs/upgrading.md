# Upgrading

This page tracks version-to-version upgrade notes. For the full list of changes in each release, see [CHANGELOG.md](../CHANGELOG.md).

## Upgrading between minor/patch versions

```bash
composer update henry-ht/laravel-bitwise-permission
php artisan vendor:publish --tag=bwp-migrations --force
php artisan migrate
```

Config changes, when they happen, are called out explicitly here. Compare your published `config/bitwise-permission.php` against the package's default after any upgrade that mentions new config keys, and merge in what's missing — publishing with `--force` will overwrite your customizations, so diff first:

```bash
php artisan vendor:publish --tag=bwp-config --force --dry-run
```

## General guidance

- Always back up your database before running new migrations in production.
- After upgrading, re-run `php artisan bwp:sync-routes --dry-run` to check whether any new named routes need registering.
- If you extended core models (`Role`, `Permission`, `Menu`, `Access`, `AppRoute`), re-check their signatures against the [source](https://github.com/henry-ht/laravel-bitwise-permission/tree/main/src/Models) after a major version bump.

## v1.0.0

Initial public release — no upgrade steps, this is the starting point.
