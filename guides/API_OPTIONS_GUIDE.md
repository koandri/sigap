# API Options for Form Fields

This guide explains how to configure form fields to populate their options from external APIs.

## Overview

![API Options Guide](/guides-imgs/api-options-guide.png)

The system now supports populating select fields (`select_single` and `select_multiple`) with options from external APIs. This is useful for scenarios like:

- Employee selection from HR systems
- Product lists from inventory systems
- Department lists from organizational systems
- Any other dynamic data sources

## Configuration

### API Source Configuration

When creating or editing a form field, you can configure an API source in the `api_source_config` field. The configuration supports:

```json
{
  "url": "https://api.example.com/employees",
  "method": "GET",
  "value_field": "id",
  "label_field": "name",
  "data_path": "data.employees",
  "auth": {
    "type": "bearer",
    "token": "your-api-token"
  },
  "params": {
    "status": "active"
  },
  "headers": {
    "Accept": "application/json"
  },
  "timeout": 30,
  "cache_ttl": 300
}
```

### Combined Labels

You can create more descriptive option labels by combining multiple fields from the API response:

**Simple Field:** `name`

**Combined Labels:**
- `{name} - {gender} - {location}` → "John Doe - Male - New York"
- `{first_name} {last_name}` → "John Doe"
- `{employee_name} ({department})` → "John Doe (HR)"
- `{name} - {position} - {department}` → "John Doe - Manager - HR"

**Template Syntax:**
- Use `{field_name}` to reference any field from the API response
- Add separators like ` - `, ` (`, `)`, spaces, etc.
- Fields that don't exist will be replaced with empty strings
- Supports any combination of fields and separators

### Configuration Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `url` | string | Yes | The API endpoint URL |
| `method` | string | No | HTTP method (GET, POST, PUT, PATCH, DELETE). Default: GET |
| `value_field` | string | Yes | Field name in API response to use as option value |
| `label_field` | string | Yes | Field name in API response to use as option label. Supports combined labels like `{name} - {gender} - {location}` |
| `data_path` | string | No | Dot notation path to the data array in the response |
| `auth` | object | No | Authentication configuration |
| `params` | object | No | Query parameters or request body data |
| `headers` | object | No | Additional HTTP headers |
| `timeout` | integer | No | Request timeout in seconds (1-300). Default: 30 |
| `cache_ttl` | integer | No | Cache time-to-live in seconds (60-3600). Default: 300 |

### Authentication Types

#### No Authentication Required
If your API doesn't require authentication, you can simply omit the `auth` field:

```json
{
  "url": "https://api.example.com/public-data",
  "method": "GET",
  "value_field": "id",
  "label_field": "name",
  "data_path": "data"
}
```

Or set it to null:
```json
{
  "url": "https://api.example.com/public-data",
  "method": "GET",
  "value_field": "id",
  "label_field": "name",
  "data_path": "data",
  "auth": null
}
```

#### Bearer Token
```json
{
  "type": "bearer",
  "token": "your-bearer-token"
}
```

#### Basic Authentication
```json
{
  "type": "basic",
  "username": "api-user",
  "password": "api-password"
}
```

#### API Key
```json
{
  "type": "api_key",
  "api_key": "your-api-key",
  "header_name": "X-API-Key"
}
```

## Example Configurations

### Public API without Authentication

```json
{
  "url": "https://jsonplaceholder.typicode.com/users",
  "method": "GET",
  "value_field": "id",
  "label_field": "name",
  "cache_ttl": 600
}
```

This example fetches user data from the public JSONPlaceholder API without any authentication required.

### Employee Selection from HR System

```json
{
  "url": "https://hr-api.company.com/api/employees",
  "method": "GET",
  "value_field": "employee_id",
  "label_field": "full_name",
  "data_path": "data",
  "auth": {
    "type": "bearer",
    "token": "hr-api-token-here"
  },
  "params": {
    "status": "active",
    "department": "all"
  },
  "cache_ttl": 600
}
```

### Product Selection from Inventory System

```json
{
  "url": "https://inventory.company.com/api/products",
  "method": "POST",
  "value_field": "sku",
  "label_field": "product_name",
  "data_path": "products",
  "auth": {
    "type": "api_key",
    "api_key": "inventory-api-key",
    "header_name": "X-API-Key"
  },
  "params": {
    "category": "electronics",
    "in_stock": true
  },
  "headers": {
    "Accept": "application/json",
    "Content-Type": "application/json"
  }
}
```

## API Response Format

The API should return a JSON response. The system will look for an array of objects at the specified `data_path` (or root if not specified).

### Expected Response Structure

```json
{
  "data": {
    "employees": [
      {
        "employee_id": "EMP001",
        "full_name": "John Doe",
        "department": "IT"
      },
      {
        "employee_id": "EMP002", 
        "full_name": "Jane Smith",
        "department": "HR"
      }
    ]
  }
}
```

## Usage in Forms

1. **Create/Edit Form Field**: When creating a select field, you can either:
   - Add static options manually, OR
   - Configure an API source

### Enhanced User Experience with TomSelect

API-sourced dropdown fields automatically use TomSelect for an enhanced user experience:

- **Search functionality**: Users can type to search through options
- **Better performance**: Handles large option lists efficiently
- **Clear button**: Easy way to clear selections
- **Responsive design**: Works well on mobile devices
- **Loading indicators**: Shows loading state while fetching options
- **No results message**: Clear feedback when no options match search

TomSelect is automatically initialized for all API-sourced dropdown fields and provides a modern, user-friendly interface.

2. **Form Submission**: When users fill out the form, the select field will automatically load options from the configured API.

3. **Caching**: API responses are cached to improve performance and reduce API calls.

## API Endpoints

### Get Field Options
```
GET /api/forms/{form}/versions/{version}/fields/{field}/options
```

Returns the combined options (static + API) for a field.

### Test API Configuration
```
POST /api/test-api-config
```

Test an API configuration before saving it to a field.

### Clear Cache
```
DELETE /api/forms/{form}/versions/{version}/fields/{field}/cache
```

Clear the cache for a specific field's API options.

## Error Handling

- If the API is unavailable, the field will show an error message
- API errors are logged for debugging
- Cached data is used when available, even if the API is temporarily down
- Form submission is not blocked by API errors

## Security Considerations

- API tokens and credentials are stored in the database
- Use HTTPS for all API endpoints
- Implement proper authentication on your API endpoints
- Consider using environment variables for sensitive configuration

## Performance

- API responses are cached for the specified TTL
- Requests are made asynchronously to avoid blocking form loading
- Timeout settings prevent long waits for slow APIs
- Multiple fields can use the same API endpoint efficiently

## Troubleshooting

### Common Issues

1. **"Error loading options"**: Check API URL, authentication, and network connectivity
2. **Empty options**: Verify `value_field` and `label_field` match the API response structure
3. **Authentication errors**: Verify API credentials and authentication type
4. **Timeout errors**: Increase the timeout setting or optimize your API response time

### Debugging

- Check browser console for detailed error messages
- Review Laravel logs for API request/response details
- Use the test API configuration endpoint to validate settings
- Verify API response format matches expected structure

