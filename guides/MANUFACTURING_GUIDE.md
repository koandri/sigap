# Manufacturing & Inventory Guide

**SIGaP Manufacturing Module**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [Warehouse Management](#warehouse-management)
3. [Item Management](#item-management)
4. [Shelf-Based Inventory](#shelf-based-inventory)
5. [Recipe Management](#recipe-management)
6. [Production Planning System](#production-planning-system)
7. [Picklist Generation](#picklist-generation)
8. [Reports and Analytics](#reports-and-analytics)
9. [Temperature Sensor Monitoring](#temperature-sensor-monitoring)

---

## Overview

The Manufacturing module provides comprehensive warehouse and inventory management with shelf-based organization, Recipe management, Production Planning System, and advanced inventory tracking.

### Key Features

- **Multi-Warehouse Management** - Multiple warehouses with shelf-based organization
- **Shelf-Based Inventory** - Organize by warehouse, shelf, and position
- **Item Management** - Comprehensive catalog with categories
- **Recipe Management** - Recipe and ingredient management (replaces BoM)
- **Production Planning System** - 5-step production planning workflow
- **FIFO Tracking** - First In, First Out inventory management
- **Expiry Tracking** - Monitor expiring items
- **Picklist Generation** - FIFO-based picking lists
- **Bulk Operations** - Bulk inventory updates
- **Excel Import/Export** - Import items and export reports

---

## Warehouse Management

### Creating a Warehouse

1. Navigate to **Manufacturing > Warehouses**

![Warehouses List](/guides-imgs/manufacturing-warehouses.png)

2. Click **"Create Warehouse"**
3. Fill in details:
   - **Code**: Short code (e.g., `WH-01`, `COLD-01`)
   - **Name**: Warehouse name (e.g., "Main Warehouse", "Cold Storage")
   - **Description**: Location and purpose
   - **Active**: Enable warehouse
4. Click **"Create"**

**Best Practices:**
- Use consistent naming convention
- Include location in name
- Document temperature/special requirements
- Plan shelf organization before creating

### Managing Shelves

#### Creating Shelves

1. Open warehouse details
2. Click **"Shelf Management"**
3. Click **"Create Shelf"**
4. Fill in details:
   - **Code**: Shelf identifier (e.g., `A-01`, `B-05`)
   - **Name**: Descriptive name (e.g., "Aisle A Shelf 1")
   - **Aisle**: Aisle number or letter
   - **Active**: Enable shelf
5. Click **"Create"**

#### Creating Positions

Each shelf has multiple positions for storing items.

1. Open shelf details
2. Click **"Add Position"**
3. Configure position:
   - **Code**: Position code (e.g., `A-01-01`, `B-05-03`)
   - **Name**: Position name (optional)
   - **Level**: Shelf level (top, middle, bottom)
   - **Active**: Enable position
4. Click **"Create"**

**Shelf Organization Tips:**
- Use alpha-numeric codes (A-01-01: Aisle-Shelf-Position)
- Label positions clearly
- Group similar items together
- Reserve positions for specific item types
- Plan for expansion

### Warehouse Visualization

The system provides visual representations:
- **Grid View**: See all shelves at a glance
- **3-Column Layout**: Easy navigation through aisles
- **Color Coding**: 
  - Green: Available capacity
  - Yellow: Partially filled
  - Red: Full or expiring items

---

## Item Management

### Item Categories

#### Creating Categories

1. Navigate to **Manufacturing > Item Categories**
2. Click **"Create Category"**
3. Enter:
   - **Code**: Category code (e.g., `RM-FISH`)
   - **Name**: Category name (e.g., "Raw Material - Fish")
   - **Description**: Category details
4. Click **"Create"**

**Common Categories:**
- Raw Materials
- Packaging Materials
- Finished Goods
- Work in Progress
- Consumables
- Spare Parts

### Adding Items

![Items List](/guides-imgs/manufacturing-items.png)

#### Manual Entry

1. Navigate to **Manufacturing > Items**
2. Click **"Create Item"**
3. Fill in item details:
   - **Code**: Unique item code/SKU
   - **Name**: Item name
   - **Category**: Select category
   - **Unit**: Unit of measurement (kg, pcs, liters, boxes, etc.)
   - **Price**: Unit price (optional)
   - **Minimum Stock**: Reorder level
   - **Description**: Additional details
   - **Active**: Enable item
4. Click **"Create"**

#### Excel Import

For bulk item creation:

1. Navigate to **Manufacturing > Items**
2. Click **"Import from Excel"**
3. Download the template
4. Fill in item data:
   - Follow template format exactly
   - Include all required columns
   - Use valid category codes
   - Use consistent units
5. Upload completed file
6. Review import preview
7. Confirm import

**Import Template Columns:**
- Code (required)
- Name (required)
- Category Code (required)
- Unit (required)
- Price (optional)
- Minimum Stock (optional)
- Description (optional)

**Import Best Practices:**
- Validate data before import
- Use consistent formatting
- Check for duplicate codes
- Back up existing data
- Test with small batch first

### Item Details

Each item tracks:
- **Identification**: Code, name, category
- **Specifications**: Unit, price, description
- **Inventory**: Current stock across all warehouses
- **Locations**: Where item is stored
- **History**: All movements and transactions
- **Expiry**: Items with upcoming expiration

---

## Shelf-Based Inventory

### Adding Items to Positions

1. Navigate to **Manufacturing > Warehouses**
2. Select warehouse
3. Click **"Shelf Inventory"**
4. Navigate to specific shelf and position
5. Click **"Add Item"**
6. Fill in details:
   - **Item**: Select from catalog
   - **Quantity**: Amount to add
   - **Unit**: Confirm unit of measurement
   - **Batch Number**: Batch/lot code
   - **Expiry Date**: Expiration date (if applicable)
   - **Notes**: Additional information
7. Click **"Add to Position"**

**Data Entry Tips:**
- Use scanner for batch numbers when possible
- Double-check expiry dates
- Verify quantities before confirming
- Add meaningful notes
- Take photos if needed

### Updating Inventory

#### Adjusting Quantity

1. Navigate to position with item
2. Click **"Update Quantity"**
3. Enter new quantity
4. Select reason:
   - Physical count
   - Correction
   - Damage/spoilage
   - Production use
   - Other (specify)
5. Add notes explaining change
6. Click **"Update"**

**Audit Trail:**
- System tracks who made change
- Timestamp recorded
- Reason documented
- Previous quantity saved
- Available in history

#### Moving Items

1. Click **"Move Item"** from current position
2. Select destination:
   - Warehouse
   - Shelf
   - Position
3. Enter quantity to move
4. Verify destination is correct
5. Add notes (optional)
6. Confirm movement

**Movement Tracking:**
- Complete transaction history
- Source and destination logged
- Quantities tracked
- User and timestamp recorded
- Reversible if needed

### Bulk Operations

For large inventory updates:

1. Navigate to warehouse
2. Click **"Bulk Edit"**
3. Select scope:
   - Entire warehouse
   - Specific aisle
   - Multiple positions
4. Choose operation:
   - Update quantities
   - Move items
   - Adjust expiry dates
   - Mark for inventory
5. Upload data or enter manually
6. Review changes
7. Confirm bulk update

---

## Recipe Management

The Recipe system replaces the previous BoM (Bill of Materials) system, providing better versioning and date tracking for production recipes.

### Creating a Recipe

1. Navigate to **Manufacturing > Recipes**
2. Click **"Create Recipe"**
3. Fill in recipe details:
   - **Dough Item**: Select the dough/product this recipe is for
   - **Name**: Recipe name (e.g., "Standard Recipe v1.0")
   - **Recipe Date**: Date when recipe is created/effective
   - **Description**: Recipe details and notes
   - **Active**: Enable recipe
4. Click **"Create"**

### Adding Ingredients

1. In recipe details, click **"Add Ingredient"**
2. Select ingredient item from inventory
3. Enter required quantity
4. Confirm unit matches
5. Set sort order (display order)
6. Add notes (optional)
7. Repeat for all ingredients
8. Click **"Save"**

**Example Recipe:**
```
Dough: Adonan Kancing
Recipe: Standard Recipe v1.0 (2025-01-15)
Ingredients:
- Tepung Tapioka: 50 kg
- Ikan Tenggiri: 30 kg
- Garam: 2 kg
- Bumbu: 1 kg
```

### Recipe Versioning

- Each recipe has a date (recipe_date) for version tracking
- Multiple recipes can exist for the same dough item
- Production plans reference specific recipes by date
- Recipe ingredients are snapshotted when used in production plans

### Recipe Approval Workflow

If approval is required:

1. Create or edit recipe
2. Click **"Submit for Approval"**
3. Recipe enters approval workflow
4. Approvers review:
   - Ingredient list
   - Quantities
   - Costs
   - Feasibility
5. Approved recipes can be used in production plans
6. Rejected recipes must be revised

---

## Production Planning System

The Production Planning System manages the complete 5-step production planning workflow for cracker manufacturing, from dough production through packing materials.

### Overview

The system tracks planned quantities by distribution channel (GL1, GL2, TA, BL) and supports a sequential planning process:

1. **Step 1**: Dough Production Planning (Adonan) with recipe selection
2. **Step 2**: Gelondongan Production Planning from Adonan
3. **Step 3**: Kerupuk Kering Production Planning from Gelondongan
4. **Step 4**: Packing Planning for finished products
5. **Step 5**: Packing Materials Planning (material requirements)

### Creating a Production Plan

1. Navigate to **Manufacturing > Production Plans**
2. Click **"Create Production Plan"**
3. Fill in plan details:
   - **Plan Date**: Date when plan is created
   - **Production Start Date**: Automatically calculated (plan_date + 1 day)
   - **Ready Date**: Automatically calculated (production_start_date + 2 days)
   - **Notes**: Additional information
4. Click **"Create"**

The plan starts in **Draft** status and you can now add planning steps.

### Step 1: Dough Production Planning

1. After creating the plan, you'll see the plan details page
2. Click **"Create Step 1"** or navigate to Step 1 tab
3. For each dough type:
   - **Dough Item**: Select dough item (Adonan)
   - **Recipe**: Select recipe for this dough
   - **Quantities**: Enter planned quantities for each channel:
     - **GL1**: Quantity for GL1 site
     - **GL2**: Quantity for GL2 site
     - **TA**: Quantity for TA site
     - **BL**: Quantity for BL site
4. Click **"Add Row"** to add more dough types
5. Click **"Save Step 1"**

**Recipe Ingredients:**
- When a recipe is selected, its ingredients are automatically loaded
- Ingredients are stored as a snapshot in the production plan
- This ensures historical accuracy even if recipes change later

### Step 2: Gelondongan Production Planning

1. After Step 1 is complete, click **"Step 2"** tab or **"Create Step 2"**
2. System auto-calculates Gelondongan quantities from Step 1 using yield guidelines
3. Review and adjust quantities if needed:
   - **Adonan Item**: Source dough item
   - **Gelondongan Item**: Target gelondongan item
   - **Adonan Quantities**: Quantities per channel (from Step 1)
   - **Gelondongan Quantities**: Calculated quantities (can be adjusted)
4. Click **"Save Step 2"**

**Yield Guidelines:**
- System uses yield guidelines to calculate conversions
- Yield guidelines are managed in **Manufacturing > Yield Guidelines**
- Different yields for different product types (Kancing, Gondang, Mentor, Mini)

### Step 3: Kerupuk Kering Production Planning

1. After Step 2 is complete, click **"Step 3"** tab or **"Create Step 3"**
2. System auto-calculates Kerupuk Kering quantities from Step 2 using yield guidelines
3. Review and adjust quantities:
   - **Gelondongan Item**: Source gelondongan item
   - **Kerupuk Kering Item**: Target kerupuk kering item
   - **Gelondongan Quantities**: Quantities per channel (from Step 2)
   - **Kg Quantities**: Calculated quantities in kilograms (can be adjusted)
4. Click **"Save Step 3"**

### Step 4: Packing Planning

1. After Step 3 is complete, click **"Step 4"** tab or **"Create Step 4"**
2. System auto-calculates packing quantities from Step 3 using weight configurations
3. For each packing type:
   - **Kerupuk Kering Item**: Source kerupuk kering item
   - **Packing Item**: Select packing SKU
   - **Kg Quantities**: Available from Step 3 (read-only)
   - **Packing Quantities**: Calculated based on weight per pack (can be adjusted)
4. System validates that packing quantities don't exceed available kg quantities
5. Click **"Save Step 4"**

**Weight Configuration:**
- System uses Kerupuk Pack Configuration to determine weight per pack
- Different pack sizes have different weights
- Configuration is managed in the system settings

### Step 5: Packing Materials Planning

1. After Step 4 is complete, click **"Step 5"** tab or **"Create Step 5"**
2. System shows all Pack SKUs from Step 4
3. For each Pack SKU, add required packing materials:
   - **Pack SKU**: Selected automatically
   - **Packing Material**: Select material item (e.g., plastic, dos)
   - **Quantity Total**: Total quantity needed for all packs
4. System can auto-calculate from Packing Material Blueprints if configured
5. Click **"Save Step 5"**

**Packing Material Blueprints:**
- Define standard material requirements per pack SKU
- Used for automatic calculation
- Managed in the system settings

### Approving a Production Plan

1. After all 5 steps are complete, review the plan
2. Click **"Approve"** button
3. Plan status changes from **Draft** to **Approved**
4. Once approved, the plan cannot be edited (unless you have special permissions)

**Approval Requirements:**
- All 5 steps must be completed
- Plan must be in Draft status
- User must have approval permissions

### Viewing Production Plans

The plan overview page shows:
- **Plan Information**: Dates, status, creator, approver
- **Step Tabs**: Navigate between all 5 steps
- **Totals**: Summary of quantities across all steps
- **Status Indicators**: Visual indicators for completion status

### Editing Production Plans

**Draft Plans:**
- Can edit any step
- Must delete later steps before editing earlier steps
- Example: To edit Step 1, you must delete Step 2, 3, 4, and 5 first

**Approved Plans:**
- Cannot be edited
- Read-only view
- Ready for production execution

### Production Plan Status Workflow

```
Draft → Approved → In Production → Completed
```

- **Draft**: Plan is being created and can be edited
- **Approved**: Plan is finalized and ready for production
- **In Production**: Production has started (future feature)
- **Completed**: Production is finished (future feature)

### Yield Guidelines Management

Yield guidelines define conversion rates between production stages:

1. Navigate to **Manufacturing > Yield Guidelines**
2. Click **"Create Yield Guideline"**
3. Configure:
   - **Product Type**: Kancing, Gondang, Mentor, or Mini
   - **From Stage**: Adonan, Gelondongan, or Kerupuk Kg
   - **To Stage**: Gelondongan, Kerupuk Kg, or Packing
   - **Yield Quantity**: Conversion rate (e.g., 3.9 means 1 unit from → 3.9 units to)
   - **Unit**: Unit of measurement
   - **Active**: Enable guideline
4. Click **"Create"**

**Example:**
- Product Type: Kancing
- From: Gelondongan
- To: Kerupuk Kg
- Yield: 3.9
- Meaning: 1 Gelondongan → 3.9 Kg Kerupuk Kering

---

## Picklist Generation

### Global Picklist

Create picklists across all warehouses:

1. Navigate to **Manufacturing > Picklists**
2. Click **"Generate Picklist"**
3. Select items and quantities:
   - Choose from available items
   - Enter quantity needed for each
   - System checks availability
4. Click **"Generate"**

### FIFO Logic

System automatically applies First-In-First-Out:

**Priority Order:**
1. Earliest expiry date first
2. Oldest batch number
3. Longest time in inventory
4. Closest to warehouse exit (optional)

**Picklist Shows:**
- Warehouse location
- Shelf and position codes
- Batch number
- Expiry date
- Quantity to pick
- Visual map (if available)

### Using Picklists

**For Warehouse Operators:**

1. Print or view picklist on mobile
2. Follow listed order
3. Go to each location
4. Verify batch number
5. Pick specified quantity

### Generate Picklist from Recipe

1. Open recipe details
2. Click **"Calculate Requirements"**
3. Enter production quantity needed
4. System calculates:
   - Required quantities of each ingredient
   - Available stock
   - Shortages (if any)
   - Cost estimate (if prices available)
5. Click **"Generate Picklist"**
6. System creates FIFO-based picklist
7. Shows exact locations to pick from
8. Print picklist for warehouse operators
9. Check off each line
10. Return completed picklist

**After Picking:**
- Update inventory quantities
- Record actual picked quantities
- Note any discrepancies
- Document reasons for variances

---

## Reports and Analytics

### Warehouse Overview Report

View inventory across all warehouses:

1. Navigate to **Manufacturing > Overview Report**
2. Apply filters:
   - **Warehouse**: Specific warehouse or all
   - **Item Category**: Filter by category
   - **Item Name**: Search specific items
   - **Expiry Filter**: 
     - Expired
     - Expiring soon (< 30 days)
     - Expiring this week
     - All items
3. Click **"Apply Filters"**

**Report Shows:**
- Item details
- Warehouse and location
- Quantity on hand
- Batch numbers
- Expiry dates
- Last updated info
- Total value (if prices available)

**Export Options:**
- Excel spreadsheet
- PDF report
- Print directly

### Shelf Report

Detailed position-by-position inventory:

1. Open warehouse details
2. Click **"Shelf Report"**
3. Select report type:
   - All shelves
   - Specific aisle
   - Single shelf
4. Generate report

**Report Includes:**
- Shelf and position codes
- Items stored
- Quantities
- Batch and expiry info
- Occupancy percentage
- Available capacity

**Use Cases:**
- Physical inventory counts
- Capacity planning
- Optimization analysis
- Audit compliance

### Expiring Items Report

Monitor items approaching expiration:

1. Navigate to Manufacturing Dashboard
2. View **"Expiring Items"** section
3. See items expiring in next 30 days
4. Click for detailed report

**Actions:**
- Plan usage before expiry
- Generate picklist to use first
- Initiate quality checks
- Plan promotions or discounts
- Update inventory if expired

### Low Stock Report

Track items below minimum levels:

1. Dashboard shows **"Low Stock Alerts"**
2. Click for detailed report
3. See items below reorder point

**Actions:**
- Create purchase orders
- Adjust minimum stock levels
- Plan production
- Check alternative suppliers

### Inventory Value Report

Calculate total inventory value:

**Requirements:**
- Item prices must be entered
- Quantities must be current

**Report Shows:**
- Value by item
- Value by category
- Value by warehouse
- Total inventory value
- Value trends over time

---

## Best Practices

### Inventory Accuracy

**Regular Counts:**
- Conduct cycle counts regularly
- Full physical inventory quarterly
- Investigate discrepancies immediately
- Update system promptly

**Data Entry:**
- Enter data in real-time
- Use scanners when available
- Double-check quantities
- Verify batch numbers and expiry dates

### FIFO Management

**Proper Rotation:**
- Always use oldest stock first
- Follow system-generated picklists
- Check expiry dates during picking
- Rotate stock during restocking

**Expiry Prevention:**
- Monitor expiring items weekly
- Plan usage before expiration
- Conduct quality checks on aging items
- Properly dispose of expired items

### Organization

**Physical Layout:**
- Match physical layout to system
- Clear labeling on all positions
- Keep similar items together
- Optimize for pick efficiency

**System Maintenance:**
- Keep items catalog updated
- Archive obsolete items
- Maintain category structure
- Regular data cleanup

### Documentation

**Transactions:**
- Document all movements
- Note reasons for adjustments
- Attach photos when helpful
- Keep batch documentation

**Reporting:**
- Generate reports regularly
- Review for trends and issues
- Share with stakeholders
- Archive for compliance

---

## Troubleshooting

### Inventory Count Wrong

**Problem**: System shows incorrect quantity

**Solutions:**
1. Check recent transactions
2. Verify no pending movements
3. Review adjustment history
4. Conduct physical count
5. Adjust with documented reason
6. Investigate cause of discrepancy

### Can't Find Item

**Problem**: Item not showing in position

**Solutions:**
1. Check if item is active
2. Verify warehouse selection
3. Search by item code
4. Check alternative positions
5. Review movement history
6. Conduct physical search

### Picklist Not Generating

**Problem**: System can't create picklist

**Solutions:**
1. Verify items are in stock
2. Check quantities available
3. Ensure warehouses are active
4. Verify item locations
5. Check for system errors
6. Contact administrator

### Import Failed

**Problem**: Excel import not working

**Solutions:**
1. Verify file format (Excel .xlsx)
2. Check all required columns present
3. Validate data format
4. Remove special characters
5. Check for duplicate codes
6. Try smaller batch
7. Review error messages

---

## Temperature Sensor Monitoring

The Manufacturing Dashboard includes a real-time temperature sensor widget that displays temperature data from HomeAssistant sensors. This feature allows you to monitor temperature conditions in your manufacturing facilities.

![Temperature Sensor Widget](/guides-imgs/manufacturing-temperature-widget.png)

### Overview

The Temperature Sensor widget provides:
- **Real-time visualization** of temperature data using interactive line charts
- **Historical data** with configurable time ranges
- **Data sampling** to optimize chart performance
- **HomeAssistant integration** for seamless sensor data retrieval

### Accessing the Widget

1. Navigate to **Manufacturing > Dashboard**
2. The Temperature Sensor widget is displayed at the top of the dashboard
3. The widget shows temperature data for the configured sensor (default: `sensor.tes_temperature`)

### Using the Widget

#### Selecting Time Range

1. Click on the **Time Period** input field
2. A datetime range picker will appear
3. Select your desired start date and time
4. Select your desired end date and time
5. The chart will automatically update with data for the selected period

**Default Time Range**: The widget defaults to showing the last 8 hours of data.

#### Adjusting Data Sampling Interval

The interval dropdown allows you to control how frequently data points are sampled:

- **5 minutes**: More detailed view, more data points
- **15 minutes**: Balanced view
- **30 minutes**: Default setting, optimized for most use cases
- **60 minutes**: Less detailed, fewer data points (good for longer time ranges)

1. Select your desired interval from the **Interval** dropdown
2. The chart will automatically refresh with the new sampling interval

#### Manual Refresh

Click the **Reload** button to manually refresh the temperature data without changing the time range or interval.

### Chart Features

- **Interactive Line Chart**: Hover over data points to see exact temperature values and timestamps
- **Smooth Curves**: The chart uses smooth curves to better visualize temperature trends
- **Horizontal Grid Lines**: Dashed grid lines help read temperature values
- **Dark Blue Line**: Matches the site's primary color scheme
- **Responsive Design**: The chart adapts to different screen sizes

### Configuration

#### Environment Setup

To use the Temperature Sensor widget, you need to configure HomeAssistant connection settings in your `.env` file:

```env
# HomeAssistant Configuration
HA_BASE_URL=http://192.168.99.99:8123
HA_TOKEN=your_homeassistant_bearer_token_here
```

**Configuration Details:**
- `HA_BASE_URL`: The base URL of your HomeAssistant instance (default: `http://192.168.99.99:8123`)
- `HA_TOKEN`: Your HomeAssistant long-lived access token (Bearer token)

#### Getting a HomeAssistant Token

1. Log in to your HomeAssistant instance
2. Go to your profile (click on your name in the bottom left)
3. Scroll down to **Long-lived access tokens**
4. Click **Create Token**
5. Give it a name (e.g., "SIGaP Temperature Widget")
6. Copy the token and add it to your `.env` file as `HA_TOKEN`

#### Sensor Entity ID

The widget is configured to use the sensor entity `sensor.tes_temperature` by default. To change this, you would need to modify the controller code.

### Troubleshooting

#### Chart Not Loading

**Issue**: The chart shows a loading indicator but never displays data.

**Solutions**:
1. Check that `HA_TOKEN` is set in your `.env` file
2. Verify that `HA_BASE_URL` is correct and accessible
3. Ensure the sensor entity ID exists in HomeAssistant
4. Check browser console for error messages
5. Verify HomeAssistant API is accessible from your server

#### No Data Points

**Issue**: The chart loads but shows no data points.

**Solutions**:
1. Verify the sensor has data for the selected time range
2. Try expanding the time range
3. Check HomeAssistant logs for API errors
4. Verify the sensor entity ID is correct

#### Time Range Not Updating

**Issue**: Changing the time range doesn't update the chart.

**Solutions**:
1. Ensure both start and end times are selected
2. Check that start time is before end time
3. Try clicking the Reload button
4. Check browser console for JavaScript errors

### Best Practices

1. **Time Range Selection**: 
   - For real-time monitoring: Use shorter ranges (1-8 hours)
   - For trend analysis: Use longer ranges (24-48 hours) with higher intervals (30-60 minutes)

2. **Interval Selection**:
   - Use 5-15 minute intervals for detailed analysis
   - Use 30-60 minute intervals for overview and longer time ranges

3. **Performance**:
   - Larger time ranges with smaller intervals may take longer to load
   - Use appropriate intervals based on your time range

4. **Monitoring**:
   - Check the widget regularly for temperature anomalies
   - Use the historical data to identify patterns and trends

---

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[Maintenance Guide](./MAINTENANCE_GUIDE.md)** - Parts inventory integration

---

**Last Updated**: November 20, 2025  
**Version**: 1.1

