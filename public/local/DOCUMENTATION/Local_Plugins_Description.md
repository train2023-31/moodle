# Local Plugins Description for Moodle System

## Overview

This document describes all the local plugins developed for this Moodle installation. Local plugins in Moodle are used for custom functionality that doesn't fit into standard plugin types. They follow Moodle's plugin development standards as outlined in the `/local/readme.txt` file.

## Local Plugin Development Guidelines

According to the documentation in `/local/readme.txt`, local plugins are used when no standard plugin fits and typically include:

- **Event consumers** communicating with external systems
- **Custom web services** and external functions 
- **System-level applications** that extend Moodle
- **New database tables** and capabilities
- **Custom admin settings** and interfaces

Each local plugin follows the standard structure:
- `version.php` - Plugin metadata and version information
- `db/install.xml` - Database schema definition
- `db/access.php` - Capability definitions
- `lang/` - Language strings (English/Arabic support)
- `settings.php` - Admin settings integration

---

## Core System Plugins

### 1. **Status Plugin** (`local_status`)
**Purpose**: Central workflow management engine for all other plugins
**Version**: 2025070803 (v3.0.7)
**Status**: MATURITY_STABLE

**Key Features**:
- **Centralized workflow engine** for managing multi-step approval processes
- **9 workflow types** supporting different business processes
- **Department-specific capabilities** across 8 departments with 4-tier hierarchy
- **Dynamic approver system** with person-based sequential approvals
- **Multi-language support** (English/Arabic)
- **Comprehensive audit trails** for compliance

**Workflow Types Supported**:
1. Course Workflow (ID: 1)
2. Finance Services (ID: 2) 
3. Residence Booking (ID: 3)
4. Computer Service (ID: 4)
5. Reports (ID: 5)
6. Training Services (ID: 6)
7. Annual Plan (ID: 7)
8. Classroom Booking (ID: 8)
9. Participants Management (ID: 9)

**Integration**: Serves as the foundation for approval workflows across all service plugins

---

### 2. **Request Services Plugin** (`local_requestservices`)
**Purpose**: Unified interface hub for accessing all service request systems
**Version**: 2025071301 (v1.0)
**Status**: MATURITY_STABLE

**Key Features**:
- **Tab-based interface** providing centralized access to 6 service categories
- **Course-level integration** within Moodle course navigation
- **Permission-based access** using `local/requestservices:view` capability
- **Bootstrap responsive design** for mobile compatibility
- **Template-based rendering** using Mustache templates

**Service Categories**:
1. **All Requests** - Overview across all service types
2. **Computer Services** - Hardware/software requests
3. **Financial Services** - Budget and funding requests
4. **Room Registration** - Facility booking
5. **Request Participant** - Lecturer and personnel requests
6. **Residence Booking** - Accommodation requests

**Architecture**: Acts as a navigation hub connecting to individual service plugins

---

## Service Management Plugins

### 3. **Annual Plans Plugin** (`local_annualplans`)
**Purpose**: Comprehensive annual training plan management and tracking system
**Version**: 1.9 (Build: 2025081200)
**Status**: MATURITY_STABLE
**Requires**: Moodle 3.10+

**Key Features**:
- **Annual plan lifecycle management** with creation, approval, and tracking
- **Course management system** with full CRUD operations and status tracking
- **Status Plugin workflow integration** with multi-level approvals and audit trails
- **Hierarchical code management** for categorization and automatic shortname generation
- **Beneficiary management** with participant tracking and capacity planning
- **Financial tracking** with budget allocation and finance source management
- **Data import/export** supporting Excel and CSV formats with validation
- **Advanced filtering and search** with date ranges, status, and category filters
- **AJAX-powered interface** with 8 dynamic endpoints for real-time interactions
- **Multi-language support** (Arabic/English) with RTL text support
- **Integration capabilities** with employee and role management systems
- **Audit field standardization** with `timecreated`, `timemodified`, `created_by`, `modified_by` tracking
- **PDF generation** for plans and reports using client-side libraries
- **Excel processing** with XLSX.js for data import/export

**Technical Architecture**:
- **MVC Pattern**: Controller-based architecture with proper separation of concerns
- **Database Integration**: 4+ normalized tables with foreign key relationships
- **Security**: Capability-based access control with CSRF protection and input validation
- **Performance**: Optimized queries, indexing, and caching integration

