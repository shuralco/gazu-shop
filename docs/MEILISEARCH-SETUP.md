# Meilisearch Setup

SimpleShop search works out of the box with SQL LIKE queries. To enable Meilisearch for typo-tolerant, fast, Ukrainian-language-aware search, follow the steps below.

## Prerequisites

The `meilisearch` service is already defined in `docker-compose.coolify-final.yml` and runs on port 7700 with master key `masterKey`.

## Activation Steps

### 1. Install packages

```bash
docker exec simpleshop-app composer require laravel/scout meilisearch/meilisearch-php
```

### 2. Add Searchable trait to Product model

Open `app/Models/Product.php` and add the trait import and usage:

```php
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, HasSeoMeta, HasTranslations, Searchable, Sluggable;
```

The `toSearchableArray()`, `searchableAs()`, and `shouldBeSearchable()` methods are already defined in the model.

### 3. Update .env

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey
```

### 4. Index products

```bash
docker exec simpleshop-app php artisan search:index
```

To flush and re-index:

```bash
docker exec simpleshop-app php artisan search:index --flush
```

### 5. Verify

- Open `/search` and type a query
- Meilisearch handles typos, partial matches, and relevance ranking automatically
- The `SearchService` detects Scout availability at runtime and switches between Meilisearch and SQL LIKE seamlessly

## How It Works

`App\Services\SearchService` checks at runtime whether:
1. `SCOUT_DRIVER` is set to something other than `collection`
2. The `Laravel\Scout\Searchable` class exists (package installed)
3. The `Product` model uses the `Searchable` trait

If all three conditions are met, search queries go through Meilisearch. Otherwise, the SQL LIKE fallback is used automatically.

## Searchable Fields

The following fields are indexed (see `Product::toSearchableArray()`):

| Field | Description |
|-------|-------------|
| title | Product name (translatable) |
| excerpt | Short description (translatable) |
| content | Full description (HTML stripped) |
| sku | Product SKU code |
| brand | Brand name |
| category_title | Category name |
| price / old_price | For filtering |
| is_hit / is_new / is_active | Flags for filtering |
| rating / reviews_count | For sorting |

## Meilisearch Dashboard

Access the Meilisearch dashboard at `http://localhost:7700` (or your server's address) to inspect indexes, documents, and search behavior.
