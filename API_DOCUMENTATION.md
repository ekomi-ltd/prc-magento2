# eKomi Product Review Container - API Documentation

## Overview

The eKomi Product Review Container is a Magento 2 module that integrates eKomi reviews and ratings into your webshop. It provides an easy way to display product reviews with Google rich snippet functionality and allows individual positioning of product reviews.

**Module Name:** `Ekomi_ProductReviewContainer`  
**Version:** 1.1.0  
**Namespace:** `Ekomi\ProductReviewContainer`  
**License:** OSL-3.0, AFL-3.0

## Features

- Integration with eKomi review API
- Product review display and management
- Review helpfulness rating system
- Support for configurable, bundle, and grouped products
- Google rich snippet functionality
- AJAX-powered review loading
- Automated review import via cron jobs
- Multi-store configuration support

## Installation & Configuration

### Prerequisites
- PHP 5.5.0+ (supports up to PHP 7.1.0)
- Magento 2 Framework 100.0.4+

### Configuration
Navigate to **Admin Panel > Stores > Configuration > Ekomi > Product Review Container** to configure:

- **Enabled**: Enable/disable the module
- **Shop ID**: Interface ID received from eKomi
- **Shop Password**: Interface password received from eKomi
- **Group Reviews**: Include child product reviews (for configurable/bundle/grouped products)
- **Text Message**: Custom message when no reviews are found

## API Endpoints

### Frontend Routes

The module registers the following frontend route:
- **Route ID**: `prc`
- **Front Name**: `prc`
- **Base URL**: `{your-domain}/prc/`

### AJAX Endpoints

#### 1. Load Reviews
**Endpoint:** `POST /prc/ajax/load`

Loads product reviews with filtering and pagination.

**Request Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `product_id` | string | Yes | - | Product ID to load reviews for |
| `limit` | integer | No | 10 | Number of reviews per page |
| `offset_page` | integer | No | 0 | Page offset for pagination |
| `filter_type` | integer | No | 0 | Review sorting filter |

**Filter Types:**
- `0`: Newest first (timestamp DESC)
- `1`: Oldest first (timestamp ASC)
- `2`: Most helpful first (helpful DESC)
- `3`: Highest rated first (stars DESC)
- `4`: Lowest rated first (stars ASC)

**Response Format:**
```json
{
  "state": "success|error",
  "message": "Response message",
  "reviews_data": {
    "result": "HTML content of reviews",
    "count": 5
  },
  "_POST": "Submitted parameters"
}
```

**Example Request:**
```javascript
$.ajax({
    url: '/prc/ajax/load',
    type: 'POST',
    data: {
        product_id: '123',
        limit: 5,
        offset_page: 0,
        filter_type: 0
    },
    success: function(response) {
        console.log(response);
    }
});
```

#### 2. Rate Review Helpfulness
**Endpoint:** `POST /prc/ajax/save`

Allows users to rate review helpfulness.

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `review_id` | string | Yes | ID of the review to rate |
| `helpfulness` | string | Yes | '1' for helpful, '0' for not helpful |

**Response Format:**
```json
{
  "state": "success|error",
  "message": "Response message",
  "rate_helpfulness": "helpful|nothelpful",
  "_POST": "Submitted parameters"
}
```

**Example Request:**
```javascript
$.ajax({
    url: '/prc/ajax/save',
    type: 'POST',
    data: {
        review_id: '456',
        helpfulness: '1'
    },
    success: function(response) {
        console.log(response);
    }
});
```

## Data Models

### Review Data Structure

The module stores reviews in the `prc_product_reviews` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| `id` | INTEGER (Primary Key) | Unique review ID |
| `shop_id` | INTEGER | eKomi shop identifier |
| `order_id` | TEXT(64) | Order ID from eKomi |
| `product_id` | TEXT(64) | Magento product ID |
| `timestamp` | INTEGER(11) | Review submission timestamp |
| `stars` | INTEGER(1) | Star rating (1-5) |
| `review_comment` | TEXT | Review text content |
| `helpful` | SMALLINT(5) | Helpful votes count |
| `nothelpful` | SMALLINT(5) | Not helpful votes count |

