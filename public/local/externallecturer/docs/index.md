# External Lecturer Management Plugin Documentation

Welcome to the comprehensive documentation for the External Lecturer Management plugin version 1.5. This documentation provides everything you need to understand, install, develop, and maintain this Moodle local plugin.

## 📚 Documentation Overview

This documentation is organized into several comprehensive guides:

### 🚀 Getting Started
- **[README.md](README.md)** - Plugin overview, features, and quick start guide
- **[INSTALLATION.md](INSTALLATION.md)** - Complete installation and configuration guide
- **[EXAMPLES.md](EXAMPLES.md)** - Practical examples and use cases

### 🔧 Development & Technical
- **[DEVELOPMENT.md](DEVELOPMENT.md)** - Development guidelines and best practices
- **[STRUCTURE.md](STRUCTURE.md)** - Detailed file structure and organization
- **[API.md](API.md)** - API documentation and technical specifications

### 📋 Maintenance & Support
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and updates

## 🎯 Quick Navigation

### For Administrators
Start with → [README.md](README.md) → [INSTALLATION.md](INSTALLATION.md) → [EXAMPLES.md](EXAMPLES.md)

### For Developers
Start with → [STRUCTURE.md](STRUCTURE.md) → [DEVELOPMENT.md](DEVELOPMENT.md) → [API.md](API.md)

