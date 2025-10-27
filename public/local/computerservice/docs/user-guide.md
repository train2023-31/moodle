# User Guide - Computer Service Plugin

This guide explains how to use the Computer Service plugin to request IT devices and manage requests.

## 🎯 Overview

The Computer Service plugin allows you to:
- **Request IT devices** (projectors, laptops, etc.) for your courses
- **Track request status** through the approval workflow
- **Manage requests** (if you have admin permissions)
- **Manage device types** (admin only)

## 👥 User Roles and Permissions

### Students and Teachers
- **Submit device requests** for courses they're enrolled in
- **View their own requests** and status updates
- **Add comments** to explain their needs

### Managers and Administrators
- **Review and approve/reject requests** at different workflow levels
- **Filter and search** through all requests
- **Export request data** to CSV
- **Manage device types** (add/edit/activate/deactivate)

## 🖥️ Accessing the Plugin

1. **Log into Moodle** with your account
2. **Navigate to the plugin**:
   - Go to Site Administration (if you're an admin)
   - Or use the direct URL: `/local/computerservice/`

The plugin interface has **three main tabs**:
- 🔵 **Request Devices** - Submit new requests
- 🟡 **Manage Requests** - Review and approve requests (admin only)
- 🟢 **Manage Devices** - Add and manage device types (admin only)

---

## 📝 Tab 1: Request Devices

This tab allows you to submit new device requests.

### Step-by-Step Request Process

#### 1. Fill Out the Request Form

**Course Selection**
- Choose the course for which you need the device
- You can only select courses you're enrolled in
- This helps track which department/course needs the equipment

**Device Type**
- Select from available device types (projectors, laptops, etc.)
- Device names appear in your selected language (English/Arabic)
- Only active devices are shown in the list

**Number of Devices**
- Enter how many devices you need (minimum: 1)
- Consider the actual number required for your class size

**Required Date**
- Select when you need the devices
- Must be a future date
- Requests needed today or tomorrow are automatically marked as "urgent"

**Comments (Optional)**
- Explain why you need the devices
- Provide any special requirements
- Include contact information if needed

#### 2. Submit the Request

- Click **"Submit Request"** to send your request
- You'll see a confirmation message if successful
- The request enters the approval workflow immediately

#### 3. urgent Requests

If your required date is today or tomorrow:
- Request is automatically flagged as "urgent"
- May receive expedited processing
- Include detailed justification in comments

### Example Request Scenarios

**Scenario 1: Regular Classroom Projection**
- Course: "Introduction to Biology"
- Device: "Projector"
- Quantity: 1
- Date: Next week Tuesday
- Comment: "Weekly lecture presentation for 30 students"

**Scenario 2: urgent Laptop Request**
- Course: "Computer Science Lab"
- Device: "Laptop"
- Quantity: 15
- Date: Tomorrow
- Comment: "Lab computers failed, urgent replacement needed for exam"

---

## 🛠️ Tab 2: Manage Requests (Admin Only)

This tab is only available to users with management permissions.

### Request Management Interface

#### Overview Table
The main table shows all requests with:
- **User Name**: Who submitted the request
- **Course**: Which course the request is for
- **Device**: Type and quantity requested
- **Status**: Current workflow status with color coding
- **urgent**: Red indicator for urgent requests
- **Actions**: Approve/Reject buttons (if you have permission)

#### Status Color Coding
- 🟡 **Yellow (Initial)**: Newly submitted, awaiting first review
- 🔵 **Blue (In Review)**: Being reviewed by leaders/managers
- 🟢 **Green (Approved)**: Request has been approved
- 🔴 **Red (Rejected)**: Request has been rejected

### Filtering and Search

#### Using the Filter Form
1. **Course Filter**: Select specific courses to view
2. **User Filter**: Search for requests by specific users
3. **Status Filter**: Filter by approval status
4. **Urgency Filter**: Filter by urgency level (All requests, Urgent, Not Urgent)
5. **Apply Filters**: Click to update the table
6. **Reset**: Clear all filters to show all requests

#### Filter Tips
- Use multiple filters together for precise results
- Course filter only shows courses you have access to
- User filter supports partial name matching
- Urgency filter helps prioritize urgent requests that need immediate attention
- "All requests" is the default filter option

### Approving Requests

#### Approval Process
1. **Review Request Details**: Check course, device type, quantity, and user comments
2. **Verify Authority**: Ensure you have permission for the current workflow step
3. **Add Approval Note** (Optional): Add context for the approval
4. **Click "Approve"**: Request moves to next workflow stage

#### Approval Notes
- Optional but recommended for transparency
- Examples: "Approved for educational purposes", "Devices available in inventory"
- Visible to other reviewers and requester

### Rejecting Requests

#### Rejection Process
1. **Review Request**: Understand why it should be rejected
2. **Write Rejection Note**: **MANDATORY** - explain why request is rejected
3. **Click "Reject"**: Request returns to initial status for resubmission

#### Rejection Note Requirements
- **Must be provided** - rejection will fail without a note
- Should be clear and constructive
- Examples: "Insufficient budget", "Device not available for requested date", "Alternative solution available"

#### Good Rejection Note Examples
- ✅ "Budget exhausted for this quarter. Please resubmit next quarter."
- ✅ "Requested devices unavailable. Consider using Room 205 which has built-in projection."
- ✅ "Request submitted too late. Please provide at least 48 hours notice."

#### Poor Rejection Note Examples
- ❌ "No" (too brief, not helpful)
- ❌ "Cannot approve" (doesn't explain why)
- ❌ "Rejected" (provides no useful information)

### Workflow Understanding

#### Typical Approval Flow
1. **Initial (15)** → First submission
2. **Leader 1 Review (16)** → Department head review
3. **Leader 2 Review (17)** → Faculty review
4. **Leader 3 Review (18)** → Administrative review
5. **Boss Review (19)** → Final management approval
6. **Approved (20)** → Request approved for fulfillment

#### Your Role in the Workflow
- You can only approve/reject at stages where you have permission
- Different users may have access to different workflow stages
- If you can't see action buttons, you don't have permission for that stage

### Exporting Data

#### CSV Export Feature
1. **Apply desired filters** to narrow down data
2. **Click "Export CSV"** button
3. **Download file** contains filtered request data
4. **Use in spreadsheets** for further analysis or reporting

#### CSV Data Includes
- User information (name, email)
- Course details
- Device type and quantity
- Request dates and status
- Comments and notes

---

## ⚙️ Tab 3: Manage Devices (Admin Only)

This tab allows system administrators to manage available device types.

### Device Management Overview

#### Device List View
- Shows all device types with English and Arabic names
- Displays current status (Active/Inactive)
- Provides toggle and edit options

### Adding New Devices

#### Step-by-Step Process
1. **Click "Add New Device"**
2. **Fill Device Form**:
   - **English Name**: Device name in English (e.g., "Projector")
   - **Arabic Name**: Device name in Arabic (e.g., "جهاز عرض")
   - **Status**: Choose Active or Inactive
3. **Submit Form**
4. **Device appears** in the device list

#### Naming Guidelines
- Use clear, descriptive names
- Be consistent with naming conventions
- English names should be simple and professional
- Arabic names should be accurate translations

#### Examples of Good Device Names
| English | Arabic | Purpose |
|---------|--------|---------|
| Projector | جهاز عرض | Classroom presentations |
| Laptop | حاسوب محمول | Student lab work |
| Microphone | ميكروفون | Lectures and events |
| Whiteboard | سبورة ذكية | Interactive teaching |

### Managing Existing Devices

#### Activating/Deactivating Devices
- **Active devices**: Appear in request forms, can be requested
- **Inactive devices**: Hidden from request forms, cannot be requested
- **Toggle status**: Click the status button to change between active/inactive

#### When to Deactivate Devices
- Device type is no longer available
- Temporary shortage or maintenance
- Discontinued or replaced equipment
- Budget restrictions

#### When to Activate Devices
- New devices received and ready for use
- Maintenance completed
- Budget restored
- Popular demand for device type

---

## 🔔 Notifications and Status Updates

### Understanding Request Status

#### Status Progression
Your request moves through these stages:
1. **Submitted** - Request received and entered into system
2. **Under Review** - Being evaluated by appropriate managers
3. **Approved** - Request approved and ready for fulfillment
4. **Rejected** - Request denied and returned to initial status for resubmission

#### Status Notifications
- Status changes are reflected immediately in the interface
- Check back periodically for updates
- Rejection notes explain why requests were denied

### What to Do After Approval

#### Next Steps
1. **Contact IT Department** or relevant office
2. **Arrange pickup/delivery** of devices
3. **Confirm device specifications** match your needs
4. **Plan for device return** if temporary loan

#### Important Notes
- Approval doesn't guarantee immediate availability
- Coordinate with relevant departments for actual device provision
- Follow institutional policies for device use and return

---

## 💡 Tips and Best Practices

### For Requesting Devices

#### Planning Ahead
- Submit requests well in advance (at least 48 hours)
- Consider alternative dates if devices might not be available
- Check if your classroom already has required equipment

#### Writing Effective Requests
- Be specific about your needs in the comments
- Explain the educational purpose
- Include class size and duration if relevant
- Provide contact information for coordination

#### Common Request Mistakes to Avoid
- ❌ Requesting too many devices for actual need
- ❌ Submitting requests too close to needed date
- ❌ Not providing clear justification in comments
- ❌ Selecting wrong course or device type

### For Request Managers

#### Efficient Approval Process
- Review requests promptly to avoid bottlenecks
- Provide clear, helpful rejection notes
- Consider alternative solutions when rejecting
- Communicate with requesters if clarification needed

#### Maintaining Good Workflow
- Check requests regularly throughout the day
- Coordinate with other managers to avoid delays
- Keep track of device availability
- Document common rejection reasons for consistency

### For Device Administrators

#### Keeping Device List Current
- Regularly review device availability
- Update device names if terminology changes
- Remove obsolete devices promptly
- Add new devices as they become available

#### Monitoring Usage Patterns
- Track which devices are most requested
- Consider adding popular device types
- Remove devices that are never requested
- Adjust availability based on demand

---

## ❓ Troubleshooting Common Issues

### Cannot Submit Request

**Problem**: Form won't submit or shows errors
**Solutions**:
- Check that required fields are filled
- Ensure date is in the future
- Verify you're enrolled in the selected course
- Try refreshing the page and resubmitting

### Cannot See Manage Requests Tab

**Problem**: Tab is missing or inaccessible
**Cause**: Insufficient permissions
**Solution**: Contact your system administrator to verify you have `local/computerservice:managerequests` capability

### Approve/Reject Buttons Not Working

**Problem**: AJAX actions fail or show errors
**Solutions**:
- Refresh the page and try again
- Check your internet connection
- Clear browser cache
- Ensure you have permission for the current workflow stage

### Device Not Appearing in List

**Problem**: Expected device type is missing from request form
**Causes**: 
- Device is set to "Inactive" status
- Device was recently added and cache needs refreshing
**Solutions**:
- Contact administrator to check device status
- Refresh page or clear browser cache

### Language Display Issues

**Problem**: Text appears in wrong language
**Solution**: 
- Check your Moodle language settings
- Contact administrator if device names are incorrect

---

*This user guide covers version 1.3.1 of the Computer Service plugin. Features and interface may vary in different versions.* 