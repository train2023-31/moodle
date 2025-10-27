# File Structure Documentation

## Root Directory Files

### version.php
**Purpose**: Plugin metadata and version information
- Defines plugin component name (`local_requestservices`)
- Sets plugin version (2025071301)
- Specifies minimum Moodle version requirement (2022041900)
- Declares maturity level and release version

### index.php
**Purpose**: Main entry point and tab controller
- Handles course context and user authentication
- Manages tab navigation system
- Includes capability checking (`local/requestservices:view`)
- Dynamically loads tab content from `/tabs/` directory
- Supports 6 main tabs: allrequests, computerservicesTab, financialservices, registeringroom, requestparticipant, residencebooking

**Key Functions**:
- Course ID validation and context setup
- Tab parameter handling
- Dynamic tab file inclusion
- Error handling for missing tab files

### lib.php
**Purpose**: Core plugin functions and Moodle integration
- Contains `local_requestservices_extend_navigation_course()` function
- Adds the plugin to course navigation menu
- Implements capability checking for navigation display

## Directory Structure

### /db/
Contains database-related configurations

#### access.php
**Purpose**: Capability definitions
- Defines `local/requestservices:view` capability
- Sets permission context (CONTEXT_COURSE)
- Grants access to teachers and editing teachers by default

### /tabs/
Contains individual tab implementation files

#### allrequests.php (2.2KB, 57 lines)
**Purpose**: Overview tab showing all request types
- Implements vertical subtab navigation
- Uses Bootstrap grid system for layout
- Provides unified view across all service categories

#### computerservicesTab.php (3.1KB, 79 lines) ✅ **MODERNIZED**
**Purpose**: Computer services request interface
- Integrates with `local_computerservice` plugin
- Includes request form and workflow manager
- Handles form submission and processing
- **Status**: Fully modernized with error handling, CSRF protection, and improved code structure

#### financialservices.php (2.8KB, 77 lines)
**Purpose**: Financial services request interface
- Manages budget and funding requests
- Includes form handling and validation

#### registeringroom.php (2.6KB, 53 lines)
**Purpose**: Room registration and booking interface
- Handles facility booking requests
- Manages room availability and scheduling

#### requestparticipant.php (4.0KB, 86 lines) ✅ **MODERNIZED**
**Purpose**: Lecturer and participant request interface
- Manages requests for teaching staff and role players
- Handles participant assignment and scheduling
- **Status**: Fully modernized with working dropdowns, conditional field behavior, and comprehensive error handling

#### residencebooking.php (2.1KB, 53 lines) ✅ **MODERNIZED**
**Purpose**: Accommodation booking interface
- Manages residence and dormitory booking requests
- Handles availability and booking confirmation
- **Status**: Fully modernized with error handling, security improvements, automatic service number population, and integration with existing residencebooking plugin JavaScript

### /tabs/subtabs/
Contains subtab implementation files for detailed views

#### computerservicesview.php (1.8KB, 50 lines) ✅ **MODERNIZED**
**Purpose**: Detailed view for computer service requests
- **Status**: Modernized with improved error handling and code structure

#### financialservicesview.php (1.3KB, 40 lines)
**Purpose**: Detailed view for financial service requests

#### participantview.php (4.3KB, 99 lines) ✅ **INTERNATIONALIZED**
**Purpose**: Detailed view for participant requests
- **Status**: Template internationalization completed, uses language strings for all text

#### registeringroomview.php (1.5KB, 49 lines)
**Purpose**: Detailed view for room registration requests

#### residencebookingview.php (1.6KB, 42 lines)
**Purpose**: Detailed view for residence booking requests

### /templates/
Contains Mustache templates for UI rendering

#### computerservices_requests.mustache (1.6KB, 63 lines)
**Purpose**: Template for computer services request display
- Shows device requests and quantities
- Displays request status and dates

#### financialservicesview.mustache (1.6KB, 64 lines)
**Purpose**: Template for financial services display
- Shows funding types and requested amounts
- Displays budget approval status