**Workflow Integration**: Uses Status Plugin workflow system for approval processes (Workflow Type ID: annual_plan_workflow with 4-step approval: leader1 → leader2 → leader3 → boss)

---

### 4. **Finance Services Plugin** (`local_financeservices`)
**Purpose**: Financial request workflow management system
**Version**: 1.10.1 (Build: 2025082400)
**Status**: MATURITY_STABLE

**Key Features**:
- **Financial service request submission** with detailed forms
- **Multi-level approval workflow** (Workflow Type ID: 2)
- **Configurable funding types** and clauses
- **Bilingual interface** (English/Arabic)
- **Complete audit trail** and reporting
- **Integration with Status Plugin** for workflow management
- **Enhanced clause management** with uniqueness validation and whitespace trimming
- **Improved data integrity** with advanced validation systems

**Recent Updates (v1.10.1)**:
- **Bug Fix**: Clause name uniqueness validation with proper whitespace handling
- **Data integrity improvements** for financial clauses
- **Enhanced form validation** for financial service submissions

**Workflow**: Supports complex approval processes for financial requests

---

### 5. **Residence Booking Plugin** (`local_residencebooking`)
**Purpose**: Accommodation booking service for educational institutions
**Version**: 2025082500 (v0.6-audit-fields)
**Status**: MATURITY_ALPHA

**Key Features**:
- **Accommodation request submission** with guest details and dates
- **Multi-stage approval workflow** (Initial → Leader1 → Leader2 → Leader3 → Boss → Final)
- **AJAX guest search functionality** for dynamic user experience
- **Multilingual support** (English/Arabic) with full field internationalization
- **Soft delete capability** for data management with restoration features
- **Integration with Status Plugin** (Workflow Type ID: 3)
- **Approval note functionality** for workflow manager feedback
- **Enhanced audit fields** for comprehensive tracking

**Recent Updates (v0.6)**:
- **Audit Field Enhancement**: Added `timecreated`, `timemodified`, `created_by`, `modified_by` fields
- **Workflow Simplification**: Updated to use simplified workflow approach
- **Status Integration**: Enhanced integration with `local_status` workflow capabilities
- **Multilingual Migration**: Completed migration from single-language to full multilingual support

**Database Tables**:
- `local_residencebooking_request` - Main booking requests
- `local_residencebooking_types` - Accommodation types
- `local_residencebooking_purpose` - Booking purposes

---

### 6. **Room Booking Plugin** (`local_roombooking`)
**Purpose**: Comprehensive classroom and facility booking system
**Version**: 2.0 (Build: 2025061600)
**Status**: MATURITY_STABLE

**Key Features**:
- **Room management system** with capacity and equipment tracking
- **Booking system** with recurring reservation support
- **Conflict detection** to prevent double bookings
- **Approval workflow integration** (Workflow Type ID: 8)
- **Dashboard interface** with filtering and sorting
- **CSV export functionality** for reporting
- **Multi-language support** with RTL support for Arabic

**Capabilities**:
- Full CRUD operations for rooms and bookings
- Time slot management
- Bulk operations support
- Advanced search and filtering

---

### 7. **Computer Service Plugin** (`local_computerservice`)
**Purpose**: IT hardware device request and management system
**Version**: 1.3.1 (Build: 2025072000)
**Status**: MATURITY_STABLE

**Key Features**:
- **Device request system** for IT equipment (projectors, laptops, etc.)
- **Workflow-based approval** with urgent request detection
- **Role-based access control** for different user types
- **Tab-based interface** (Request Devices, Manage Requests, Manage Devices)
- **AJAX-powered operations** for smooth user experience
- **CSV export functionality** for administrative reporting
- **Bilingual support** (English/Arabic)
- **Enhanced transparency** with approval and rejection notes
- **Color-coded status display** for easy status identification
- **Session key validation** for security
- **Race condition prevention** for concurrent requests

**Recent Updates (v1.3.1)**:
- **Enhanced AJAX workflow system** replacing deprecated status manager
- **Improved security** with session key validation and CSRF protection
- **Transparency features** with rejection notes and approval feedback
- **Optimized database schema** with cleaned-up unused fields
- **Better error handling** and user feedback systems

**User Roles**:
- **Students/Teachers**: Submit device requests with urgency indicators
- **Managers**: Review and approve requests with notes
- **Administrators**: Manage device types and system configuration

