# Finance Services – Moodle Local Plugin

A full-featured **financial request workflow** plugin for Moodle. This plugin enables staff to request financial services (e.g., course funding, allowances), and routes these requests through a customizable multi-step approval process using the shared `local_status` engine.

---

## 🌟 Features

### 🧾 Request Management

- Submit finance service requests (course-related)
- Specify funding type, requested amount, reason, and deadline
- Choose a financial clause (optional)

### 🛠 Workflow Engine

- Powered by `local_status` plugin (must be installed)
- Multi-stage approval flow based on capabilities
- Records status history and approver notes
- **Prevents double approval** with immediate button disabling
- **Session-based security** with CSRF protection
- **Race condition protection** with server-side validation

### 🧑‍💼 Role-Based Access

- Staff: Submit requests
- Managers: Approve/reject requests
- Admins: Configure funding types and clauses

### 🌐 Bilingual UI

- Fully localized in English and Arabic
- Auto-switch status and clause labels based on user language

### 📋 Filtering & Reports

- Filter requests by course, funding type, status, or clause
- Export lists to CSV

### 🧩 Modular Management Pages

- Manage Funding Types (add/edit/delete)
- Manage Clauses (terms or conditions)
- Each has tabbed navigation and Bootstrap-styled cards

### 🔄 Advanced Workflow Features

- **Color-coded status display**: Green (approved), Red (rejected), Yellow (pending)
- **Rejection note visibility**: Shows rejection reasons when requests move backwards
- **Approval note display**: Shows approval notes when requests are approved
- **Status-based action messages**: Clear messages instead of buttons for final states
- **Workflow transparency**: Users can see why requests were previously rejected

---

## 📁 File Structure

```
local/financeservices/
├── index.php                          # Main entrypoint with tab layout
├── settings.php                       # (optional) Admin settings
├── version.php                        # Moodle plugin version
├── actions/
│   └── update_request_status.php      # AJAX status update for approvals (enhanced)
├── classes/
│   ├── simple_workflow_manager.php    # Workflow controller (enhanced with rejection tracking)
│   ├── form/
│   │   ├── add_form.php               # Request submission form
│   │   ├── clause_form.php            # Add/edit clause
│   │   ├── filter_form.php            # Filtering requests on list tab
│   │   └── funding_type_form.php      # Add/edit funding type
│   └── output/
│       └── tab_list.php               # Renderer for list.mustache (enhanced)
├── db/
│   ├── access.php                     # Capability definitions
│   ├── install.php                    # Sample data installer
│   ├── install.xml                    # DB table schema (uses XMLDB)
│   └── upgrade.php                    # DB migration logic
├── lang/
│   ├── ar/
│   │   └── local_financeservices.php  # Arabic translation (enhanced)
│   └── en/
│       └── local_financeservices.php  # English strings (enhanced)
├── pages/
│   ├── add_clause.php
│   ├── add_fundingtype.php
│   ├── delete_clause.php
│   ├── delete_fundingtype.php
│   ├── edit_clause.php
│   ├── edit_fundingtype.php
│   ├── manage.php                     # Dashboard (cards)
│   ├── manage_clauses.php            # CRUD interface for clauses
│   └── manage_fundingtypes.php       # CRUD interface for funding types
└── templates/
    └── list.mustache                  # HTML list of all finance requests (enhanced)
```

---

## 🚀 Installation

1. Place the folder in `local/financeservices`
2. Make sure `local_status` is installed first (dependency)
3. Visit the Moodle admin page to trigger plugin installation
4. Plugin creates 3 tables:
   - `local_financeservices`
   - `local_financeservices_funding_type`
   - `local_financeservices_clause`
5. Initial dummy data is inserted by `install.php`

---

## 🆙 Upgrading

Schema updates are defined in `upgrade.php`. On upgrade:

- `install.xml` defines required fields
- `upgrade.php` checks and applies missing fields
- Use version numbering to control stepwise upgrades

---

## 🛡 Capabilities

```php
local/financeservices:view      # Required to access tabs
local/financeservices:manage    # Required to add/edit/delete types/clauses
```

