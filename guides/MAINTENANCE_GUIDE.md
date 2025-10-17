# Maintenance Management Guide (CMMS)

**SIGaP Computerized Maintenance Management System**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [Asset Management](#asset-management)
3. [Maintenance Schedules](#maintenance-schedules)
4. [Work Orders](#work-orders)
5. [Maintenance Logs](#maintenance-logs)
6. [Reports and Analytics](#reports-and-analytics)

---

## Overview

The Computerized Maintenance Management System (CMMS) module manages assets, schedules preventive maintenance, tracks work orders, and maintains complete maintenance history.

### Key Features

- **Asset Management** - Track all equipment with QR codes
- **Preventive Maintenance** - Schedule with multiple frequency types
- **Work Order Management** - Complete lifecycle tracking
- **Automatic Generation** - Auto-create work orders from schedules
- **Parts Integration** - Track parts used from inventory
- **Maintenance Calendar** - Visual planning tool
- **Reports & Analytics** - Performance metrics and cost tracking

---

## Asset Management

### Asset Categories

#### Creating Categories

1. Navigate to **Maintenance > Asset Categories**
2. Click **"Create Category"**
3. Enter details:
   - **Code**: Category code (e.g., `MTR`, `PUMP`)
   - **Name**: Category name (e.g., "Motors", "Pumps")
   - **Description**: Category details
   - **Active**: Enable category
4. Click **"Create"**

**Common Categories:**
- Motors and Drives
- Pumps and Compressors
- Conveyors and Material Handling
- Refrigeration Equipment
- Processing Equipment
- Packaging Machines
- HVAC Systems
- Electrical Systems

### Adding Assets

#### Creating an Asset

1. Navigate to **Maintenance > Assets**
2. Click **"Create Asset"**
3. Fill in asset details:

**Identification:**
- **Asset Number**: Unique identifier (e.g., `MTR-001`)
- **Name**: Asset name (e.g., "Main Conveyor Motor")
- **Category**: Select category

**Specifications:**
- **Manufacturer**: Brand/maker
- **Model**: Model number
- **Serial Number**: Serial number
- **Specifications**: Technical details (JSON format)

**Location:**
- **Location**: Physical location (dropdown):
  - Production Area
  - Warehouse
  - Cold Storage
  - Processing Room
  - Packaging Line
  - Office
  - Outdoor
  - Other

**Financial:**
- **Purchase Date**: Date acquired
- **Purchase Cost**: Initial cost
- **Warranty Expiry**: Warranty end date

**Status:**
- **Status**: Current status:
  - Operational (working normally)
  - Down (not working)
  - Under Maintenance
  - Retired (out of service)
- **Active**: Enable asset

**Additional:**
- **Notes**: Additional information
- **Installation Date**: When installed
- **Expected Life**: Years of expected service

4. Click **"Create Asset"**

### QR Code Generation

Generate QR codes for quick asset access:

1. Open asset details
2. Click **"Generate QR Code"**
3. QR code image appears
4. Options:
   - Print QR code
   - Download image
   - Attach to asset
5. Users can scan to view asset details

**QR Code Benefits:**
- Quick asset identification
- Mobile-friendly access
- View maintenance history
- Create work orders on-site
- Check maintenance schedules

### Asset Details View

Asset details page shows:
- **Information**: All asset data
- **Maintenance Schedules**: All schedules for this asset
- **Work Orders**: All work orders (past and present)
- **Maintenance History**: Complete log
- **Documents**: Manuals, certificates, photos
- **Parts Used**: History of parts consumed

---

## Maintenance Schedules

### Overview

Schedule regular maintenance with flexible frequency options. For detailed scheduling information, see the **[Enhanced Scheduling Guide](./ENHANCED_SCHEDULING_GUIDE.md)**.

### Creating a Schedule

1. Navigate to **Maintenance > Schedules**
2. Click **"Create Schedule"**
3. Fill in details:

**Basic Information:**
- **Asset**: Select asset to maintain
- **Maintenance Type**: Select type:
  - Inspection
  - Preventive Maintenance
  - Predictive Maintenance
  - Corrective Maintenance
  - Emergency
  - Calibration
  - Cleaning
  - Lubrication

**Frequency Configuration:**
- **Frequency Type**: Select type:
  - Hourly
  - Daily
  - Weekly
  - Monthly
  - Yearly

**Schedule Details:**
- **Description**: What needs to be done
- **Checklist**: Add checklist items (optional)
- **Assigned To**: Default technician
- **Active**: Enable schedule

4. Click **"Create Schedule"**

### Frequency Types

#### Hourly

**Example**: Every 4 hours
```
Configuration:
- Interval: 4
Result: "Every 4 hours"
```

**Use Case**: Critical equipment requiring frequent checks

#### Daily

**Example**: Every day
```
Configuration:
- Interval: 1
Result: "Daily"
```

**Use Case**: Daily inspections and routine checks

#### Weekly

**Example**: Monday, Wednesday, Friday
```
Configuration:
- Interval: 1 week
- Days: [Monday, Wednesday, Friday]
Result: "Every Monday, Wednesday, Friday"
```

**Use Case**: Regular maintenance on specific weekdays

#### Monthly

**Three sub-types:**

**A. Specific Date**
```
Configuration:
- Type: Date of month
- Date: 15
Result: "Monthly on the 15th"
```

**B. Last Day**
```
Configuration:
- Type: Last day of month
Result: "Monthly on the last day"
```

**C. Specific Weekday**
```
Configuration:
- Type: Day of week
- Week: 1 (First/Second/Third/Fourth/Last)
- Day: Monday
Result: "Monthly on the first Monday"
```

**Use Case**: Monthly preventive maintenance

#### Yearly

**Example**: Annual certification
```
Configuration:
- Month: June
- Date: 15
Result: "Yearly on June 15th"
```

**Use Case**: Annual certifications and major overhauls

### Managing Schedules

#### Viewing Schedules

1. Navigate to **Maintenance > Schedules**
2. Filter by:
   - Asset
   - Maintenance Type
   - Status (Active, Overdue, Upcoming)
   - Search by description

#### Editing Schedules

1. Open schedule details
2. Click **"Edit"**
3. Modify any fields
4. Next due date recalculates automatically
5. Click **"Update"**

#### Triggering Work Orders Manually

1. Open schedule details
2. Click **"Trigger Work Order"**
3. Work order is created immediately
4. Next due date updates
5. Assigned technician is notified

---

## Work Orders

### Work Order Lifecycle

Work orders flow through these statuses:

```
Submitted → Assigned → In Progress → Pending Verification → Verified → Completed
```

**Status Descriptions:**
- **Submitted**: Created, awaiting assignment
- **Assigned**: Assigned to technician
- **In Progress**: Technician working on it
- **Pending Verification**: Work done, awaiting supervisor review
- **Verified**: Supervisor approved work
- **Completed**: Fully completed and closed

### Creating a Work Order

#### Manual Creation

1. Navigate to **Maintenance > Work Orders**
2. Click **"Create Work Order"**
3. Fill in details:

**Basic Information:**
- **Asset**: Select asset
- **Maintenance Type**: Type of work
- **Priority**: 
  - Low (routine)
  - Medium (scheduled)
  - High (important)
  - Critical (urgent)

**Scheduling:**
- **Scheduled Date**: When to perform
- **Estimated Hours**: Time estimate

**Assignment:**
- **Assigned To**: Technician (optional initially)

**Description:**
- **Description**: Detailed work description
- **Notes**: Additional information

4. Click **"Create Work Order"**

#### Automatic Creation

Work orders are automatically generated:
- From overdue maintenance schedules
- Runs daily at midnight (if enabled)
- Assigns to technician from schedule
- Sets status to "Assigned"

### For Technicians: Completing Work

#### Starting Work

1. Navigate to **Maintenance > Work Orders**
2. Filter to show "Assigned to Me"
3. Open your work order
4. Click **"Start Work"**
5. Status changes to "In Progress"
6. Start time recorded

#### During Work

**Log Progress:**
1. Click **"Log Progress"**
2. Enter notes about progress
3. Timestamp recorded
4. Visible in work order history

**Add Actions:**
1. Click **"Add Action"**
2. Describe action taken
3. Helps document work performed

**Upload Photos:**
1. Click **"Upload Photo"**
2. Select or capture photo
3. Add description
4. Photos attached to work order

**Add Parts:**
1. Click **"Add Parts"**
2. Select part from inventory
3. Enter quantity used
4. Parts deducted from inventory

#### Completing Work

1. Click **"Submit for Verification"**
2. Fill in completion details:
   - **Actual Hours**: Time spent
   - **Action Taken**: What was done (required)
   - **Findings**: What was found
   - **Recommendations**: Future recommendations
   - **Cost**: Labor or external costs
   - **Parts Used**: Select from inventory
3. Click **"Submit"**

**What Happens:**
- Status changes to "Pending Verification"
- Supervisor is notified
- Maintenance log created
- Parts inventory updated

### For Supervisors: Verifying Work

#### Viewing Pending Work Orders

1. Navigate to **Maintenance > Work Orders**
2. Filter: "Pending Verification"
3. See work orders awaiting review

#### Reviewing Work

1. Open work order
2. Review:
   - Work performed
   - Parts used
   - Photos uploaded
   - Technician notes
   - Time spent
3. Check quality of work

#### Verifying Work

**If Satisfactory:**
1. Click **"Verify"**
2. Add verification notes (optional)
3. Work order status → "Verified"
4. System updates:
   - Asset maintenance history
   - Next due date (if from schedule)
   - Performance metrics

**If Not Satisfactory:**
1. Click **"Reject"** or "Send Back"
2. Add specific feedback
3. Work order returns to technician
4. Technician addresses issues

### Work Order Actions

#### Assigning Work Orders

1. Open work order in "Submitted" status
2. Click **"Assign"**
3. Select technician
4. Status changes to "Assigned"
5. Technician receives notification

#### Updating Status

Administrators can manually update:
1. Open work order
2. Click **"Update Status"**
3. Select new status
4. Add reason for change
5. Confirm update

#### Closing Work Orders

After verification:
1. Click **"Close Work Order"**
2. Final status → "Completed"
3. Can no longer be edited
4. Archived in maintenance logs

---

## Maintenance Logs

### Viewing Logs

1. Navigate to **Maintenance > Logs**
2. See complete maintenance history
3. Filter by:
   - Asset
   - Date range
   - Maintenance type
   - Performed by (technician)

### Asset History

View all maintenance for specific asset:

1. Click **"Asset History"**
2. Select asset
3. See:
   - All work orders
   - All maintenance logs
   - Parts used
   - Costs
   - Downtime
   - Trends

**Use Cases:**
- Asset reliability analysis
- Cost tracking
- Maintenance planning
- Audit compliance
- Warranty claims

### Maintenance Log Details

Each log contains:
- **Asset**: Which asset was maintained
- **Performed By**: Technician who did work
- **Performed At**: Date and time
- **Action Taken**: What was done
- **Findings**: What was discovered
- **Recommendations**: Future actions
- **Cost**: Financial cost
- **Parts Used**: Items consumed
- **Photos**: Documentation

---

## Reports and Analytics

### Dashboard

Main dashboard shows:
- **Total Assets**: All active assets
- **Active Work Orders**: Open work orders
- **Overdue Schedules**: Past due maintenance
- **Upcoming Schedules**: Next 7 days

**Charts:**
- Asset status distribution
- Work order priority distribution
- Recent work orders list
- Upcoming maintenance list

### Maintenance Reports

1. Navigate to **Maintenance > Reports**
2. Select date range
3. Apply filters:
   - Asset
   - Category
   - Maintenance type

**Report Metrics:**
- Total work orders completed
- Total cost
- Total hours
- Average completion time
- Maintenance type breakdown
- Asset with most work
- Cost per asset
- Technician performance

**Export Options:**
- Excel spreadsheet
- PDF report
- Print

### Maintenance Calendar

Visual calendar view:

1. Navigate to **Maintenance > Calendar**
2. View all scheduled maintenance
3. Color-coded by:
   - Maintenance type
   - Priority
   - Status

**Features:**
- Month, week, day views
- Click event for details
- Create work orders from calendar
- Filter by asset or type

### Key Performance Indicators (KPIs)

**Asset Reliability:**
- Mean Time Between Failures (MTBF)
- Mean Time To Repair (MTTR)
- Asset uptime percentage
- Failure rate trends

**Maintenance Effectiveness:**
- Preventive vs. corrective ratio
- Schedule compliance rate
- Average response time
- Average completion time

**Cost Analysis:**
- Maintenance cost per asset
- Parts cost trends
- Labor cost tracking
- Budget vs. actual

---

## Best Practices

### For Maintenance Managers

**Planning:**
- Set realistic maintenance schedules
- Balance preventive and predictive maintenance
- Plan for seasonal requirements
- Budget for parts and labor

**Resource Management:**
- Assign appropriate technicians
- Balance workload
- Ensure proper training
- Maintain spare parts inventory

**Performance Monitoring:**
- Review KPIs regularly
- Identify problematic assets
- Track maintenance costs
- Adjust schedules as needed

### For Technicians

**Documentation:**
- Log all work performed
- Take before/after photos
- Document parts used
- Note any anomalies

**Communication:**
- Update work order status promptly
- Report issues immediately
- Provide clear recommendations
- Communicate with supervisors

**Quality:**
- Follow maintenance procedures
- Complete checklist items
- Verify work before submission
- Clean up after work

### For Supervisors

**Verification:**
- Review work orders promptly
- Verify quality of work
- Check parts usage
- Provide constructive feedback

**Planning:**
- Review upcoming schedules
- Anticipate resource needs
- Plan for complex work
- Coordinate with operations

---

## Troubleshooting

### Work Order Not Generating

**Problem**: Scheduled maintenance isn't creating work orders

**Solutions:**
1. Verify schedule is active
2. Check next due date is past
3. Verify automatic generation is enabled (see `routes/console.php`)
4. Check system logs for errors
5. Manually trigger work order
6. Contact system administrator

### Can't Complete Work Order

**Problem**: Buttons disabled or workflow stuck

**Solutions:**
1. Verify work order status is correct
2. Check you're assigned to work order
3. Ensure all required fields filled
4. Check if parts are available
5. Verify permissions
6. Contact supervisor

### Parts Not Deducting

**Problem**: Inventory not updating after work order

**Solutions:**
1. Verify parts were added to work order
2. Check parts exist in inventory
3. Verify sufficient quantity available
4. Check inventory permissions
5. Review transaction history
6. Contact administrator

### Schedule Dates Wrong

**Problem**: Next due date is incorrect

**Solutions:**
1. Review frequency configuration
2. Check for manual overrides
3. Verify last maintenance date
4. Recalculate using "Edit" and "Save"
5. Check for system timezone issues
6. Contact administrator

---

## Integration with Manufacturing

### Parts Inventory

CMMS integrates with manufacturing inventory:

**When Parts Used:**
1. Select part from inventory
2. Enter quantity used
3. System automatically:
   - Deducts from warehouse position
   - Records transaction
   - Updates part cost
   - Tracks usage history

**Viewing Parts:**
- Work order shows parts used
- Maintenance log records parts
- Inventory shows maintenance consumption
- Reports track parts costs

### Inventory Planning

**Spare Parts Management:**
- Track frequently used parts
- Set minimum stock levels for spares
- Generate purchase requests
- Monitor spare parts inventory
- Forecast parts needs

---

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[Enhanced Scheduling Guide](./ENHANCED_SCHEDULING_GUIDE.md)** - Detailed scheduling information
- **[Manufacturing Guide](./MANUFACTURING_GUIDE.md)** - Parts inventory management

---

**Last Updated**: October 17, 2025  
**Version**: 1.0

