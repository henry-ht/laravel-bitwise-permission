# Contributing

Contributions are welcome and will be fully credited. This guide keeps things fast for you and consistent for everyone else.

## Before you start

For anything beyond a small fix, please open an issue first to discuss what you'd like to change. It saves everyone from rework, especially for anything touching the permission-resolution core.

## Pull requests

- **One change per pull request.** Smaller PRs are reviewed and merged faster.
- **Add or update tests** for any behavioral change. Pull requests without tests for new functionality won't be merged.
- **Update the docs** (`README.md`, `README.es.md`, or the relevant file under `docs/`) whenever a change affects public behavior, config keys, or method signatures.
- **Follow the existing code style.** Run the checks below before pushing.
- **Write a clear commit message and PR description** — what changed, and why.

## Setting up

```bash
git clone https://github.com/henry-ht/laravel-bitwise-permission.git
cd laravel-bitwise-permission
composer install
```

## Running the test suite

```bash
composer test
```

The package is tested against Orchestra Testbench with Pest. New features should include feature or unit tests under `tests/`, following the structure of the existing suite.

## Code style

This project follows PSR-12. If you have [Laravel Pint](https://github.com/laravel/pint) available:

```bash
vendor/bin/pint
```

## Reporting bugs

Great bug reports include:

- A clear, specific title.
- Package version, PHP version, and Laravel version.
- Steps to reproduce — ideally a minimal reproduction repo or a failing test.
- What you expected to happen, and what happened instead.

## Security vulnerabilities

Please do **not** open a public issue for security vulnerabilities. See [SECURITY.md](SECURITY.md) instead.

## Code of Conduct

By participating in this project, you agree to abide by the [Code of Conduct](CODE_OF_CONDUCT.md).

Thank you for helping make this package better.
