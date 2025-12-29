# CerneCMS Testing Guide

This guide provides comprehensive information about the testing infrastructure and how to write and run tests for CerneCMS.

## Table of Contents

- [Overview](#overview)
- [Backend Testing (PHP)](#backend-testing-php)
- [Frontend Testing (Svelte)](#frontend-testing-svelte)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [CI/CD](#cicd)
- [Coverage Goals](#coverage-goals)

---

## Overview

CerneCMS uses a comprehensive testing strategy covering both PHP backend and Svelte frontend:

- **Backend**: PHPUnit for unit and integration tests
- **Frontend**: Vitest with Testing Library for component and unit tests
- **CI/CD**: GitHub Actions for automated testing on push and pull requests

---

## Backend Testing (PHP)

### Framework: PHPUnit

PHPUnit is the de facto standard for PHP unit testing, providing excellent IDE integration, mocking capabilities, and assertion libraries.

### Installation

PHPUnit is already installed as a dev dependency:

```bash
composer install
```

### Test Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── PageTest.php
│   │   ├── BlockTest.php
│   │   ├── MenuTest.php
│   │   ├── MenuItemTest.php
│   │   ├── SettingsTest.php
│   │   └── UserTest.php
│   ├── Services/
│   │   ├── BlockRendererTest.php
│   │   └── MenuServiceTest.php
│   └── Helpers/
│       ├── CacheTest.php
│       └── ViteTest.php
├── Integration/
│   ├── Controllers/
│   │   ├── ApiControllerTest.php
│   │   ├── AdminControllerTest.php
│   │   └── AuthControllerTest.php
│   └── Database/
│       └── DatabaseTest.php
├── bootstrap.php
└── phpunit.xml
```

### Test Configuration

The [`phpunit.xml`](../phpunit.xml) file configures PHPUnit:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">app</directory>
        </include>
    </source>
</phpunit>
```

### Test Bootstrap

The [`tests/bootstrap.php`](../tests/bootstrap.php) file sets up the test environment:

- Loads Composer autoloader
- Sets test environment variables
- Initializes Flight framework
- Creates in-memory SQLite database
- Sets up database schema

### Example Backend Test

```php
<?php

namespace tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use app\models\Page;

class PageTest extends TestCase
{
    public function testCreatePage()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test-page';
        $page->status = 'published';
        $page->save();

        $this->assertNotNull($page->id);
        $this->assertEquals('Test Page', $page->title);
    }

    public function testGetParentPage()
    {
        $parent = new Page();
        $parent->title = 'Parent Page';
        $parent->slug = 'parent';
        $parent->save();

        $child = new Page();
        $child->title = 'Child Page';
        $child->slug = 'child';
        $child->parent_id = $parent->id;
        $child->save();

        $retrievedChild = (new Page())->eq('id', $child->id)->find();
        $parentPage = $retrievedChild->getParent();

        $this->assertEquals('Parent Page', $parentPage->title);
    }
}
```

### Key Backend Test Files

1. **BlockRendererTest.php** - Tests Tiptap JSON to HTML conversion
2. **PageTest.php** - Tests page CRUD, hierarchy, and search
3. **MenuTest.php** - Tests menu creation, hierarchy, and primary menu
4. **MenuItemTest.php** - Tests menu item URL resolution and active state
5. **SettingsTest.php** - Tests settings CRUD operations
6. **MenuServiceTest.php** - Tests menu rendering and sidebar logic

---

## Frontend Testing (Svelte)

### Framework: Vitest + Testing Library

Vitest is fast, native to Vite, and provides excellent TypeScript support. Testing Library focuses on user behavior rather than implementation details.

### Installation

Vitest and testing libraries are already installed as dev dependencies:

```bash
pnpm install
```

### Test Structure

```
src/
├── tests/
│   ├── unit/
│   │   ├── stores/
│   │   │   └── editor.test.js
│   │   ├── utils/
│   │   │   └── image.test.js
│   │   └── editor/
│   │       ├── Calendar.test.js
│   │       ├── CTA.test.js
│   │       └── ImageUpload.test.js
│   ├── components/
│   │   ├── Editor.test.js
│   │   ├── ComponentToolbar.test.js
│   │   ├── MediaManager.test.js
│   │   ├── PageSettingsDrawer.test.js
│   │   ├── blocks/
│   │   │   ├── ImageBlock.test.js
│   │   │   └── ImageUploadBlock.test.js
│   │   └── admin/
│   │       ├── MenuEditor.test.js
│   │       ├── MenuManager.test.js
│   │       └── SiteSettings.test.js
│   └── setup.js
```

### Test Configuration

The [`vitest.config.js`](../vitest.config.js) file configures Vitest:

```javascript
import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
  plugins: [svelte()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/tests/setup.js'],
    globals: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'src/tests/',
        '**/*.d.ts',
        '**/*.config.*',
        '**/dist/**'
      ]
    }
  }
});
```

### Test Setup

The [`src/tests/setup.js`](../src/tests/setup.js) file sets up the test environment:

- Imports Testing Library matchers
- Mocks `window.matchMedia`
- Mocks `IntersectionObserver`
- Mocks `ResizeObserver`
- Mocks `localStorage` and `sessionStorage`

### Example Frontend Test

```javascript
import { describe, it, expect, beforeEach } from 'vitest';
import { editor } from '$lib/stores/editor.svelte.js';

describe('Editor Store', () => {
  beforeEach(() => {
    // Reset editor state before each test
    editor.set({
      content: [],
      selection: null
    });
  });

  it('initializes with empty content', () => {
    const state = editor.get();
    expect(state.content).toEqual([]);
    expect(state.selection).toBeNull();
  });

  it('adds a new block', () => {
    const newBlock = {
      type: 'paragraph',
      content: [
        { type: 'text', text: 'New paragraph' }
      ]
    };

    editor.update(state => ({
      ...state,
      content: [...state.content, newBlock]
    }));

    const currentState = editor.get();
    expect(currentState.content).toHaveLength(1);
    expect(currentState.content[0]).toEqual(newBlock);
  });
});
```

### Key Frontend Test Files

1. **editor.test.js** - Tests editor store state management
2. **ComponentToolbar.test.js** - Tests block insertion toolbar
3. **ImageBlock.test.js** - Tests image block component
4. **MediaManager.test.js** - Tests media upload and selection
5. **MenuEditor.test.js** - Tests menu editing interface

---

## Running Tests

### Backend Tests

```bash
# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Run with coverage report
composer test:coverage
```

### Frontend Tests

```bash
# Run all tests in watch mode
pnpm test

# Run all tests once
pnpm test:run

# Run only unit tests
pnpm test:unit

# Run only component tests
pnpm test:components

# Run with coverage report
pnpm test:coverage
```

### Running Specific Tests

**Backend**:
```bash
# Run specific test class
vendor/bin/phpunit tests/Unit/Services/BlockRendererTest.php

# Run specific test method
vendor/bin/phpunit --filter testRenderValidDocument
```

**Frontend**:
```bash
# Run specific test file
pnpm test src/tests/unit/stores/editor.test.js

# Run tests matching pattern
pnpm test --grep "Editor Store"
```

---

## Writing Tests

### Backend Testing Best Practices

1. **Use dependency injection** to make code testable
2. **Mock external dependencies** (database, APIs, file system)
3. **Test edge cases** (null values, empty arrays, invalid input)
4. **Use data providers** for testing multiple scenarios
5. **Keep tests independent** - no test should rely on another

### Frontend Testing Best Practices

1. **Test user behavior**, not implementation details
2. **Use Testing Library queries** (getByRole, getByText) over CSS selectors
3. **Mock API calls** using vi.fn()
4. **Test accessibility** (ARIA roles, labels)
5. **Test error states** and loading states

### Test Naming Conventions

**Backend**:
- Test class: `ClassNameTest.php`
- Test method: `testMethodName()`
- Data provider: `methodNameDataProvider()`

**Frontend**:
- Test file: `filename.test.js`
- Test suite: `describe('ComponentName', ...)`
- Test case: `it('should do something', ...)`

---

## CI/CD

### GitHub Actions Workflow

The [`.github/workflows/test.yml`](../.github/workflows/test.yml) file defines the CI/CD pipeline:

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo, pdo_sqlite
        coverage: xdebug
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    - name: Run PHPUnit tests
      run: composer test
    - name: Upload coverage
      uses: codecov/codecov-action@v3

  frontend-tests:
    runs-on: ubuntu-latest
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
    - name: Install pnpm
      uses: pnpm/action-setup@v2
      with:
        version: 8
    - name: Install dependencies
      run: pnpm install
    - name: Run Vitest tests
      run: pnpm test:run
    - name: Upload coverage
      uses: codecov/codecov-action@v3
```

### When Tests Run

- **On push**: Tests run on every push to `main` or `develop` branches
- **On pull request**: Tests run on every PR targeting `main` or `develop` branches
- **Coverage**: Coverage reports are uploaded to Codecov

---

## Coverage Goals

### Backend (PHP)
- **Target**: 80%+ code coverage
- **Critical paths**: 90%+ coverage
  - BlockRenderer service
  - Page model
  - Menu model
  - ApiController

### Frontend (Svelte)
- **Target**: 75%+ code coverage
- **Critical paths**: 85%+ coverage
  - Editor store
  - Editor component
  - Block components
  - Component toolbar

### Viewing Coverage Reports

**Backend**:
```bash
composer test:coverage
# Open coverage/index.html in browser
```

**Frontend**:
```bash
pnpm test:coverage
# Open coverage/index.html in browser
```

---

## Troubleshooting

### Common Issues

**Backend Tests**:
- **Database errors**: Ensure SQLite extension is enabled
- **Class not found**: Run `composer dump-autoload`
- **Flight errors**: Ensure Flight is properly initialized in bootstrap

**Frontend Tests**:
- **Module not found**: Run `pnpm install`
- **Svelte errors**: Ensure `@sveltejs/vite-plugin-svelte` is installed
- **jsdom errors**: Check setup.js for proper mocks

### Debugging Tests

**Backend**:
```bash
# Run with verbose output
vendor/bin/phpunit --verbose

# Run with debug output
vendor/bin/phpunit --debug
```

**Frontend**:
```bash
# Run with UI mode
pnpm test --ui

# Run with inspector
pnpm test --inspect
```

---

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Vitest Documentation](https://vitest.dev/)
- [Testing Library Documentation](https://testing-library.com/)
- [Svelte Testing Library](https://testing-library.com/docs/svelte-testing-library/intro)

---

## Contributing Tests

When adding new features to CerneCMS:

1. Write tests for the new functionality
2. Ensure all tests pass
3. Maintain or improve coverage percentages
4. Update this documentation if needed

Happy testing! 🧪
