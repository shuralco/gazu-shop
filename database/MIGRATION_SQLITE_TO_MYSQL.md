# SQLite to MySQL Migration Guide

## Overview
This guide describes how to migrate data from SQLite to MySQL for the SimpleShop application.

## Prerequisites
- SSH access to the production server
- MySQL container running (`simpleshop-mysql`)
- SQLite database file present at `database/database.sqlite`

## Migration Steps

### Step 1: Deploy Updated Code

First, ensure the updated code is deployed to the server:
```bash
# Files that need to be on the server:
# - .env (updated with MySQL credentials)
# - config/database.php (sqlite_source connection)
# - app/Console/Commands/MigrateSqliteToMysql.php
```

### Step 2: Connect to Application Container

```bash
# Option A: Via Coolify
# Go to Coolify dashboard > SimpleShop > Terminal

# Option B: Via SSH
ssh root@23.88.115.55
docker exec -it simpleshop-app bash
```

### Step 3: Create SQLite Backup

```bash
cd /var/www/simpleshop
cp database/database.sqlite database/database.sqlite.backup_$(date +%Y%m%d_%H%M%S)
```

### Step 4: Run Laravel Migrations on MySQL

```bash
# Clear config cache first
php artisan config:clear

# Run fresh migrations on MySQL
php artisan migrate:fresh --force
```

### Step 5: Test Migration (Dry Run)

```bash
# First, do a dry run to see what will be migrated
php artisan db:migrate-sqlite-to-mysql --dry-run
```

Expected output:
```
===========================================
   SQLite to MySQL Migration Tool
===========================================

Verifying database connections...
  [OK] SQLite connection
  [OK] MySQL connection

  [MIGRATING] users: X records...
  [MIGRATING] brands: X records...
  ...
```

### Step 6: Execute Migration

```bash
# Run the actual migration
php artisan db:migrate-sqlite-to-mysql
```

### Step 7: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 8: Verify Migration

```bash
# Check record counts via tinker
php artisan tinker
```

In tinker:
```php
$tables = ['users', 'categories', 'products', 'brands', 'orders'];
foreach ($tables as $table) {
    echo "{$table}: " . DB::table($table)->count() . "\n";
}
exit;
```

### Step 9: Test Application

Visit in browser:
- https://shop.textory.online/brands - Should show brands
- https://shop.textory.online/admin - Admin panel should work

## Migration Command Options

```bash
# Migrate all tables
php artisan db:migrate-sqlite-to-mysql

# Dry run (no changes)
php artisan db:migrate-sqlite-to-mysql --dry-run

# Migrate specific table only
php artisan db:migrate-sqlite-to-mysql --table=brands
```

## Rollback Procedure

If migration fails, you can rollback to SQLite:

### Quick Rollback

Edit `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/simpleshop/database/database.sqlite
```

Then:
```bash
php artisan config:clear
```

### Full Rollback with MySQL Backup

```bash
# Backup MySQL data first
docker exec simpleshop-mysql mysqldump \
  -h mysql -u simpleshop -psecret \
  --single-transaction simpleshop > /tmp/mysql_backup.sql

# Then switch back to SQLite
# Edit .env as shown above
```

## Table Migration Order

Tables are migrated in this order to respect foreign key constraints:

1. **Base tables**: users, brands, coupons, filter_groups, shipping_providers, etc.
2. **Level 1**: categories, filters, shipping_zones, shipping_methods
3. **Level 2**: products, orders
4. **Level 3**: order_products, filter_products, brand_filters, reviews, payments
5. **Level 4**: payment_logs, tracking_updates

**Skipped tables**: cache, sessions, jobs, migrations (regenerated automatically)

## Troubleshooting

### Error: SQLSTATE[HY000] [2002] Connection refused

MySQL container might not be running:
```bash
docker ps | grep mysql
docker start simpleshop-mysql
```

### Error: Table doesn't exist

Run migrations first:
```bash
php artisan migrate:fresh --force
```

### Error: Foreign key constraint fails

The migration command disables FK checks automatically. If still failing:
```bash
# Manually disable FK checks
docker exec simpleshop-mysql mysql -u simpleshop -psecret -e "SET FOREIGN_KEY_CHECKS=0"
```

### Verify MySQL Connection

```bash
docker exec simpleshop-mysql mysql -h mysql -u simpleshop -psecret -e "SHOW DATABASES"
```

## Post-Migration Checklist

- [ ] All tables migrated successfully
- [ ] /brands page loads without errors
- [ ] Admin panel accessible
- [ ] Products display correctly
- [ ] Search functionality works
- [ ] User login works
- [ ] Orders history visible

## Support

If you encounter issues, check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```
