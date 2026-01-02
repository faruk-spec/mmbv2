# Phase 12: Testing & Quality Assurance - Implementation Guide

## Overview
This document describes the testing infrastructure and quality assurance processes implemented in Phase 12 of the MMB platform.

## Important: Database Configuration
All tests follow the database-agnostic approach:
- Tests use in-memory SQLite or configured test database
- No hardcoded database names or credentials in tests
- Test configuration in `tests/bootstrap.php`

## Completed Features

### 1. PHPUnit Testing Framework

#### Setup Files
- **`phpunit.xml`** - PHPUnit configuration with test suites and coverage settings
- **`tests/bootstrap.php`** - Test environment initialization and helper class
- **`composer.json`** - Dependencies including PHPUnit, PHPStan, and PHP_CodeSniffer

#### Test Directory Structure
```
tests/
├── Unit/           # Unit tests for individual classes
├── Integration/    # Integration tests for controllers
├── Feature/        # Feature tests for complete workflows
├── coverage/       # Code coverage reports
├── results/        # Test results (JUnit XML)
└── bootstrap.php   # Test bootstrap file
```

### 2. Unit Tests

#### CacheTest.php
Tests for the Cache utility class:
- ✅ Set and get operations
- ✅ Default value handling
- ✅ Key existence checking (has)
- ✅ Cache deletion
- ✅ TTL and expiration
- ✅ Remember pattern
- ✅ Cache clearing
- ✅ Statistics retrieval
- ✅ Complex data types (arrays, objects, nested data)
- ✅ Cleanup of expired entries

#### QRCodeTest.php
Tests for the QRCode utility class:
- ✅ QR code generation
- ✅ Different size handling
- ✅ Data URL generation
- ✅ File saving
- ✅ SVG generation
- ✅ Share link generation
- ✅ URL encoding validation

#### AnalyticsTest.php
Tests for the Analytics tracking class:
- ✅ Event tracking
- ✅ Download tracking
- ✅ Page view tracking
- ✅ Summary generation
- ✅ Download statistics
- ✅ Report generation (HTML, CSV, JSON)
- ✅ Queue flushing
- ✅ Multiple event handling

### 3. Test Helper Class

The `TestCase` base class in `bootstrap.php` provides:
- Automatic cache cleanup before/after tests
- Temporary file creation and cleanup
- Test environment setup
- Database configuration for tests

## Installation

### Install Dependencies

```bash
# Install Composer dependencies
composer install

# Or install specific tools
composer require --dev phpunit/phpunit
composer require --dev phpstan/phpstan
composer require --dev squizlabs/php_codesniffer
```

## Running Tests

### All Tests
```bash
# Run all tests
composer test
# or
./vendor/bin/phpunit

# Run with verbose output
./vendor/bin/phpunit --verbose
```

### Specific Test Suites
```bash
# Run only unit tests
composer test:unit
# or
./vendor/bin/phpunit --testsuite="Unit Tests"

# Run only integration tests
composer test:integration
# or
./vendor/bin/phpunit --testsuite="Integration Tests"

# Run only feature tests
./vendor/bin/phpunit --testsuite="Feature Tests"
```

### Specific Test Files
```bash
# Run a specific test file
./vendor/bin/phpunit tests/Unit/CacheTest.php

# Run a specific test method
./vendor/bin/phpunit --filter testSetAndGet tests/Unit/CacheTest.php
```

### Code Coverage
```bash
# Generate HTML coverage report
composer test:coverage
# or
./vendor/bin/phpunit --coverage-html tests/coverage

# View coverage report
# Open tests/coverage/index.html in a browser

# Generate text coverage summary
./vendor/bin/phpunit --coverage-text
```

## Static Analysis

### PHPStan
Run static analysis to detect potential bugs:

```bash
# Analyze code
composer analyze
# or
./vendor/bin/phpstan analyse core controllers --level=5

# Use stricter level
./vendor/bin/phpstan analyse core controllers --level=8
```

### PHP_CodeSniffer
Check code style compliance:

```bash
# Check code style
composer lint
# or
./vendor/bin/phpcs --standard=PSR12 core controllers

# Auto-fix code style issues
./vendor/bin/phpcbf --standard=PSR12 core controllers
```

## Writing Tests

### Unit Test Example

```php
<?php

use PHPUnit\Framework\TestCase;
use Core\YourClass;

class YourClassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set up test fixtures
    }
    
    protected function tearDown(): void
    {
        // Clean up after test
        parent::tearDown();
    }
    
    public function testSomeFeature()
    {
        $instance = new YourClass();
        $result = $instance->someMethod('input');
        
        $this->assertEquals('expected', $result);
    }
    
    public function testExceptionHandling()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $instance = new YourClass();
        $instance->methodThatThrows();
    }
}
```

