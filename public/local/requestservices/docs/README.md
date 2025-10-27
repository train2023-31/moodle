# Request Services Plugin Documentation

## Overview

The **Request Services Plugin** (`local_requestservices`) is a Moodle local plugin that provides a comprehensive tab-based interface for managing various types of service requests within courses. It integrates seamlessly with Moodle's course navigation and provides a centralized location for users to submit and view different types of service requests.

## Plugin Information

- **Component Name**: `local_requestservices`
- **Plugin Type**: Local Plugin
- **Version**: 1.0 (Build: 2025071301)
- **Maturity**: Stable
- **Minimum Moodle Version**: 4.0 (2022041900)
- **Language Support**: English (en) and Arabic (ar)

## Features

### Service Categories

The plugin provides six main service request categories:

1. **All Requests** - Overview of all service requests
2. **Computer Services** - Hardware and software requests ✅ **MODERNIZED**
3. **Financial Services** - Budget and funding requests  
4. **Room Registration** - Facility booking and registration
5. **Request Participant** - Lecturer and role player requests ✅ **MODERNIZED**
6. **Residence Booking** - Accommodation booking requests ✅ **MODERNIZED**

### Key Capabilities

- **Tab-based Interface**: Clean, organized navigation between different service types
- **Permission Control**: Role-based access using Moodle capabilities
- **Multi-language Support**: Built-in support for English and Arabic ✅ **ENHANCED**
- **Template System**: Uses Mustache templates for consistent UI rendering ✅ **INTERNATIONALIZED**
- **Integration**: Seamlessly integrates with Moodle course navigation
- **Responsive Design**: Uses Bootstrap grid system for mobile compatibility

## Current Development Status

### ✅ **Completed Modernizations:**
- **Computer Services Tab**: Fully modernized with error handling, CSRF protection, and improved code structure
- **Participant View Template**: Fully internationalized with language string support (English/Arabic)
- **Request Participant Tab**: Fully functional with working dropdowns and conditional field behavior ✅ **RESOLVED**
- **Residence Booking Tab**: Fully modernized with error handling, security improvements, and automatic service number population ✅ **RESOLVED**

### ⚠️ **In Progress (0%):**
- No tabs currently in progress

### 🔄 **Pending Modernization (50%):**
- Financial Services Tab modernization
- Room Registration Tab modernization
- Remaining template internationalization

### 📊 **Progress Summary:**
- **Bug Fixes**: 3 of 3 resolved (100%) ✅
- **Tab Modernization**: 3 of 6 completed (50%)
- **Template Internationalization**: 1 of 6 completed (17%)

## Architecture

### Navigation Flow
```
Course → Request Services Tab → Service Category Tabs → Subtab Views
```

### Permission System
- **Capability**: `local/requestservices:view`
- **Context Level**: Course level
- **Default Access**: Teachers and Editing Teachers

## Recent Updates

### September 3, 2025
- ✅ Fixed participantview.mustache template internationalization
- ✅ Added missing language strings for both English and Arabic
- ✅ Replaced hardcoded Arabic text with proper language string calls
- ✅ Improved template maintainability and localization support
- ✅ **RESOLVED Bug #2**: Fixed requestparticipant dropdown data loading issue
- ✅ Dropdowns now working correctly with employee and lecturer data
- ✅ **RESOLVED Bug #3**: Fully modernized residencebooking tab with automatic service number population
- ✅ Added comprehensive error handling, security improvements, and user experience enhancements
- ✅ Integrated with existing residencebooking plugin JavaScript for autocomplete functionality

## File Structure Overview

See [File Structure Documentation](file-structure.md) for detailed information about each file and directory.

## Development Guidelines

See [Development Guide](development-guide.md) for coding standards, best practices, and contribution guidelines.

## Installation and Configuration

See [Installation Guide](installation.md) for setup instructions and configuration options.

## API Documentation

See [API Documentation](api-reference.md) for detailed information about classes, functions, and hooks.

## Troubleshooting

See [Troubleshooting Guide](troubleshooting.md) for common issues and solutions.

## Bug Tracking

See [Bug Tracking](FUTURE_BUGS_FIX.md) for current issues and development priorities. 