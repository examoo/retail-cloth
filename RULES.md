# Project Coding Standards

This project adheres to strict coding standards using a **Service-Repository** pattern and **Reusable Components**.
These rules are enforced via `.cursorrules` and must be followed by any AI agent working on this codebase.

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

## 3. Architecture & Patterns (Strict)
- **Service Layer**: ALL business logic must reside in Service classes (e.g., `app/Services/OrderService.php`). Controllers should only handle request parsing and response formatting.
- **Form Requests**: NEVER validate data in the controller. Use dedicated Request classes (e.g., `StoreOrderRequest`).
- **DRY Principle**: No duplicate code. Extract shared logic into Traits, Services, or Helper functions.

## 4. Frontend Reusability (Strict)
- **Component Reuse**: Never use raw HTML elements for standard UI. Always use reusable components.
- **Inputs**: Use `TextInput.vue`, `InputLabel.vue`, etc. Do not write `<input class="...">` manually.
- **Composition**: Prefer Composition API with `<script setup>`.

## 5. Git Hooks (Husky)
- A `pre-commit` hook is configured to automatically run proper linters on staged files.
- If the linter fails, the commit will be rejected. Fix the errors and try again.