### Review Object Methods

#### `Ekomi\ProductReviewContainer\Model\Reviews`

**Key Methods:**

- `productReviews($productId, $orderBy, $sortType, $offsetPage, $limit)` - Retrieve product reviews
- `starsAverage($product)` - Calculate average star rating
- `reviewStarsCount($product)` - Get star distribution count
- `productReviewsCount($product)` - Get total review count
- `rateReviewHelpfulness($reviewId, $helpfulness)` - Rate review helpfulness
- `getSingleReview($reviewId)` - Get individual review data
- `populateTable($range)` - Import reviews from eKomi API

## External API Integration

### eKomi API
The module integrates with eKomi's product feedback API:

**API Endpoint:** `http://api.ekomi.de/v3/getProductfeedback`

**Parameters:**
- `interface_id`: Shop ID from configuration
- `interface_pw`: Shop password from configuration
- `type`: `json`
- `charset`: `utf-8`
- `range`: Time range for reviews (e.g., '1w' for 1 week)

**Response:** JSON array of review objects with the following structure:
```json
[
  {
    "order_id": "12345",
    "product_id": "67890",
    "submitted": 1234567890,
    "rating": 5,
    "review": "Great product!"
  }
]
```

## Cron Jobs

### Automated Review Import
- **Job Name:** `magestore_cron`
- **Schedule:** Daily at midnight (`0 0 * * *`)
- **Class:** `Ekomi\ProductReviewContainer\Cron\Import`
- **Method:** `execute()`
- **Function:** Imports reviews from eKomi API for the past week

## Product Type Support

The module supports various Magento product types:

### Configurable Products
- Reviews from child products are included when "Group Reviews" is enabled
- Uses `getTypeInstance()->getUsedProducts()` to fetch child products

### Bundle Products
- Child product reviews are aggregated
- Uses `Bundle\Model\ResourceModel\Selection` to get child product IDs

### Grouped Products
- Associated product reviews are included
- Uses `GroupedProduct\Model\ResourceModel\Product\Link` to fetch linked products

## Frontend Integration

### Templates
- `reviewscontainer.phtml` - Main review container
- `reviewscontainerpartial.phtml` - Partial review list for AJAX loading
- `ministarscounter.phtml` - Star rating display

### JavaScript
- `ekomi-prc.js` - Frontend AJAX functionality
- Handles review loading and helpfulness rating

### CSS
- `ekomi-prc.css` - Styling for review components
- Includes star rating sprites and layout styles

## Security & Validation

### Input Validation
- All AJAX requests validate required parameters
- Review IDs and helpfulness values are sanitized
- Module checks if it's enabled before processing requests

### ACL Permissions
- Configuration access controlled by `Ekomi_ProductReviewContainer::system_config`

## Error Handling

### Common Error Responses
- **Missing Parameters:** Returns error state with parameter validation message
- **Module Disabled:** Returns error when module is not active
- **API Failures:** Graceful handling of eKomi API connection issues
- **Database Errors:** Exception handling for database operations

## Performance Considerations

### Caching
- Reviews are stored locally in Magento database
- Reduces external API calls for better performance

### Pagination
- Configurable limit and offset for large review sets
- Prevents memory issues with large datasets

### Multi-store Support
- Separate configuration per store/website
- Isolated review data per shop ID

## Troubleshooting

### Common Issues
1. **Reviews not loading:** Check eKomi API credentials and module enable status
2. **AJAX errors:** Verify frontend routes are properly configured
3. **Import failures:** Check cron job execution and API connectivity
4. **Missing reviews:** Ensure "Group Reviews" setting matches product type requirements

### Debug Information
- Module includes `_POST` data in all AJAX responses for debugging
- Check Magento logs for detailed error information
- Verify database table `prc_product_reviews` exists and has data

## Version History

- **1.1.0** - Current version with full Magento 2 compatibility
- Supports PHP 5.5.0 to 7.1.0
- Magento Framework 100.0.4+

## Support

For technical support or questions about this module, please refer to the eKomi documentation or contact eKomi support directly.
