# MySQL Database Backup System for SimpleShop

This directory contains database backups and backup management scripts for the SimpleShop project.

## Quick Start

### Create a Backup
```bash
# Run from the project root directory
./create_mysql_backup.sh
```

### List Available Backups
```bash
./restore_mysql_backup.sh
```

### Restore a Backup
```bash
./restore_mysql_backup.sh simpleshop_backup_20250830_123205.sql
```

## Files Description

### Scripts
- **`create_mysql_backup.sh`** - Creates MySQL database backups with timestamp
- **`restore_mysql_backup.sh`** - Restores database from backup files

### Backup Files
- **`simpleshop_backup_YYYYMMDD_HHMMSS.sql`** - Timestamped database backup files

## Database Connection Details

The backup scripts use the following connection parameters:
- **Host**: mysql (Docker container hostname)
- **Database**: simpleshop
- **Username**: simpleshop
- **Password**: secret
- **Docker Container**: simpleshop-mysql

## Backup Features

### Backup Script Features
- ✅ **Connection Testing** - Verifies database connectivity before backup
- ✅ **Docker Integration** - Works seamlessly with Docker containers
- ✅ **Error Handling** - Graceful error handling and informative messages
- ✅ **Integrity Verification** - Checks backup completeness
- ✅ **Timestamped Files** - Automatic timestamp-based naming
- ✅ **Comprehensive Options** - Includes routines, triggers, and proper SQL options
- ✅ **Progress Feedback** - Colored output for better user experience

### Backup Options Used
```bash
mysqldump \
    --single-transaction \    # Consistent backup without locking
    --routines \             # Include stored procedures and functions
    --triggers \             # Include triggers
    --add-drop-table \       # Add DROP TABLE statements
    --add-locks \            # Add table locks
    --create-options \       # Include table creation options
    --disable-keys \         # Disable keys during import for speed
    --extended-insert \      # Use extended INSERT syntax
    --quick \                # Don't cache query results
    --set-charset           # Set charset information
```

## Restore Features

### Restore Script Features
- ✅ **Interactive Mode** - Safe restore with confirmation prompts
- ✅ **Backup Listing** - Shows available backups when run without arguments
- ✅ **Integrity Check** - Verifies backup file before restore
- ✅ **Progress Feedback** - Clear status updates during restore
- ✅ **Verification** - Confirms successful restore with table count
- ✅ **Flexible Input** - Accepts both filename and full path

## Usage Examples

### Basic Backup Creation
```bash
lionex@server:~/Projects/simpleshop$ ./create_mysql_backup.sh
[INFO] Starting MySQL backup for SimpleShop project...
[INFO] Timestamp: 20250830_123205
[SUCCESS] Docker container is running
[SUCCESS] Database connection successful
[SUCCESS] Backup created successfully!
[SUCCESS] Size: 159K
```

### Listing Available Backups
```bash
lionex@server:~/Projects/simpleshop$ ./restore_mysql_backup.sh
[INFO] Available backup files in /home/lionex/Projects/simpleshop/database:

  • simpleshop_backup_20250830_123122.sql (Size: 159K, Modified: Aug 30 12:31)
  • simpleshop_backup_20250830_123205.sql (Size: 159K, Modified: Aug 30 12:32)
```

### Restoring from Backup
```bash
lionex@server:~/Projects/simpleshop$ ./restore_mysql_backup.sh simpleshop_backup_20250830_123205.sql
[WARNING] This will replace all data in the 'simpleshop' database!
Are you sure you want to continue? Type 'yes' to confirm: yes
[SUCCESS] Database restore completed successfully!
[SUCCESS] Tables restored: 32
```

## Manual Commands

If you prefer to run commands manually:

### Manual Backup
```bash
docker exec simpleshop-mysql mysqldump \
  -h mysql -u simpleshop -psecret \
  --single-transaction --routines --triggers \
  simpleshop > database/backup_$(date +%Y%m%d_%H%M%S).sql
```