#### participantview.mustache (1.2KB, 40 lines) ✅ **INTERNATIONALIZED**
**Purpose**: Template for participant request display
- Shows lecturer and role player requests
- Displays availability and assignment status
- **Status**: Fully internationalized with language string support (English/Arabic)
- **Features**: Uses {{#str}} calls for all text, supports RTL languages

#### registeringroomview.mustache (15KB, 378 lines)
**Purpose**: Comprehensive room registration template
- Complex form with multiple room options
- Includes scheduling and availability display

#### residencebookingview.mustache (15KB, 381 lines)
**Purpose**: Comprehensive residence booking template
- Detailed accommodation booking form
- Includes room types and amenity selection

### /classes/output/
Contains PHP renderer classes for template data preparation

#### computerservices_requests.php (1.2KB, 41 lines) ✅ **MODERNIZED**
**Purpose**: Data renderer for computer services template
- **Status**: Modernized with improved error handling

#### financialservicesview.php (1.2KB, 40 lines)
**Purpose**: Data renderer for financial services template

#### participantview.php (1.1KB, 37 lines)
**Purpose**: Data renderer for participant template

#### registeringroomview.php (6.0KB, 143 lines)
**Purpose**: Data renderer for room registration template

#### residencebookingview.php (4.0KB, 98 lines)
**Purpose**: Data renderer for residence booking template

### /js/
Contains JavaScript files for enhanced functionality

#### guest_autocomplete.js (4.2KB, 120 lines) ✅ **NEW**
**Purpose**: Guest autocomplete with automatic service number population
- Provides automatic PF number extraction and population
- Handles multiple event types (change, select2:select, input, blur)
- Includes AJAX fallback for PF number retrieval
- Auto-initializes on residencebooking tab
- **Features**: Comprehensive event handling, console logging, error handling

### /lang/
Contains language string definitions

#### /lang/en/local_requestservices.php (1.1KB, 57 lines) ✅ **UPDATED**
**Purpose**: English language strings
- Plugin name and tab labels
- Form field labels and messages
- Status and error messages
- **Recent Updates**: Added missing strings for participantview template internationalization and residencebooking tab modernization

#### /lang/ar/local_requestservices.php (1.1KB, 57 lines) ✅ **UPDATED**
**Purpose**: Arabic language strings
- Arabic translations for all English strings
- RTL (Right-to-Left) language support
- **Recent Updates**: Added missing Arabic translations for participantview template and residencebooking tab modernization

## Current Development Status

### ✅ **Completed Modernizations:**
1. **computerservicesTab** - Fully modernized with error handling, CSRF protection, and improved code structure
2. **participantview template** - Fully internationalized with language string support
3. **requestparticipant tab** - Fully functional with working dropdowns and conditional field behavior ✅ **RESOLVED**
4. **residencebooking tab** - Fully modernized with error handling, security improvements, automatic service number population, and integration with existing residencebooking plugin JavaScript ✅ **RESOLVED**

### ⚠️ **Partially Completed:**
- No tabs currently partially completed

### 🔄 **Pending Modernization:**
1. **financialservices tab** - Needs modernization
2. **registeringroom tab** - Needs modernization  
3. **Remaining templates** - Need internationalization

### 📊 **Progress Summary:**
- **Bug Fixes**: 3 of 3 resolved (100%) ✅
- **Tab Modernization**: 3 of 6 completed (50%)
- **Template Internationalization**: 1 of 6 completed (17%)

## Dependencies

### External Plugin Dependencies
- **local_computerservice**: Required for computer services functionality
  - Provides request form (`request_form.php`)
  - Provides workflow manager (`simple_workflow_manager.php`)
- **local_participant**: Required for participant functionality
  - Provides request form (`request_form.php`)
  - Provides JavaScript for conditional field behavior (`js/main.js`)
- **local_residencebooking**: Required for residence booking functionality
  - Provides request form (`residencebooking_form.php`)
  - Provides workflow manager (`simple_workflow_manager.php`)
  - Provides JavaScript for autocomplete and service number population (`amd/build/guest_autocomplete.min.js`)

### Moodle Core Dependencies
- **Navigation API**: For course navigation integration
- **Capability System**: For permission management
- **Template System**: For Mustache template rendering
- **Tab API**: For tab navigation functionality
- **Bootstrap Framework**: For responsive layout

## File Naming Conventions

- **Tab files**: Named after their function (e.g., `computerservicesTab.php`)
- **Template files**: Use `.mustache` extension with descriptive names
- **Language files**: Follow Moodle standard (`local_requestservices.php`)
- **Class files**: Follow PSR-4 autoloading standards
- **Renderer files**: Located in `/classes/output/` directory

## Recent Changes and Updates

### September 3, 2025
- ✅ Fixed participantview.mustache template internationalization
- ✅ Added missing language strings for both English and Arabic
- ✅ Replaced hardcoded Arabic text with proper language string calls
- ✅ Updated language files with new strings for template support
- ✅ **RESOLVED Bug #2**: Fixed requestparticipant dropdown data loading issue
- ✅ Dropdowns now working correctly with employee and lecturer data
- ✅ JavaScript AJAX URLs fixed to use correct absolute paths
- ✅ All debugging code removed for production use
- ✅ **RESOLVED Bug #3**: Fully modernized residencebooking tab
- ✅ Added comprehensive error handling, security improvements, and automatic service number population
- ✅ Integrated with existing residencebooking plugin JavaScript for autocomplete functionality
- ✅ Added proper PHPDoc documentation and improved code structure
- ✅ Used `$PAGE->requires->js_call_amd()` for proper AMD module loading
- ✅ **CREATED**: Separate JavaScript file for guest autocomplete functionality
- ✅ **ENHANCED**: Moved inline JavaScript to dedicated `/js/guest_autocomplete.js` file
- ✅ **IMPROVED**: Better code organization and maintainability 