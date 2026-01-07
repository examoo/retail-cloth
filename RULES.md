# Project Coding Standards

This project adheres to strict coding standards to ensure high-level quality and maintainability.

## 1. Backend (Laravel/PHP)
- **Style Guide**: We follow PSR-12 via **Laravel Pint**.
- **Static Analysis**: We use **PHPStan** (Level 5). All code must pass static analysis.
- **Strict Types**: New files should use `declare(strict_types=1);`.

### How to run:
```bash
# Fix styling
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse
```

## 2. Frontend (Vue 3/JS)
- **Framework**: Vue 3 (Composition API) + Vite.
- **Linter**: ESLint with Vue recommended rules.
- **Formatter**: Prettier.
- **Style**: Tailwind CSS.

### How to run:
```bash
# Lint and fix
npm run lint -- --fix
```

## 3. Git Hooks (Husky)
- A `pre-commit` hook is configured to automatically run proper linters on staged files.
- If the linter fails, the commit will be rejected. Fix the errors and try again.
