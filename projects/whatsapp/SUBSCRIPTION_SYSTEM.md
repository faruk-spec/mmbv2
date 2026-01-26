# WhatsApp API Subscription System

Complete subscription management system for the WhatsApp API project with admin controls and user interface.

## 📋 Features

### Admin Features
- **Subscription Plans Management**
  - Create, edit, and delete subscription plans
  - Set pricing, limits, and duration for each plan
  - Enable/disable plans
  - Track plan usage statistics

- **User Subscriptions Management**
  - View all user subscriptions with filtering
  - Manually assign subscriptions to users
  - Extend subscription periods
  - Reset usage statistics
  - Cancel subscriptions
  - Real-time usage monitoring

### User Features
- **Subscription Status Page**
  - Current plan details and status
  - Days remaining until expiry
  - Usage statistics with visual progress bars
  - Messages, sessions, and API calls tracking
  - View available plans for upgrade

### Helper Functions
- Subscription limit checking before actions
- Automatic usage tracking
- Expired subscription detection
- Support for unlimited limits (value = 0)

## 🗄️ Database Schema

### Tables Created

#### `whatsapp_subscription_plans`
Stores available subscription plans:
- `id` - Plan ID
- `name` - Plan name (e.g., "Premium Plan")
- `description` - Plan description
- `price` - Plan price
- `currency` - Currency (USD, EUR, GBP, INR)
- `messages_limit` - Maximum messages (0 = unlimited)
- `sessions_limit` - Maximum sessions (0 = unlimited)
- `api_calls_limit` - Maximum API calls (0 = unlimited)
- `duration_days` - Plan duration in days
- `is_active` - Plan status
- `created_at` - Creation timestamp

#### `whatsapp_subscriptions`
Stores user subscriptions:
- `id` - Subscription ID
- `user_id` - User ID (foreign key)
- `plan_type` - Plan type (free/basic/premium/enterprise)
- `status` - Subscription status (active/inactive/expired/cancelled)
- `start_date` - Subscription start date
- `end_date` - Subscription end date
- `messages_limit` - Messages limit for this subscription
- `sessions_limit` - Sessions limit for this subscription
- `api_calls_limit` - API calls limit for this subscription
- `messages_used` - Messages used count
- `sessions_used` - Sessions used count
- `api_calls_used` - API calls used count
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### View: `whatsapp_subscription_details`
Combined view with user information and usage percentages for easy querying.

### Default Plans
Four default plans are created:
1. **Free Plan** - 100 messages, 1 session, 1000 API calls ($0.00)
2. **Basic Plan** - 1000 messages, 3 sessions, 10000 API calls ($9.99)
3. **Premium Plan** - 10000 messages, 10 sessions, 100000 API calls ($29.99)
4. **Enterprise Plan** - Unlimited everything ($99.99)

## 📁 Files Created

### Database
- `/projects/whatsapp/subscription_schema.sql` - Database schema

### Controllers
- `/controllers/Admin/WhatsAppSubscriptionController.php` - Admin subscription management
- Updated `/projects/whatsapp/controllers/DashboardController.php` - Added subscription() method

### Views

#### Admin Views
- `/views/admin/projects/whatsapp/subscription-plans.php` - List all plans
- `/views/admin/projects/whatsapp/subscription-plan-form.php` - Create/edit plan form
- `/views/admin/projects/whatsapp/user-subscriptions.php` - List user subscriptions
- `/views/admin/projects/whatsapp/assign-subscription.php` - Assign subscription form

#### User Views
- `/views/whatsapp/subscription.php` - User subscription status page

### Helpers
- `/projects/whatsapp/helpers/SubscriptionHelper.php` - Subscription checking and usage tracking

### Routes
- Updated `/routes/admin.php` - Added 11 new admin routes
- Updated `/projects/whatsapp/routes/web.php` - Added user subscription route

### Layouts
- Updated `/views/layouts/admin.php` - Added subscription menu items to sidebar

## 🚀 Installation

1. **Import Database Schema**
   ```bash
   mysql -u username -p database_name < /home/runner/work/mmbv2/mmbv2/projects/whatsapp/subscription_schema.sql
   ```

2. **Routes are auto-loaded** - No additional configuration needed

3. **Access Admin Panel**
   - Navigate to: `/admin/whatsapp/subscription-plans`
   - Or use sidebar: WhatsApp API → Subscription Plans

## 📍 Routes

### Admin Routes

#### Subscription Plans
- `GET /admin/whatsapp/subscription-plans` - List all plans
- `GET /admin/whatsapp/subscription-plans/create` - Create plan form
- `POST /admin/whatsapp/subscription-plans/create` - Store new plan
- `GET /admin/whatsapp/subscription-plans/edit/{id}` - Edit plan form
- `POST /admin/whatsapp/subscription-plans/update/{id}` - Update plan
- `POST /admin/whatsapp/subscription-plans/delete/{id}` - Delete plan

#### User Subscriptions
- `GET /admin/whatsapp/user-subscriptions` - List user subscriptions
- `GET /admin/whatsapp/user-subscriptions/assign` - Assign subscription form
- `POST /admin/whatsapp/user-subscriptions/assign` - Assign subscription
- `POST /admin/whatsapp/user-subscriptions/update/{id}` - Update subscription
- `POST /admin/whatsapp/user-subscriptions/cancel/{id}` - Cancel subscription

### User Routes
- `GET /projects/whatsapp/subscription` - View subscription status

