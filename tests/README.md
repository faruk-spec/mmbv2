# MMB Platform Tests

This directory contains all tests for the MyMultiBranch platform.

## Directory Structure

- **Unit/** - Unit tests for individual classes and utilities
- **Integration/** - Integration tests for controllers and components
- **Feature/** - Feature tests for complete user workflows
- **coverage/** - Code coverage reports (generated)
- **results/** - Test results in JUnit XML format (generated)
- **bootstrap.php** - Test environment initialization

## Running Tests

```bash
# Install dependencies first
composer install

# Run all tests
composer test

# Run specific test suite
composer test:unit
composer test:integration

# Generate coverage report
composer test:coverage
```

## Writing Tests

All test classes should extend the base `TestCase` class defined in `bootstrap.php`.

Example:

```php
<?php

use PHPUnit\Framework\TestCase;
use Core\YourClass;

class YourClassTest extends TestCase
{
    public function testSomething()
    {
        $instance = new YourClass();
        $result = $instance->doSomething();
        
        $this->assertEquals('expected', $result);
    }
}
```

## Test Coverage

Current test coverage can be viewed by running:

```bash
composer test:coverage
```

Then open `tests/coverage/index.html` in your browser.

## Documentation

See [PHASE_12_TESTING_GUIDE.md](../PHASE_12_TESTING_GUIDE.md) for complete testing documentation.
