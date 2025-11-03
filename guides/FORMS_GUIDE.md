# Form Management Guide

**SIGaP Forms Module**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [For Administrators: Creating Forms](#for-administrators-creating-forms)
3. [Form Field Types](#form-field-types)
4. [API-Sourced Dropdowns](#api-sourced-dropdowns)
5. [Calculated Fields](#calculated-fields)
6. [For Users: Submitting Forms](#for-users-submitting-forms)

---

## Overview

The Form Management module allows administrators to create dynamic forms with various field types, version control, and department-based access. This guide covers both creating forms (for administrators) and submitting forms (for users).

### Key Features

- **Dynamic Form Builder** - Create custom forms with 10+ field types
- **Form Versioning** - Complete version control with activation management
- **Department-based Access** - Granular form access control by organizational units
- **API Integration** - Dynamic dropdown options from external data sources
- **Form Prefilling** - Auto-populate forms based on user context

---

## For Administrators: Creating Forms

### Step 1: Create a New Form

1. Navigate to **Forms** in the sidebar

![Forms List](/guides-imgs/forms-list.png)

2. Click **"Create New Form"**
3. Fill in the form details:
   - **Form Number**: Unique identifier (e.g., `FORM-001`)
   - **Name**: Descriptive name
   - **Description**: Purpose of the form
   - **Departments**: Select which departments can access this form
   - **Requires Approval**: Check if submissions need approval

4. Click **"Create Form"**

### Step 2: Create a Form Version

Forms use version control to track changes over time.

1. From the form details page, click **"Create New Version"**
2. Enter version notes (what changed in this version)
3. Click **"Create Version"**

### Step 3: Add Form Fields

Click **"Add Field"** and configure the field properties.

#### Basic Field Configuration

Every field requires:

- **Label**: Field name shown to users
- **Name**: Internal field identifier (no spaces, use underscores)
- **Required**: Whether field is mandatory
- **Help Text**: Guidance for users (optional)
- **Validation**: Field-specific validation rules

#### Field Display Options

- **Width**: Full width or half width (for side-by-side fields)
- **Order**: Fields display in order (can be reordered)
- **Conditional Display**: Show/hide based on other field values

### Step 4: Activate Version

1. Once all fields are added, click **"Activate Version"**
2. This version becomes the active form for users
3. Only one version can be active at a time
4. Previous versions remain accessible for reference

---

## Form Field Types

### Text Fields

**Text**
- Single-line text input
- Use for: Names, short descriptions, codes
- Validation: Min/max length, regex patterns

**Textarea**
- Multi-line text input
- Use for: Long descriptions, comments, notes
- Configurable rows (height)

**Email**
- Email address validation
- Automatic format checking
- Use for: Contact information

### Number Fields

**Number**
- Numeric input only
- Use for: Quantities, measurements, counts
- Options: Min/max value, decimal places
- Step value for increments

### Date/Time Fields

**Date**
- Date picker interface
- Use for: Dates, deadlines, schedules
- Validation: Min/max dates, date ranges
- Format: Based on system locale

### Selection Fields

**Select Single**
- Dropdown with one choice
- Use for: Status, category selection, single options
- Options can be:
  - Manually entered
  - Loaded from API
  - Dependent on other fields

**Select Multiple**
- Dropdown with multiple choices
- Use for: Tags, multiple categories, multi-selection
- Supports search and filtering
- Uses TomSelect for enhanced UX

### File Fields

**File Upload**
- Upload files and images
- Use for: Documents, photos, PDFs
- Configuration:
  - Allowed file types
  - Maximum file size
  - Multiple file upload
  - Image watermarking (optional)
- Automatic image optimization

**Photo Capture**
- Live camera capture
- Use for: Field documentation, site photos
- Requires camera permission
- Automatic compression and watermarking

**Signature**
- Digital signature capture
- Use for: Approvals, sign-offs, acknowledgments
- Touch and mouse support
- Stored as image

### Calculated Fields

**Calculated**
- Auto-calculated based on other fields
- Use for: Totals, subtotals, formulas
- See [Calculated Fields](#calculated-fields) section

### Hidden Fields

**Hidden**
- Not visible to users
- Use for: Auto-populated data, system values
- Can be prefilled based on rules
- Useful for tracking metadata

---

## API-Sourced Dropdowns

For dynamic dropdown options from external systems, configure an API source in the field settings.

### Basic Configuration

When editing a Select field, configure the API source:

```json
{
  "url": "https://api.example.com/employees",
  "method": "GET",
  "value_field": "id",
  "label_field": "name",
  "cache_ttl": 300
}
```

### Configuration Options

| Field | Required | Description |
|-------|----------|-------------|
| `url` | Yes | API endpoint URL |
| `method` | No | HTTP method (GET, POST). Default: GET |
| `value_field` | Yes | Field name for option value |
| `label_field` | Yes | Field name for option label |
| `data_path` | No | Path to data array (e.g., `data.employees`) |
| `auth` | No | Authentication configuration |
| `params` | No | Query parameters or request body |
| `headers` | No | Additional HTTP headers |
| `timeout` | No | Request timeout (1-300 seconds) |
| `cache_ttl` | No | Cache duration (60-3600 seconds) |

### Combined Labels

Create descriptive labels by combining multiple fields:

**Examples:**
- `{name} - {department}` → "John Doe - HR"
- `{first_name} {last_name}` → "John Doe"
- `{name} ({position})` → "John Doe (Manager)"

### Authentication Types

#### No Authentication
```json
{
  "url": "https://api.example.com/public-data",
  "value_field": "id",
  "label_field": "name"
}
```

#### Bearer Token
```json
{
  "auth": {
    "type": "bearer",
    "token": "your-bearer-token"
  }
}
```

#### Basic Authentication
```json
{
  "auth": {
    "type": "basic",
    "username": "api-user",
    "password": "api-password"
  }
}
```

#### API Key
```json
{
  "auth": {
    "type": "api_key",
    "api_key": "your-api-key",
    "header_name": "X-API-Key"
  }
}
```

### TomSelect Enhancement

API-sourced dropdowns automatically use TomSelect for enhanced UX:
- Search functionality
- Better performance with large lists
- Clear button
- Loading indicators
- Responsive design

For more details, see **[API Options Guide](./API_OPTIONS_GUIDE.md)**.

---

## Calculated Fields

Use formulas to automatically calculate values based on other fields.

### Available Functions

**Mathematical Operations:**
- `SUM(field1, field2, field3, ...)` - Add values
- `MULTIPLY(field1, field2)` - Multiply values
- `DIVIDE(field1, field2)` - Divide values
- `SUBTRACT(field1, field2)` - Subtract values
- `AVERAGE(field1, field2, field3, ...)` - Calculate average

**Logical Operations:**
- `IF(condition, true_value, false_value)` - Conditional logic

### Formula Examples

**Simple Total:**
```
SUM(item1_cost, item2_cost, item3_cost)
```

**Line Total:**
```
MULTIPLY(quantity, unit_price)
```

**Discount Calculation:**
```
IF(total > 1000, MULTIPLY(total, 0.1), 0)
```

**Net Amount:**
```
SUBTRACT(gross_amount, discount_amount)
```

**Complex Example:**
```
IF(
  quantity > 100,
  MULTIPLY(quantity, unit_price, 0.9),
  MULTIPLY(quantity, unit_price)
)
```

### Tips for Calculated Fields

1. **Field Names**: Use exact field names (as defined in the Name field)
2. **Real-time**: Calculations update as users type
3. **Read-only**: Calculated fields cannot be manually edited
4. **Validation**: Ensure source fields are numeric
5. **Order**: Place calculated fields after their source fields

---

## For Users: Submitting Forms

### Viewing Available Forms

1. Navigate to **Form Submissions > Fill Form**

![Fill Forms Page](/guides-imgs/forms-submit-new.png)

2. You'll see all forms available to your department
3. Click on a form to start filling it out

### Filling Out a Form

#### Text and Number Fields

1. Click in the field and type your response
2. Required fields are marked with a red asterisk (*)
3. Help text appears below the field
4. Validation errors show in red

#### Date Fields

1. Click the date field to open the picker
2. Navigate to the desired month/year
3. Click on the date to select
4. Or manually type the date in the required format

#### Select Fields

**Single Select:**
1. Click the dropdown
2. Search or scroll to find your option
3. Click to select

**Multiple Select:**
1. Click the dropdown
2. Select multiple options
3. Click outside or press Enter to close
4. Remove selections with the X button

#### File Upload

1. Click **"Choose File"** or drag and drop files
2. Multiple files can be uploaded if configured
3. Wait for upload confirmation
4. File types and size limits are shown in help text

#### Photo Capture

1. Click **"Capture Photo"**
2. Allow camera access when prompted
3. Position the camera and take photo
4. Confirm or retake if needed
5. Photo is automatically compressed

#### Digital Signature

1. Click **"Sign"** button
2. Draw your signature with mouse or touch
3. Click **"Clear"** to start over
4. Click **"Save Signature"** when satisfied

### Submitting the Form

1. Review all required fields are completed
2. Check for validation errors (shown in red)
3. Click **"Submit"** at the bottom
4. Wait for confirmation message
5. You'll be redirected to the submission details

### Viewing Your Submissions

1. Navigate to **Form Submissions > My Submissions**
2. See all your submitted forms
3. Filter by:
   - Form type
   - Status (pending, approved, rejected)
   - Date range
   - Submission ID

4. Click on a submission to view details

### Editing Submissions

**If allowed:**
1. Open the submission
2. Click **"Edit"**
3. Make your changes
4. Click **"Update"**

**Note**: Submissions in approval workflows may not be editable.

### Printing Submissions

1. Open the submission details
2. Click **"Print"** button
3. A print-friendly version opens
4. Use browser print (Ctrl+P or Cmd+P)
5. Choose printer or save as PDF

---

## Best Practices

### For Administrators

**Form Design:**
- Use clear, descriptive field labels
- Provide helpful help text with examples
- Group related fields together
- Use appropriate field types for data
- Set realistic field validation

**Field Organization:**
- Order fields logically
- Use sections to break up long forms
- Place calculated fields after their sources
- Put most important fields at the top

**Testing:**
- Submit test forms before activation
- Test all field types and validations
- Check calculated fields work correctly
- Test on different devices/browsers
- Verify API integrations work

### For Users

**Before Submitting:**
- Read all field labels and help text carefully
- Have all required information ready
- Prepare files to upload in advance
- Double-check calculated values
- Review the entire form before submitting

**Data Entry:**
- Enter data accurately
- Use consistent formatting
- Don't leave required fields empty
- Upload correct file types
- Provide clear descriptions in text areas

---

## Troubleshooting

### Form Won't Submit

**Symptoms:**
- Submit button doesn't work
- Error messages appear

**Solutions:**
1. Check for required fields (marked with *)
2. Verify all validation messages are cleared
3. Check file upload size limits
4. Ensure all calculated fields show values
5. Try refreshing the page
6. Clear browser cache
7. Try a different browser

### File Upload Failing

**Symptoms:**
- Upload progress stops
- Error message appears

**Solutions:**
1. Check file size (usually max 10MB)
2. Verify file type is allowed
3. Check internet connection
4. Try a smaller file
5. Try different file format
6. Contact administrator if issue persists

### API Dropdown Not Loading

**Symptoms:**
- Dropdown shows "Loading..." or "Error"
- No options appear

**Solutions:**
1. Check internet connection
2. Wait a few seconds and try again
3. Refresh the page
4. Contact administrator to check API configuration
5. Check browser console for errors

### Calculated Field Not Working

**Symptoms:**
- Calculated field shows blank or zero
- Value doesn't update

**Solutions:**
1. Verify all source fields have values
2. Check source fields are numeric
3. Enter values in source fields first
4. Contact administrator if formula is incorrect

---

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[API Options Guide](./API_OPTIONS_GUIDE.md)** - Detailed API configuration
- **[Approval Workflows Guide](./WORKFLOWS_GUIDE.md)** - Form approval processes

---

**Last Updated**: October 17, 2025  
**Version**: 1.0