## 💡 Usage Examples

### Checking Subscription Limits

```php
use Projects\WhatsApp\Helpers\SubscriptionHelper;

// Check if user can send a message
$check = SubscriptionHelper::canSendMessage($userId);
if (!$check['allowed']) {
    die('Error: ' . $check['reason']);
}

// Check if user can create a session
$check = SubscriptionHelper::canCreateSession($userId);
if (!$check['allowed']) {
    die('Error: ' . $check['reason']);
}

// Check if user can make API call
$check = SubscriptionHelper::canMakeApiCall($userId);
if (!$check['allowed']) {
    die('Error: ' . $check['reason']);
}
```

### Tracking Usage

```php
use Projects\WhatsApp\Helpers\SubscriptionHelper;

// After sending a message
SubscriptionHelper::incrementMessageUsage($userId, 1);

// After creating a session
SubscriptionHelper::incrementSessionUsage($userId, 1);

// After API call
SubscriptionHelper::incrementApiCallUsage($userId, 1);

// When deleting a session
SubscriptionHelper::decrementSessionUsage($userId, 1);
```

### Getting Usage Statistics

```php
use Projects\WhatsApp\Helpers\SubscriptionHelper;

$stats = SubscriptionHelper::getUsageStats($userId);
// Returns:
// [
//     'messages' => ['used' => 50, 'limit' => 100, 'percent' => 50],
//     'sessions' => ['used' => 1, 'limit' => 3, 'percent' => 33.33],
//     'api_calls' => ['used' => 500, 'limit' => 1000, 'percent' => 50]
// ]
```

### Update Expired Subscriptions (Cron Job)

```php
use Projects\WhatsApp\Helpers\SubscriptionHelper;

// Run this in a daily cron job
SubscriptionHelper::updateExpiredSubscriptions();
```

## 🎨 UI Features

### Admin Interface
- Modern card-based design for plans
- Color-coded status badges
- Real-time usage statistics with progress bars
- Filtering by status, plan type, and user
- Inline actions (extend, reset, cancel)
- Pagination support
- Responsive design

### User Interface
- Beautiful subscription status dashboard
- Visual progress bars for usage tracking
- Color-coded warnings for limits
- Days remaining countdown
- Available plans showcase
- Responsive mobile design
- WhatsApp brand colors

## 🔒 Security

- CSRF token validation on all forms
- Admin-only access for management pages
- Input validation and sanitization
- SQL injection protection via prepared statements
- User session verification
- Foreign key constraints in database

## 🎯 Key Features

1. **Flexible Plans** - Support for any combination of limits
2. **Unlimited Support** - Set limit to 0 for unlimited
3. **Real-time Tracking** - Instant usage updates
4. **Status Management** - Active, inactive, expired, cancelled states
5. **Easy Assignment** - Quick subscription assignment to users
6. **Visual Feedback** - Progress bars and color coding
7. **Multi-currency** - Support for USD, EUR, GBP, INR
8. **Custom Duration** - Override plan duration when assigning
9. **Expiry Warnings** - Alert users before expiration
10. **Usage Reset** - Admin can reset usage counters

## 📝 Notes

- When a user gets a new subscription, the old one is automatically deactivated
- Plans can't be deleted if they're in use by active subscriptions
- 0 value for limits means unlimited
- Subscription status is automatically updated to 'expired' when end date passes
- Usage tracking is cumulative for the subscription period
- All timestamps are in the database timezone

## 🔧 Integration Points

To integrate subscription checking into existing WhatsApp features:

1. **Before sending messages** - Add check in MessageController
2. **Before creating sessions** - Add check in SessionController  
3. **Before API calls** - Add check in ApiHandler
4. **After successful actions** - Increment usage counters

Example integration in MessageController:

```php
use Projects\WhatsApp\Helpers\SubscriptionHelper;

public function send() {
    $userId = Auth::user()['id'];
    
    // Check subscription
    $check = SubscriptionHelper::canSendMessage($userId);
    if (!$check['allowed']) {
        return $this->jsonError($check['reason']);
    }
    
    // Send message logic here...
    
    // Track usage
    SubscriptionHelper::incrementMessageUsage($userId, 1);
}
```

## 📊 Admin Sidebar

The subscription links are added under the WhatsApp API dropdown:
- Overview
- Sessions
- Messages
- Users
- API Logs
- **Subscription Plans** ⭐ NEW
- **User Subscriptions** ⭐ NEW

## 🎓 Best Practices

1. **Regular Monitoring** - Check subscription usage regularly
2. **Expiry Notifications** - Set up email notifications for expiring subscriptions
3. **Usage Limits** - Set reasonable limits based on server capacity
4. **Plan Pricing** - Review and adjust pricing based on usage patterns
5. **Backup** - Regular backup of subscription data
6. **Cron Jobs** - Set up automated expiry checking
7. **Analytics** - Track which plans are most popular

## ⚡ Performance

- Database indexes on frequently queried columns
- View for optimized subscription queries
- Minimal queries using helper functions
- Prepared statements for security and speed
- Efficient usage tracking

## 🆘 Support

For issues or questions about the subscription system:
1. Check the database schema is properly imported
2. Verify routes are loaded correctly
3. Ensure user has admin privileges for admin features
4. Check browser console for JavaScript errors
5. Review PHP error logs for backend issues

---

**Version:** 1.0  
**Created:** <?= date('Y-m-d') ?>  
**Author:** AI Assistant  
**License:** Same as parent project