---

### 8. **Participant Management Plugin** (`local_participant`)
**Purpose**: External and internal participant request management
**Version**: 2.2 (Build: 2025081801)
**Status**: MATURITY_STABLE

**Key Features**:
- **Participant request management** for both internal and external participants
- **Workflow system integration** (Workflow Type ID: 9)
- **Multi-level approval process** with leader and boss reviews
- **Bilingual interface** (English/Arabic)
- **Tab-based navigation** with filtering capabilities
- **Permission-based access** with specific capabilities
- **Oracle integration** for participant data retrieval
- **Enhanced audit trails** with creator and modifier tracking
- **CSV export functionality** for administrative reporting
- **Form validation** and comprehensive error handling

**Recent Updates (v2.2)**:
- **Oracle Data Fields**: Enhanced integration with Oracle DUNIA system
- **Type ID Bug Fix**: Resolved issues with participant type identification
- **Audit Field Standardization**: Added comprehensive tracking fields
- **Enhanced Data Validation**: Improved form validation and error handling

**Capabilities**:
- `local/participant:view` - View participant requests
- `local/participant:addrequest` - Add new participant requests

---

### 9. **Student Reports Plugin** (`local_reports`)
**Purpose**: Student report management with approval workflow
**Version**: v0.1 (Build: 2025041702)
**Status**: MATURITY_ALPHA

**Key Features**:
- **Student report creation** and management
- **Multi-step approval process** for report validation
- **PDF generation** in vertical and horizontal formats
- **AJAX interface** with modal dialogs
- **Tab-based navigation** (pending and approved reports)
- **System-wide overview** across all courses
- **Permission-based access control**

**Functionality**:
- Create and edit reports for individual students
- Workflow-based approval system
- PDF export capabilities
- Multi-language support

---

## Utility and Integration Plugins

### 10. **External Lecturer Management Plugin** (`local_externallecturer`)
**Purpose**: Management of external lecturers and course enrollments
**Version**: 1.6.1 (Build: 2025081400)
**Status**: MATURITY_STABLE

**Key Features**:
- **Lecturer profile management** with complete information tracking
- **Course enrollment system** with cost tracking
- **Pagination support** for large datasets
- **AJAX operations** for smooth user experience
- **CSV export functionality** for reporting
- **Modal forms** for user-friendly data entry
- **Database integration** with proper foreign key relationships

**Database Schema**:
- `externallecturer` - Lecturer profiles (name, age, specialization, organization, degree, passport)
- `externallecturer_courses` - Course enrollments with cost tracking

**Recent Updates (v1.6.1)**:
- **Dual Lecturer Types**: Separate forms for external visitors and resident lecturers
- **Oracle DUNIA Integration**: Enhanced integration for resident lecturer data retrieval via civil number search
- **Lecturer Type Classification**: Database support for distinguishing lecturer types
- **Enhanced UI**: Two distinct creation buttons and workflows
- **Nationality Field**: Extended profile information for lecturers
- **Audit Field Standardization**: Improved tracking with `timecreated`, `timemodified`, `created_by`, `modified_by`

---

### 11. **Oracle Fetch Plugin** (`local_oraclefetch`)
**Purpose**: Centralized Oracle database access for all Moodle local plugins
**Version**: Development/Integration version
**Status**: Production Integration Tool

**Key Features**:
- **Centralized Oracle connection** configuration for all plugins
- **Employee and person data operations** with standardized API
- **AJAX endpoints** for front-end integration (`ajax/search_by_civil.php`, etc.)
- **Helper functions** for easy plugin integration
- **Self-contained design** with local resources
- **Oracle DUNIA integration** for civil number lookups
- **Multi-table support** with consistent error handling
- **Arabic character support** with proper UTF-8 encoding

**Core Functions**:
- `oracle_get_employee_name($pf_number)` - Get employee by PF number
- `oracle_get_person_name($civil_number)` - Get person by civil number
- `oracle_get_person_nationality($civil_number)` - Get nationality data
- `oracle_get_person_data($identifier)` - Get comprehensive person data

**Technical Implementation**:
- Uses OCI8 PHP extension for Oracle connectivity
- Provides standardized API for all local plugins
- AJAX-enabled search functionality
- Integration with external lecturer and participant plugins

