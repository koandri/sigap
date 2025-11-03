# Approval Workflows Guide

**SIGaP Approval Workflows Module**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [For Administrators: Creating Workflows](#for-administrators-creating-workflows)
3. [For Approvers: Processing Approvals](#for-approvers-processing-approvals)
4. [Workflow Types](#workflow-types)
5. [Best Practices](#best-practices)

---

## Overview

![Workflows Guide](/guides-imgs/workflows-guide.png)

Approval workflows enable multi-level approval processes for form submissions. Workflows can be sequential (one after another) or parallel (simultaneous approvals).

### Key Features

- **Sequential Workflows** - Step-by-step approval chains
- **Parallel Workflows** - Simultaneous multi-approver processes
- **Multi-level Approvals** - Unlimited approval steps
- **Role-based Assignment** - Assign approvers by user, role, or department
- **SLA Management** - Set time limits with automatic escalation
- **Complete Audit Trail** - Full logging with metadata and timestamps

---

## For Administrators: Creating Workflows

### Step 1: Create Workflow

1. Navigate to a form's details page
2. Click **"Approval Workflows"**
3. Click **"Create Workflow"**
4. Fill in workflow details:
   - **Name**: Workflow identifier (e.g., "Standard Approval")
   - **Description**: Purpose of workflow
   - **Active**: Whether this workflow is currently in use

5. Click **"Create"**

### Step 2: Add Approval Steps

Each workflow consists of one or more approval steps.

#### Adding a Step

1. Click **"Add Step"**
2. Configure step properties:

**Basic Configuration:**
- **Step Number**: Order of approval (1, 2, 3, etc.)
- **Step Name**: Descriptive name (e.g., "Supervisor Review")
- **Description**: What this step validates

**Approver Configuration:**
- **Approver Type**: 
  - Specific User
  - Any user with Role
  - Department Head
  - Form Creator's Manager
- **Approver**: Select user or role

**Requirements:**
- **Required**: Is this step mandatory?
  - Required steps must be completed
  - Optional steps can be skipped
- **Auto-approve**: Skip if certain conditions met

**SLA Configuration:**
- **SLA (hours)**: Time limit for approval
- **Escalate**: Auto-escalate if overdue
- **Escalate To**: Who receives escalation (usually manager)

3. Click **"Save Step"**

#### Step Order

- **Sequential**: Steps execute in numerical order (1, 2, 3...)
- **Parallel**: Multiple steps with same number execute simultaneously
  - Example: Step 2a, 2b, 2c all at same time

### Step 3: Configure Conditions (Optional)

Set conditions for when workflow steps apply:

**Conditional Routing:**
- Based on form field values
- Based on submission amount
- Based on submitter's department
- Based on form data

**Example Conditions:**
```
IF amount > 10000 THEN require CFO approval
IF department = "Production" THEN skip manager approval
IF priority = "High" THEN escalate after 2 hours
```

### Step 4: Activate Workflow

1. Review all steps
2. Test workflow if possible
3. Click **"Activate"**
4. The workflow becomes active for new submissions
5. Only one workflow per form can be active

### Testing Workflows

Before activating:

1. Click **"Test Workflow"**
2. Enter sample data
3. Review calculated approval path
4. Verify approvers are correct
5. Check SLA times are reasonable

---

## For Approvers: Processing Approvals

### Viewing Pending Approvals

#### Dashboard View

1. Log in to SIGaP
2. Dashboard shows pending approval count
3. Click **"Pending Approvals"** badge

#### Detailed View

1. Navigate to **Form Submissions > Pending Approvals**
2. See all submissions awaiting your approval
3. Organized by:
   - Priority (if set)
   - Due date (SLA)
   - Form type
   - Submission date

#### Filtering

Filter pending approvals by:
- **Form Type**: Specific forms
- **Priority**: High, Medium, Low
- **Overdue**: Past SLA deadline
- **Department**: Submitter's department
- **Date Range**: Submission date

### Reviewing a Submission

1. Click on a submission to open details
2. Review all information:
   - Form data and responses
   - Attachments and files
   - Photos and signatures
   - Calculated values
   - Previous approval comments

3. Check for completeness:
   - All required fields filled
   - Attachments are valid
   - Data makes sense
   - Calculations are correct

### Approving a Submission

1. After reviewing, click **"Approve"** button
2. Add comments (optional but recommended):
   - Confirmation notes
   - Additional requirements
   - Follow-up actions needed
3. Click **"Confirm Approval"**
4. Submission moves to:
   - Next approval step, OR
   - Approved status (if last step)

**Approval Confirmation:**
- You'll receive confirmation message
- Email notification sent (if configured)
- Submission updated immediately
- Next approver notified (if applicable)

### Rejecting a Submission

1. Click **"Reject"** button
2. **Provide reason** (required):
   - What's wrong with the submission
   - What needs to be corrected
   - Clear instructions for resubmission
3. Click **"Confirm Rejection"**

**After Rejection:**
- Submitter receives notification
- Submission status changes to "Rejected"
- Submitter can view rejection reason
- Submitter may resubmit (depending on configuration)

### Requesting Changes

If submission needs minor corrections:

1. Click **"Request Changes"** or add rejection comment
2. Specify exactly what needs changing:
   - Which fields need correction
   - What information is missing
   - What documents need replacement
3. Be specific and clear
4. Submitter receives detailed feedback

### Delegating Approvals

If you're unable to approve:

1. Contact system administrator
2. Request temporary delegation
3. Administrator can:
   - Assign to another approver
   - Add alternate approver
   - Extend SLA deadline

### Viewing Approval History

For any submission:

1. Open submission details
2. Click **"Approval History"** tab
3. View complete audit trail:
   - Each approval step
   - Who approved/rejected
   - When action was taken
   - Comments provided
   - Time spent at each step
   - SLA compliance

---

## Workflow Types

### Sequential Workflow

Steps execute one after another in order.

**Example - Standard Purchase Approval:**
1. **Step 1**: Supervisor Review
2. **Step 2**: Department Manager Approval
3. **Step 3**: Finance Approval
4. **Step 4**: General Manager Final Approval

**Flow:**
```
Submit → Supervisor → Manager → Finance → GM → Approved
```

**Best for:**
- Clear hierarchical approvals
- When each step builds on previous
- When order matters
- Simple approval chains

### Parallel Workflow

Multiple approvers review simultaneously.

**Example - Multi-department Review:**
- **Step 1**: All must approve simultaneously
  - Quality Assurance
  - Production Manager
  - Safety Officer

**Flow:**
```
Submit → [QA + Production + Safety] → Approved
```

**Best for:**
- Independent reviews
- Cross-functional approvals
- Faster approval process
- When order doesn't matter

### Hybrid Workflow

Combination of sequential and parallel steps.

**Example - Complex Project Approval:**
1. **Step 1**: Project Manager Review
2. **Step 2**: Parallel review by:
   - Technical Lead
   - Budget Officer
3. **Step 3**: Director Final Approval

**Flow:**
```
Submit → PM → [Technical + Budget] → Director → Approved
```

**Best for:**
- Complex approval requirements
- Multiple stakeholder input
- Balanced speed and thoroughness

### Conditional Workflow

Different paths based on submission data.

**Example - Amount-based Approval:**
```
IF amount < 5000:
  Manager → Approved
  
IF amount >= 5000 AND < 20000:
  Manager → Director → Approved
  
IF amount >= 20000:
  Manager → Director → CFO → CEO → Approved
```

**Best for:**
- Variable approval requirements
- Risk-based approvals
- Efficient resource use
- Compliance requirements

---

## SLA and Escalation

### Service Level Agreements

**Setting SLA:**
- Define maximum time for each step
- Measured in hours
- Starts when step becomes active
- Tracks business hours (optional)

**Example SLAs:**
- Supervisor: 24 hours
- Manager: 48 hours
- Director: 72 hours

### Escalation Process

**When Escalates:**
- SLA deadline passes
- Escalation is enabled on step
- Approver hasn't acted

**Escalation Actions:**
- Email notification to escalation contact
- Notification to approver's manager
- Dashboard alert
- Optional auto-approve (if configured)

**Best Practices:**
- Set realistic SLA times
- Allow buffer for holidays/weekends
- Configure appropriate escalation contacts
- Monitor escalation frequency

---

## Notifications

### Email Notifications

Sent automatically for:
- **Approvers**: When approval needed
- **Submitters**: When approved/rejected
- **Escalation**: When SLA exceeded
- **Completion**: When workflow complete

### Dashboard Notifications

Real-time badges showing:
- Pending approval count
- Overdue approvals
- Recent decisions
- Escalated items

### Configuring Notifications

Administrators can configure:
- Email template content
- Notification timing
- Recipient list
- Notification triggers

---

## Best Practices

### For Administrators

**Workflow Design:**
- Keep workflows as simple as possible
- Use parallel steps when order doesn't matter
- Set realistic SLA times
- Test thoroughly before activation
- Document workflow purpose

**Approver Selection:**
- Choose appropriate approvers for each step
- Consider backup approvers
- Ensure approvers have required knowledge
- Balance workload across approvers

**SLA Configuration:**
- Account for approver availability
- Consider time zones
- Allow for complex reviews
- Set up escalation appropriately

### For Approvers

**Timely Processing:**
- Check pending approvals regularly
- Respond within SLA timeframe
- Don't wait until deadline
- Delegate if unavailable

**Thorough Review:**
- Review all submission details
- Check attachments and files
- Verify calculations
- Ensure compliance with policies

**Clear Communication:**
- Provide specific feedback
- Explain rejection reasons clearly
- Document approval conditions
- Communicate with submitters when needed

**Consistent Standards:**
- Apply same criteria to all submissions
- Follow established guidelines
- Be fair and objective
- Document exceptions

### For Submitters

**Before Submitting:**
- Ensure all required fields complete
- Double-check data accuracy
- Upload all required documents
- Review submission before sending

**After Submitting:**
- Monitor approval progress
- Respond quickly to feedback
- Address rejection reasons promptly
- Keep documentation ready

---

## Troubleshooting

### Approval Not Appearing

**Problem**: Submission awaiting your approval doesn't show

**Solutions:**
1. Verify you're assigned as approver
2. Check you're logged in with correct account
3. Refresh the pending approvals page
4. Check if someone else already approved
5. Review workflow configuration
6. Contact system administrator

### Can't Approve/Reject

**Problem**: Buttons are disabled or missing

**Solutions:**
1. Verify it's your turn to approve
2. Check if previous steps are complete
3. Ensure you have approval permission
4. Verify workflow is active
5. Check if submission is already processed
6. Contact administrator

### Wrong Approver Assigned

**Problem**: Approval routed to wrong person

**Solutions:**
1. Contact administrator immediately
2. Administrator can reassign
3. Check workflow configuration
4. Verify role/user assignments
5. Update workflow for future submissions

### SLA Already Expired

**Problem**: Approval shows as overdue

**Solutions:**
1. Process as soon as possible
2. Add comment explaining delay
3. Notify next approver of urgency
4. Contact administrator if workflow stuck
5. Review SLA settings for future

---

## Audit and Compliance

### Audit Trail

Every approval action is logged:
- Who performed the action
- When it was performed
- What action was taken
- Comments provided
- IP address and location
- Time spent at each step

### Compliance Features

- **Immutable logs**: Cannot be edited or deleted
- **Complete history**: All actions recorded
- **Timestamp accuracy**: Server-based timestamps
- **User authentication**: Verified user identity
- **Comment preservation**: All feedback retained

### Reports

Available reports:
- Approval time analysis
- Bottleneck identification
- Approver performance
- SLA compliance
- Rejection rate analysis

---

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[Forms Guide](./FORMS_GUIDE.md)** - Creating forms with approval workflows
- **[Admin Guide](./ADMIN_GUIDE.md)** - User and permission management

---

**Last Updated**: October 17, 2025  
**Version**: 1.0