### Integration Test Example

```php
<?php

use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    public function testUserRegistration()
    {
        // Simulate POST request
        $_POST = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $controller = new \Controllers\AuthController();
        $result = $controller->register();
        
        // Assert user was created
        $this->assertNotNull($result);
    }
}
```

## Test Coverage Goals

### Coverage Targets
- **Overall**: 70%+ coverage
- **Core utilities**: 80%+ coverage
- **Controllers**: 60%+ coverage
- **Critical paths**: 90%+ coverage

### Current Coverage
Run `composer test:coverage` to see current coverage statistics.

## Continuous Integration

### GitHub Actions (Recommended)

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        extensions: mbstring, pdo, pdo_mysql
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run tests
      run: composer test
      
    - name: Run static analysis
      run: composer analyze
      
    - name: Check code style
      run: composer lint
```

## Security Testing

### CSRF Protection Testing
```php
public function testCsrfProtection()
{
    // Test that forms require CSRF token
    $_POST = ['data' => 'value'];
    // Should fail without token
    
    $_POST['csrf_token'] = 'valid_token';
    // Should succeed with token
}
```

### XSS Prevention Testing
```php
public function testXssPrevention()
{
    $input = '<script>alert("xss")</script>';
    $sanitized = Security::sanitize($input);
    
    $this->assertStringNotContainsString('<script>', $sanitized);
}
```

### SQL Injection Testing
```php
public function testSqlInjectionPrevention()
{
    $maliciousInput = "1' OR '1'='1";
    
    // Should use prepared statements
    $result = $db->fetch("SELECT * FROM users WHERE id = ?", [$maliciousInput]);
    
    // Should not return all users
    $this->assertNotNull($result);
}
```

## Performance Testing

### Load Testing with Apache JMeter
1. Install JMeter
2. Create test plan for critical endpoints
3. Run load tests with multiple users
4. Analyze response times and error rates

### Database Query Profiling
```php
public function testQueryPerformance()
{
    $startTime = microtime(true);
    
    // Execute query
    $result = $db->fetchAll("SELECT * FROM large_table");
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    // Assert query completes in reasonable time
    $this->assertLessThan(0.1, $duration, 'Query should complete in < 100ms');
}
```

## Best Practices

### Test Naming
- Use descriptive method names: `testUserCanLoginWithValidCredentials()`
- Group related tests: `testValidInput()`, `testInvalidInput()`, `testEdgeCases()`

### Test Organization
- One assertion per test when possible
- Use data providers for testing multiple inputs
- Keep tests independent (don't rely on test execution order)

### Test Data
- Use factories or fixtures for test data
- Clean up test data in `tearDown()`
- Use in-memory databases for speed

### Mocking
- Mock external services (email, payment gateways)
- Mock time-dependent operations
- Use PHPUnit's mock objects for dependencies

## Troubleshooting

### Tests Failing
1. Check test environment setup in `bootstrap.php`
2. Verify database configuration for tests
3. Clear cache: `php -r "require 'core/Cache.php'; Core\Cache::clear();"`
4. Check file permissions on storage directories

### Coverage Not Generating
1. Install Xdebug: `pecl install xdebug`
2. Enable in php.ini: `zend_extension=xdebug.so`
3. Or use PCOV: `pecl install pcov`

### Slow Tests
1. Use in-memory database for tests
2. Mock external API calls
3. Run tests in parallel (requires paratest)
4. Profile slow tests with `--log-junit`

## Adding New Tests

### For New Features
1. Write tests first (TDD approach)
2. Create test file in appropriate directory
3. Extend `TestCase` base class
4. Run tests to verify they pass
5. Check coverage with `composer test:coverage`

### For Bug Fixes
1. Write a test that reproduces the bug
2. Verify test fails
3. Fix the bug
4. Verify test passes
5. Commit test with fix

## Next Steps

After Phase 12:
- [ ] Add integration tests for all controllers
- [ ] Add feature tests for user workflows
- [ ] Set up CI/CD pipeline
- [ ] Implement E2E tests with Selenium/Playwright
- [ ] Add API tests
- [ ] Set up automated security scanning
- [ ] Configure code quality metrics
- [ ] Add performance benchmarks

## Support

For issues or questions:
1. Check PHPUnit documentation: https://phpunit.de/
2. Review test output for specific errors
3. Check logs in `/storage/logs/`
4. Verify all dependencies are installed: `composer install`
5. Run with verbose flag: `./vendor/bin/phpunit --verbose`