**Integration**: Used by External Lecturer and Participant Management plugins for Oracle data retrieval

---

### 12. **Under Work Plugin** (`local_underwork`)
**Purpose**: Display "under construction" messages for pages in development
**Version**: 1.0 (Build: 2023092200)
**Status**: MATURITY_STABLE

**Key Features**:
- **Simple maintenance page** display
- **Multi-language support** (English/Arabic)
- **Capability-based access** (`local/underwork:view`)
- **Admin settings integration**
- **Return to home functionality**

**Use Case**: Provides a professional "under construction" message for features being developed

---

### 13. **Open LMS Utility Plugin** (`local_openlms`)
**Purpose**: Required utility plugin for Open LMS integrations
**Version**: v2.0 (Build: 2023051500)
**Status**: MATURITY_STABLE

**Key Features**:
- **Dependency for Open LMS plugins** such as Programs for Moodle
- **Core utility functions** for Open LMS ecosystem
- **Hook management** for Open LMS integrations
- **Notification system** integration
- **AMD module support** for JavaScript functionality

**Technical Requirements**:
- Requires Moodle 4.1.2 or higher
- Supports Moodle versions 4.01 and 4.02
- Licensed under GNU GPL v3

**Note**: This plugin is required by Open LMS published plugins and provides foundational utilities

---

## Documentation and Support

### 14. **Documentation Folder** (`local/DOCUMENTATION`)
**Purpose**: Centralized documentation for all local plugins

**Contents**:
- **Core Code Change Documentation** - Tracks important system changes
- **Plugin development guidelines** - Best practices and standards
- **Integration documentation** - How plugins work together

---

## Plugin Architecture and Integration

### Workflow Management Architecture
The local plugins follow a centralized workflow architecture:

1. **Status Plugin** serves as the core workflow engine
2. **Service plugins** (Finance, Residence, Computer, etc.) integrate with Status Plugin
3. **Request Services Plugin** provides unified access interface
4. **Individual plugins** handle specific business logic

### Common Features Across Plugins
- **Bilingual support** (English/Arabic) with RTL support
- **Bootstrap-based responsive design**
- **AJAX-powered user interfaces**
- **Mustache template rendering**
- **Role-based access control**
- **CSV export functionality**
- **Integration with Moodle's core features**

### Database Design Patterns
- **Foreign key relationships** to Status Plugin for workflow management
- **Soft delete capabilities** for data retention
- **Multi-language table structures**
- **Proper indexing** for performance

### Security Implementation
- **Capability-based permissions** using Moodle's standard system
- **Input validation** and sanitization
- **Session key validation** for state changes
- **Transaction-protected updates**

---

## Development Standards

All local plugins follow Moodle's development standards:

1. **File Structure**: Standard Moodle plugin structure with version.php, db/, lang/, etc.
2. **Coding Standards**: PHP coding standards with proper documentation
3. **Database Design**: Using Moodle's database abstraction layer
4. **Language Support**: Multi-language implementation with string externalization
5. **Security**: Following Moodle's security practices and capability system
6. **Integration**: Proper integration with Moodle's core features and navigation

## Plugin Summary

This comprehensive system consists of **13 local plugins** providing a complete workflow management and service request platform built specifically for educational institution needs while maintaining full integration with Moodle's core functionality.

### Plugin Status Overview
- **MATURITY_STABLE**: 10 plugins (Status, Request Services, Annual Plans, Finance Services, Room Booking, Computer Service, Participant Management, External Lecturer, Under Work, Open LMS)
- **MATURITY_ALPHA**: 2 plugins (Residence Booking, Student Reports)
- **Production Integration Tool**: 1 plugin (Oracle Fetch)

### Latest Updates (August 2025)
- **Annual Plans v1.9**: Enhanced audit fields and PDF generation capabilities
- **Finance Services v1.10.1**: Clause management improvements and bug fixes
- **Computer Service v1.3.1**: Enhanced AJAX workflow and security improvements
- **Participant Management v2.2**: Oracle integration and type ID bug fixes
- **External Lecturer v1.6.1**: Dual lecturer types and Oracle DUNIA integration
- **Residence Booking v0.6**: Audit field standardization and workflow improvements

This system demonstrates advanced Moodle plugin development practices with comprehensive workflow management, multi-language support, Oracle integration, and modern web technologies, providing a robust platform for educational institution service management. 