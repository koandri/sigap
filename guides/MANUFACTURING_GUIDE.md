# Manufacturing & Inventory Guide

**SIGaP Manufacturing Module**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [Warehouse Management](#warehouse-management)
3. [Item Management](#item-management)
4. [Shelf-Based Inventory](#shelf-based-inventory)
5. [Bill of Materials (BoM)](#bill-of-materials-bom)
6. [Picklist Generation](#picklist-generation)
7. [Reports and Analytics](#reports-and-analytics)

---

## Overview

The Manufacturing module provides comprehensive warehouse and inventory management with shelf-based organization, Bill of Materials (BoM), and advanced inventory tracking.

### Key Features

- **Multi-Warehouse Management** - Multiple warehouses with shelf-based organization
- **Shelf-Based Inventory** - Organize by warehouse, shelf, and position
- **Item Management** - Comprehensive catalog with categories
- **Bill of Materials** - Recipe and ingredient management
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

## Bill of Materials (BoM)

### Creating a BoM

1. Navigate to **Manufacturing > Bill of Materials**
2. Click **"Create BoM"**
3. Fill in BoM details:
   - **Code**: BoM identifier (e.g., `BOM-001`)
   - **Name**: Product name
   - **Type**: Select type:
     - Raw Material
     - Finished Goods
     - Semi-Finished
     - Packaging
   - **Base Quantity**: Production batch size
   - **Unit**: Output unit
   - **Description**: Product details
   - **Active**: Enable BoM
4. Click **"Create"**

### Adding Ingredients

1. In BoM details, click **"Add Ingredient"**
2. Select item from inventory
3. Enter required quantity per base quantity
4. Confirm unit matches
5. Add notes (optional)
6. Repeat for all ingredients
7. Click **"Save"**

**Example BoM:**
```
Product: Fish Cake (100 kg batch)
Ingredients:
- Fish Meat: 70 kg
- Flour: 20 kg
- Seasoning: 5 kg
- Water: 5 kg
```

### Using BoMs for Production

#### Calculate Requirements

1. Open BoM details
2. Click **"Calculate Requirements"**
3. Enter production quantity needed
4. System calculates:
   - Required quantities of each ingredient
   - Available stock
   - Shortages (if any)
   - Cost estimate (if prices available)

#### Generate Picklist from BoM

1. After calculating requirements
2. Click **"Generate Picklist"**
3. System creates FIFO-based picklist
4. Shows exact locations to pick from
5. Print picklist for warehouse operators

### BoM Approval Workflow

If approval is required:

1. Create or edit BoM
2. Click **"Submit for Approval"**
3. BoM enters approval workflow
4. Approvers review:
   - Ingredient list
   - Quantities
   - Costs
   - Feasibility
5. Approved BoMs can be used for production
6. Rejected BoMs must be revised

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
6. Check off each line
7. Return completed picklist

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

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[Maintenance Guide](./MAINTENANCE_GUIDE.md)** - Parts inventory integration

---

**Last Updated**: October 17, 2025  
**Version**: 1.0