### Manual Restore
```bash
docker exec -i simpleshop-mysql mysql \
  -h mysql -u simpleshop -psecret \
  simpleshop < database/your_backup_file.sql
```

## Database Schema

The backup includes the following table types:

### Core Tables
- `users` - User accounts and profiles
- `categories` - Product categories (hierarchical)
- `products` - Product catalog
- `orders` - Customer orders
- `order_products` - Order line items

### Filter System
- `filter_groups` - Filter group definitions (Color, Size, etc.)
- `filters` - Individual filter values
- `category_filters` - Category-filter relationships
- `filter_products` - Product-filter relationships

### E-commerce Features
- `coupons` - Discount coupons
- `coupon_usages` - Coupon usage tracking
- `media` - File attachments and images

### Shipping System
- `shipping_methods` - Available shipping options
- `shipping_providers` - Shipping service providers
- `shipping_rates` - Shipping cost calculations
- `shipping_warehouses` - Warehouse locations
- `shipping_zones` - Delivery zones
- `shipments` - Shipment tracking

### Payment System
- `payments` - Payment transactions
- `payment_logs` - Payment processing logs
- `payment_gateway_settings` - Payment gateway configurations

### System Tables
- `cache` - Application cache
- `jobs` - Background job queue
- `failed_jobs` - Failed background jobs
- `migrations` - Database migration history

## Troubleshooting

### Common Issues

#### 1. Docker Container Not Running
```
[ERROR] Docker container 'simpleshop-mysql' is not running!
```
**Solution**: Start the containers
```bash
docker-compose up -d
```

#### 2. Connection Failed
```
[ERROR] Failed to connect to database!
```
**Solutions**:
- Check if MySQL container is healthy: `docker ps`
- Verify connection details in the script
- Check container logs: `docker logs simpleshop-mysql`

#### 3. Permission Warnings
```
[WARNING] Access denied; you need (at least one of) the PROCESS privilege(s)
```
**Note**: This is a harmless warning about tablespace dumping. The backup is still complete.

#### 4. Backup File Empty
```
[ERROR] Backup file is empty or was not created!
```
**Solutions**:
- Check disk space: `df -h`
- Verify write permissions to database directory
- Check container status and logs

### Log Files

The scripts create temporary log files during operation:
- `/tmp/mysqldump_errors.log` - Backup error logs
- `/tmp/mysql_restore_errors.log` - Restore error logs

These files are automatically cleaned up after script completion.

## Security Considerations

1. **Password Visibility**: The MySQL password is visible in the script. In production:
   - Use environment variables or Docker secrets
   - Implement proper credential management

2. **Backup Storage**: 
   - Store backups in secure locations
   - Consider encryption for sensitive data
   - Implement backup rotation policies

3. **Access Control**:
   - Limit access to backup scripts and files
   - Use appropriate file permissions (current: 755 for scripts)

## Automation

### Cron Job Example
```bash
# Daily backup at 2 AM
0 2 * * * /home/lionex/Projects/simpleshop/create_mysql_backup.sh >/dev/null 2>&1

# Weekly cleanup of old backups (keep last 30 days)
0 3 * * 0 find /home/lionex/Projects/simpleshop/database -name "simpleshop_backup_*.sql" -mtime +30 -delete
```

### Backup Rotation Script
```bash
#!/bin/bash
# Keep only the last 10 backups
cd /home/lionex/Projects/simpleshop/database
ls -t simpleshop_backup_*.sql | tail -n +11 | xargs rm -f
```

## Support

For issues or questions:
1. Check Docker container status: `docker ps`
2. Review container logs: `docker logs simpleshop-mysql`
3. Verify database connectivity manually
4. Check script permissions and file paths

## Version Information

- **MySQL Version**: 8.0.43
- **mysqldump Version**: 10.13
- **Container**: mysql:8.0
- **Scripts Created**: 2025-08-30
- **Last Updated**: 2025-08-30