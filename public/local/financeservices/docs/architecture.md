# Architecture Overview

## 🏗️ Plugin Architecture

The Finance Services plugin follows standard Moodle plugin architecture patterns with a modular, MVC-inspired design.

## 🎯 Design Principles

### 1. **Separation of Concerns**
- **Models**: Data access and business logic (`classes/`)
- **Views**: Presentation layer (`templates/`, `pages/`)
- **Controllers**: Request handling (`index.php`, `actions/`)

### 2. **Workflow-Driven Design**
- Core functionality built around approval workflows
- Status transitions managed by `simple_workflow_manager`
- Integration with `local_status` plugin for status management

### 3. **Bilingual Architecture**
- Language-agnostic data storage
- Dynamic language switching at display time
- Separate language strings for English and Arabic

## 🔧 Core Components

### Main Entry Point (`index.php`)
```php
┌─────────────────┐
│   index.php     │ ← Main controller with tab navigation
├─────────────────┤
│ • Tab routing   │
│ • Form handling │
│ • Data display  │
└─────────────────┘
```

### Workflow Management
```php
┌─────────────────────────┐
│ simple_workflow_manager │ ← Core workflow logic
├─────────────────────────┤
│ • Status transitions    │
│ • Approval/rejection    │
│ • Permission checking   │
│ • History tracking      │
└─────────────────────────┘
```

### Form System
```php
┌─────────────────┐
│ Form Classes    │ ← Moodle formslib integration
├─────────────────┤
│ • add_form      │ ← Request submission
│ • filter_form   │ ← List filtering
│ • clause_form   │ ← Clause management
│ • funding_type  │ ← Funding type management
└─────────────────┘
```

## 🗄️ Data Layer

### Database Tables
```
┌─────────────────────────┐
│ local_financeservices   │ ← Main requests table
├─────────────────────────┤
│ • Request details       │
│ • Status tracking       │
│ • User associations     │
└─────────────────────────┘
            │
            ├── foreign key references
            ▼
┌─────────────────────────┐
│ local_financeservices_  │ ← Configuration tables
│ funding_type            │
│ clause                  │ 
└─────────────────────────┘
```

### Status Integration
```
┌─────────────────┐
│ local_status    │ ← External dependency
├─────────────────┤
│ • Status states │
│ • Transitions   │
│ • Permissions   │
└─────────────────┘
```

## 🎨 Presentation Layer

### Template System
- **Mustache templates** for consistent rendering
- **Bootstrap styling** for responsive design
- **AJAX interactions** for dynamic updates

### Page Structure
```
index.php (Main Controller)
├── Tab: Add (Request Submission)
├── Tab: List (Request Viewing)
└── Tab: Manage (Configuration)
    ├── Funding Types
    └── Clauses
```

## 🔄 Request Lifecycle

### 1. **Request Creation**
```
User Form → Validation → Database → Event Trigger → Redirect
```

### 2. **Workflow Processing**
```
Status Change → Permission Check → Update Record → Log Event → AJAX Response
Note: Rejections always return to Leader 1 Review (except Leader 1 → Final Rejection)
```

### 3. **Data Retrieval**
```
Filter Form → SQL Query → Template Rendering → Display
```

## 🔐 Security Architecture

### Authentication & Authorization
- **Moodle session management** for user authentication
- **Capability-based permissions** for action authorization
- **Context-aware access control** for data visibility

### Input Validation
- **Moodle parameter cleaning** for all inputs
- **Form validation** using formslib
- **SQL injection prevention** via parameterized queries

### CSRF Protection
- **Session keys** for all form submissions
- **AJAX request validation** with session tokens
- **Race condition prevention** in workflow actions

## 🌐 Internationalization

### Language Strategy
- **Dynamic language detection** using `current_language()`
- **Database field selection** based on language
- **Fallback mechanisms** for missing translations

### Implementation Pattern
```php
// Example of language-aware field selection
$field = (current_language() === 'ar') ? 'name_ar' : 'name_en';
$sql = "SELECT {$field} as display_name FROM table";
```

## 📦 Plugin Dependencies

### Core Dependencies
- **Moodle 3.9+** - Base platform
- **local_status** - Workflow status management
- **PHP 7.4+** - Language requirements

### Optional Integrations
- **JavaScript libraries** - For enhanced UI interactions
- **CSS frameworks** - Bootstrap for styling
- **External APIs** - For extended functionality

## 🔄 Event-Driven Architecture

### Event System
```php
┌─────────────────┐
│ Event Classes   │ ← Moodle event system
├─────────────────┤
│ • request_*     │ ← Request lifecycle events
│ • fundingtype_* │ ← Configuration events
│ • clause_*      │ ← Management events
└─────────────────┘
```

### Event Flow
```
Action → Event Creation → Event Trigger → Logging → Notification
```

## 🚀 Performance Considerations

### Database Optimization
- **Indexed foreign keys** for efficient joins
- **Parameterized queries** for query plan caching
- **Selective field loading** to minimize data transfer

### Caching Strategy
- **Moodle cache API** for configuration data
- **Static variable caching** for repeated calculations
- **Template caching** via Mustache engine

### AJAX Optimization
- **Minimal data payloads** in JSON responses
- **Client-side state management** to reduce requests
- **Debounced interactions** to prevent request flooding

This architecture ensures maintainability, security, and performance while following Moodle best practices. 