These are set in `access.php` and granted by role:

- Managers: All
- Teachers: View only

---

## 🌐 Language Support

- Arabic and English fully supported via `lang/ar/` and `lang/en/`
- Strings include labels for buttons, tables, statuses, and tabs
- Status text auto-switches between `display_name_ar` and `status_string_en`
- **Enhanced workflow strings** for rejection reasons, approval notes, and status messages

---

## 🔄 Workflow System

### Workflow Steps

The plugin implements a 5-step approval workflow:

1. **Initial** (Status ID: 8) - Request submitted
2. **Leader 1 Review** (Status ID: 9) - First-level approval
3. **Leader 2 Review** (Status ID: 10) - Second-level approval
4. **Leader 3 Review** (Status ID: 11) - Third-level approval
5. **Boss Review** (Status ID: 12) - Final approval
6. **Approved** (Status ID: 13) - Final approved state
7. **Rejected** (Status ID: 14) - Final rejected state

### Rejection Flow

When a request is rejected, it can move backwards in the workflow:

- **Leader 1 → Rejected**: Final rejection
- **Leader 2 → Leader 1**: Back to first level
- **Leader 3 → Leader 2**: Back to second level
- **Boss → Leader 3**: Back to third level

### Security Features

- **Session key validation** for all AJAX requests
- **Immediate button disabling** to prevent double submissions
- **Server-side race condition protection**
- **Capability-based access control** for each workflow step

### Status Display

- **Color-coded status**: Visual indication of request state
- **Rejection note visibility**: Shows why requests were previously rejected
- **Approval note display**: Shows approval comments when available
- **Status-based actions**: Appropriate buttons/messages based on current state

---

## 🛠 Technical Implementation

### AJAX Workflow Processing

The plugin uses a secure AJAX system for workflow actions:

```javascript
// Example approval request
fetch(actionUrlBase, {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    action: "approve",
    id: requestId,
    sesskey: sesskey,
  }),
  credentials: "same-origin",
});
```

### Rejection Note Tracking

The system tracks rejection notes and displays them appropriately:

- **When rejected and moved backwards**: Shows "Previously Rejected - Rejection Reason"
- **When in final rejected state**: Shows "Rejection Reason"
- **When approved**: Rejection notes are automatically hidden

### Error Handling

- **Client-side**: Immediate button disabling and loading states
- **Server-side**: Comprehensive error handling with user-friendly messages
- **Network errors**: Graceful fallback with user notification

---

## 👨‍💻 For Developers

- All data access is via Moodle `$DB` object
- Status flow is handled through `simple_workflow_manager.php`
- Templates use Mustache and `renderer_base`
- Formslib used for all forms
- **Enhanced workflow manager** with rejection tracking methods
- **Secure AJAX implementation** following Moodle best practices

### Key Classes

- **`simple_workflow_manager`**: Handles workflow logic and status transitions
- **`tab_list`**: Renders the request list with status information
- **`update_request_status.php`**: AJAX endpoint for workflow actions

### Workflow Methods

```php
// Check if user can approve at current status
simple_workflow_manager::can_user_approve($status_id)

// Check if request has been rejected and moved backwards
simple_workflow_manager::has_rejection_note($status_id)

// Approve a request
simple_workflow_manager::approve_request($id, $note)

// Reject a request
simple_workflow_manager::reject_request($id, $note)
```

---

## 🔧 Recent Enhancements

### Version 1.1.0 - Workflow Improvements

- **Fixed double approval issue** with immediate button disabling
- **Added session key validation** for enhanced security
- **Implemented rejection note visibility** when requests move backwards
- **Added color-coded status display** for better UX
- **Enhanced error handling** with comprehensive validation
- **Improved status messages** instead of buttons for final states
- **Added workflow transparency** with rejection reason display

### Security Improvements

- CSRF protection with session keys
- Server-side race condition detection
- Input validation and sanitization
- Proper error handling and logging

### User Experience Enhancements

- Visual status indication with colors
- Clear rejection reason display
- Appropriate action messages based on status
- Loading states and error feedback
- Responsive design with Bootstrap styling
