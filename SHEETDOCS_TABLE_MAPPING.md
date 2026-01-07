# SheetDocs Table Name Mapping

## Database Table Naming Convention

All SheetDocs tables use the `sheet_` prefix to clearly identify them as part of the SheetDocs project.

## Complete Table Mapping

| Original Name | New Name with Prefix | Purpose |
|--------------|---------------------|---------|
| `documents` | `sheet_documents` | Main table for documents and spreadsheets |
| `sheets` | `sheet_sheets` | Sheet tabs within spreadsheet documents |
| `sheet_cells` | `sheet_cells` | Individual cell data (already had prefix) |
| `document_shares` | `sheet_document_shares` | Collaboration and sharing permissions |
| `document_versions` | `sheet_document_versions` | Version history (premium feature) |
| `comments` | `sheet_comments` | Document commenting system |
| `user_subscriptions` | `sheet_user_subscriptions` | User subscription management |
| `usage_stats` | `sheet_usage_stats` | Usage tracking per user |
| `activity_logs` | `sheet_activity_logs` | Complete audit trail |
| `templates` | `sheet_templates` | Pre-built document templates |
| `settings` | `sheet_settings` | System configuration |

## Total Tables: 11

All tables are prefixed with `sheet_` for:
- **Clear identification**: Easy to identify SheetDocs-related tables
- **Namespace isolation**: Prevents conflicts with other projects
- **Better organization**: Groups all related tables together
- **Database management**: Easier to manage, backup, and migrate

## Foreign Key Relationships

All foreign key relationships have been updated:
- `sheet_sheets.document_id` → `sheet_documents.id`
- `sheet_cells.sheet_id` → `sheet_sheets.id`
- `sheet_document_shares.document_id` → `sheet_documents.id`
- `sheet_document_versions.document_id` → `sheet_documents.id`
- `sheet_comments.document_id` → `sheet_documents.id`
- `sheet_activity_logs.document_id` → `sheet_documents.id`

## PHP Controller Updates

All controllers have been updated to use the new table names:
- ✅ DashboardController.php
- ✅ DocumentController.php
- ✅ SheetController.php
- ✅ SubscriptionController.php
- ✅ ShareController.php
- ✅ TemplateController.php
- ✅ ApiController.php
- ✅ ExportController.php
- ✅ PublicController.php
- ✅ SettingsController.php
- ✅ SheetDocsAdminController.php (Admin)

## Example SQL Queries

**Old:**
```sql
SELECT * FROM documents WHERE user_id = 1;
```

**New:**
```sql
SELECT * FROM sheet_documents WHERE user_id = 1;
```

**Old:**
```sql
SELECT s.* FROM sheets s
INNER JOIN documents d ON s.document_id = d.id
WHERE d.user_id = 1;
```

**New:**
```sql
SELECT s.* FROM sheet_sheets s
INNER JOIN sheet_documents d ON s.document_id = d.id
WHERE d.user_id = 1;
```

## Validation Status

✅ All PHP files validated for syntax errors
✅ All foreign key relationships verified
✅ All SQL queries updated in controllers
✅ Schema file updated with correct references
✅ Default data INSERTs updated

The implementation is ready for database creation and testing.
