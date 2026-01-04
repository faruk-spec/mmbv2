# Mail Hosting SaaS Platform - Testing Guide

## Overview

This directory contains comprehensive test suites for the mail hosting SaaS platform. The testing framework covers unit tests, integration tests, API tests, and end-to-end tests.

## Test Structure

```
tests/
├── Unit/                    # Unit tests for individual components
│   ├── Controllers/         # Controller tests
│   ├── Workers/            # Background worker tests
│   ├── Models/             # Model/database tests
│   └── Helpers/            # Helper function tests
├── Integration/            # Integration tests
│   ├── Payment/           # Payment gateway integration
│   ├── Email/             # Email sending/receiving
│   └── API/               # API endpoint tests
├── Feature/               # Feature tests (end-to-end)
│   ├── Subscription/      # Subscription flow tests
│   ├── Webmail/          # Webmail interface tests
│   └── Admin/            # Admin panel tests
└── TestCase.php          # Base test class

## Running Tests

### All Tests
```bash
./vendor/bin/phpunit
```

### Unit Tests Only
```bash
./vendor/bin/phpunit --testsuite Unit
```

### Integration Tests Only
```bash
./vendor/bin/phpunit --testsuite Integration
```

### Feature Tests Only
```bash
./vendor/bin/phpunit --testsuite Feature
```

### Specific Test File
```bash
./vendor/bin/phpunit tests/Unit/Controllers/SubscriberControllerTest.php
```

### With Coverage Report
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Test Database

Tests use a separate test database to avoid affecting production data.

### Configuration

Set up `.env.testing` file:
```env
DB_HOST=localhost
DB_DATABASE=mail_test
DB_USERNAME=root
DB_PASSWORD=
```

### Setup Test Database
```bash
mysql -u root -p -e "CREATE DATABASE mail_test;"
mysql -u root -p mail_test < projects/mail/schema.sql
```

## Writing Tests

### Unit Test Example

```php
<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;

class SubscriberControllerTest extends TestCase
{
    public function testDashboardLoadsSuccessfully()
    {
        // Arrange
        $subscriberId = $this->createTestSubscriber();
        
        // Act
        $response = $this->get("/projects/mail/subscriber/dashboard?subscriber_id={$subscriberId}");
        
        // Assert
        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('Dashboard', $response['body']);
    }
}
```

### Integration Test Example

```php
<?php

namespace Tests\Integration\Email;

use Tests\TestCase;

class EmailSendingTest extends TestCase
{
    public function testEmailQueueProcessing()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        $this->queueEmail($mailboxId, 'test@example.com', 'Test Subject', 'Test Body');
        
        // Act
        $processor = new QueueProcessor();
        $processor->processBatch();
        
        // Assert
        $this->assertEmailWasSent('test@example.com', 'Test Subject');
    }
}
```

## Test Coverage Goals

- **Unit Tests**: 80% code coverage minimum
- **Integration Tests**: All critical paths covered
- **Feature Tests**: All user workflows covered
- **API Tests**: All endpoints tested

## Continuous Integration

Tests run automatically on:
- Pull request creation
- Push to main branch
- Scheduled daily runs

## Mocking

Use PHPUnit mocking for external dependencies:

```php
// Mock payment gateway
$stripeMock = $this->createMock(StripeGateway::class);
$stripeMock->method('createCheckout')->willReturn(['id' => 'sess_123']);
```

## Test Data

Test data fixtures are located in `tests/fixtures/`:
- `subscribers.json` - Test subscriber data
- `mailboxes.json` - Test mailbox data
- `emails.json` - Test email messages

## Assertions

Common assertions used:
- `assertEquals()` - Value equality
- `assertStringContainsString()` - String contains
- `assertDatabaseHas()` - Database record exists
- `assertEmailSent()` - Email was sent
- `assertRedirect()` - HTTP redirect occurred

## Best Practices

1. **Arrange-Act-Assert Pattern**: Structure tests clearly
2. **Isolation**: Each test should be independent
3. **Cleanup**: Reset database state after each test
4. **Descriptive Names**: Test names should describe what they test
5. **Fast Tests**: Keep tests fast by mocking external services
6. **Edge Cases**: Test boundary conditions and error cases

## Troubleshooting

### Database Connection Errors
Ensure test database exists and credentials are correct in `.env.testing`

### Failed Assertions
Check test output for detailed error messages and stack traces

### Slow Tests
Use `--filter` to run specific tests during development

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://phpunit.de/best-practices.html)