### For Troubleshooting
Go to → [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

## 📖 What is External Lecturer Management?

The External Lecturer Management plugin is a comprehensive Moodle local plugin designed to manage external lecturers and their course enrollments within educational institutions. It provides a complete interface for:

- **Managing external lecturer profiles** with detailed information and type classification
- **Dual lecturer types** supporting both external visitors and residents
- **Tracking course enrollments** with associated costs
- **Monitoring lecturer workload** and utilization
- **Exporting data** for reporting and analysis
- **Bilingual support** for Arabic and English interfaces

## ✨ Key Features at a Glance

| Feature | Description |
|---------|-------------|
| **Lecturer Management** | Add, edit, delete external lecturer profiles with type classification |
| **Course Enrollment** | Assign lecturers to courses with cost tracking |
| **Pagination** | Efficient handling of large datasets |
| **AJAX Interface** | Smooth, responsive user experience |
| **CSV Export** | Data export for reporting and analysis |
| **Modal Forms** | User-friendly dialog-based data entry |
| **Database Integration** | Proper Moodle database abstraction |
| **Bilingual Support** | Full English and Arabic translation |
| **Civil Number Tracking** | Optional civil identification support |
| **Dual Lecturer Types** | Separate forms for external visitors and residents |
| **Audit Trail Display** | View who created records and when they were last modified |

## 🏗️ Plugin Architecture

```
External Lecturer Plugin v1.6.1
├── Database Layer
│   ├── externallecturer (profiles + civil_number)
│   └── externallecturer_courses (enrollments)
├── Backend (PHP)
│   ├── CRUD Operations (/actions/)
│   ├── Rendering Classes (/classes/)
│   └── Database Schema (/db/)
├── Frontend (JavaScript/CSS)
│   ├── User Interface (/templates/)
│   ├── AJAX Operations (/js/)
│   └── Internationalization (/lang/)
└── Configuration
    ├── Version Management (1.5)
    └── Installation Scripts
```

## 📊 Database Schema Overview

### Main Tables

**`externallecturer`** - Stores lecturer profile information
- Personal details (name, age, organization)
- Qualifications (degree, specialization)
- Identification (passport number, civil_number)
- Usage tracking (course count)

<!-- The externallecturer_courses table has been removed from this plugin -->

## 🛠️ Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 7.4+ (Moodle compatible) |
| **Database** | MySQL/MariaDB with foreign keys |
| **Frontend** | JavaScript (ES6+), HTML5, CSS3 |
| **Templates** | Mustache templating engine |
| **UI Framework** | Bootstrap (Moodle theme compatible) |
| **AJAX** | Fetch API with JSON responses |
| **Languages** | English (primary), Arabic (RTL support) |

## 📋 System Requirements

### Minimum Requirements
- **Moodle Version**: 3.11 or higher
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Browser Support**: Modern browsers with JavaScript enabled

### Recommended Requirements
- **Moodle Version**: 4.0 or higher
- **PHP Version**: 8.0 or higher
- **Memory**: 512MB PHP memory limit minimum
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

## 🔄 Version Information

- **Current Version**: 1.6.2 (2025081500)
- **Release Date**: August 2025
- **Moodle Compatibility**: 3.11+
- **License**: GNU GPL v3

### What's New in Version 1.6.1

- **Dual Lecturer Types**: Separate forms for external visitors and resident lecturers
- **Oracle DUNIA Integration**: Enhanced integration for resident lecturer data retrieval via civil number search
- **Lecturer Type Classification**: Database support for distinguishing lecturer types
- **Enhanced UI**: Two distinct creation buttons and workflows
- **Nationality Field**: Extended profile information for lecturers
- **Audit Field Standardization**: Improved tracking with `timecreated`, `timemodified`, `created_by`, `modified_by`

## 🚀 Quick Start Guide

1. **Installation**
   ```bash
   # Copy plugin to Moodle directory
   cp -r externallecturer /path/to/moodle/local/
   
   # Set permissions
   chmod -R 755 /path/to/moodle/local/externallecturer
   ```

2. **Database Setup**
   - Visit Site Administration > Notifications
   - Complete database upgrade process

3. **Configuration**
   - Assign `local/externallecturer:manage` capability
   - Configure language settings if needed

4. **Access**
   - Navigate to `/local/externallecturer/index.php`
   - Start managing external lecturers

## 📖 Documentation Sections

### Installation & Setup
- **[INSTALLATION.md](INSTALLATION.md)** - Complete installation guide with troubleshooting
- **[EXAMPLES.md](EXAMPLES.md)** - Real-world usage examples and scenarios

### Development & Customization
- **[STRUCTURE.md](STRUCTURE.md)** - Detailed file organization and architecture
- **[DEVELOPMENT.md](DEVELOPMENT.md)** - Development guidelines and best practices
- **[API.md](API.md)** - Complete API documentation with examples

### Maintenance & Support
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Comprehensive troubleshooting guide
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and upgrade notes

## 🔧 Configuration Options

### Permissions
- `local/externallecturer:manage` - Required for all plugin operations
- System context permissions
- Role-based access control

### Language Support
- **English**: Primary language with complete translation
- **Arabic**: Full bilingual support with RTL layout
- **Extensible**: Easy to add additional languages

### Database Configuration
- Automatic table creation during installation
- Foreign key constraints for data integrity
- Cascade deletes for maintaining consistency

## 📊 Data Management

### Lecturer Information
- Personal details (name, age, organization)
- Qualifications (degree, specialization)
- Identification (passport, civil number)
- Course count tracking

### Course Enrollments
- Lecturer-course assignments
- Cost tracking in OMR (Omani Rial)
- Duplicate prevention
- Automatic course count updates

### Export Capabilities
- CSV export for lecturer data
- CSV export for course enrollments
- Integration with external reporting tools

## 🔒 Security Features

### Input Validation
- Comprehensive parameter validation
- SQL injection prevention
- XSS protection through output escaping

### Access Control
- Role-based permissions
- System context security
- User authentication requirements

### Data Protection
- Secure database operations
- Proper error handling
- Audit trail capabilities

## 🚀 Performance Optimization

### Database Optimization
- Efficient query design
- Proper indexing strategy
- Pagination for large datasets

### Frontend Performance
- AJAX operations for smooth UX
- Optimized JavaScript loading
- Responsive design implementation

### Caching Strategy
- Moodle's built-in caching
- Session-based data caching
- Query result optimization

## 🤝 Support & Community

### Getting Help
1. **Check Documentation**: Start with this guide and related documents
2. **Troubleshooting**: Review the troubleshooting guide for common issues
3. **Community Support**: Reach out to Moodle community forums
4. **Developer Resources**: Consult Moodle developer documentation

### Contributing
- Follow Moodle coding standards
- Test thoroughly before submitting
- Document any changes made
- Respect the plugin's architecture

## 📄 License Information

This plugin is licensed under the GNU General Public License v3. See the LICENSE file for complete license terms.

---

**Last Updated**: August 2025  
**Plugin Version**: 1.6.1 (2025081400)  
**Documentation Version**: 1.6.1 