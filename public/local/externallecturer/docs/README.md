# External Lecturer Management Plugin

A comprehensive Moodle local plugin for managing external lecturers and their course enrollments with cost tracking and bilingual support.

## 🎯 Overview

The External Lecturer Management plugin provides educational institutions with a complete solution for managing external teaching staff. It enables administrators to track lecturer profiles, manage course enrollments, monitor costs, and generate reports - all within Moodle's familiar interface.

## ✨ Key Features

### 📋 Lecturer Management
- **Complete Profile Management**: Add, edit, and delete external lecturer profiles
- **Dual Lecturer Types**: Separate creation forms for "external_visitor" and "resident" lecturers with distinct workflows
- **Oracle Database Integration**: Autocomplete functionality for external visitors (name-based) and civil number search for residents
- **Comprehensive Data Tracking**: Store personal details, qualifications, organizational affiliations, and nationality
- **Identification Support**: Track passport numbers and civil identification with Oracle DUNIA integration
- **Workload Monitoring**: Automatic tracking of course assignments per lecturer
- **Audit Trail**: Track who created each record and when it was last modified with standardized audit fields

<!-- Course enrollment functionality has been removed from this plugin -->

### 🌐 User Experience
- **Bilingual Interface**: Full support for English and Arabic
- **Responsive Design**: Works seamlessly on desktop and mobile devices
- **AJAX Operations**: Smooth, real-time interactions without page reloads
- **Modal Forms**: User-friendly dialog-based data entry
- **Search & Pagination**: Efficient handling of large datasets
- **Audit Trail Display**: View who created records and when they were last modified

### 📊 Data Management
- **CSV Export**: Generate reports for external analysis
- **Database Integration**: Proper Moodle database abstraction
- **Data Validation**: Comprehensive input validation and error handling
- **Audit Trail**: Track all modifications and deletions

## 🏗️ Technical Architecture

### Database Schema
- **`externallecturer`**: Stores lecturer profiles and qualifications with audit fields (`timecreated`, `timemodified`, `created_by`, `modified_by`), lecturer type classification (`lecturer_type`), and nationality support

### Technology Stack
- **Backend**: PHP 7.4+ (Moodle compatible)
- **Database**: MySQL/MariaDB with foreign key relationships
- **Frontend**: JavaScript (ES6+), HTML5, CSS3
- **Templates**: Mustache templating engine
- **UI**: Bootstrap-compatible responsive design

## 📋 System Requirements

### Minimum Requirements
- **Moodle**: 3.11 or higher
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Browser**: Modern browsers with JavaScript enabled

### Recommended Requirements
- **Moodle**: 4.0 or higher
- **PHP**: 8.0 or higher
- **Memory**: 512MB PHP memory limit
- **Database**: MySQL 8.0+ or MariaDB 10.6+

## 🚀 Quick Start

1. **Installation**: Copy plugin files to `local/externallecturer/`
2. **Database Setup**: Run Moodle's database upgrade process
3. **Permissions**: Assign `local/externallecturer:manage` capability
4. **Access**: Navigate to `/local/externallecturer/index.php`

## 📚 Documentation

- **[Installation Guide](INSTALLATION.md)** - Complete setup instructions
- **[API Documentation](API.md)** - Technical specifications
- **[Development Guide](DEVELOPMENT.md)** - Contributing guidelines
- **[Troubleshooting](TROUBLESHOOTING.md)** - Common issues and solutions
- **[Examples](EXAMPLES.md)** - Practical use cases

## 🔧 Configuration

### Permissions
The plugin requires the `local/externallecturer:manage` capability, typically assigned to:
- Site administrators
- Course creators (optional)
- Custom roles with appropriate privileges

### Language Support
- **English**: Primary language with complete translation
- **Arabic**: Full bilingual support with RTL layout
- **Extensible**: Easy to add additional languages

## 📈 Version Information

- **Current Version**: 1.6.2 (2025081500)
- **Moodle Compatibility**: 3.11+
- **Last Updated**: August 2025
- **License**: GNU GPL v3

## 🤝 Support

For technical support, bug reports, or feature requests:
1. Check the [Troubleshooting Guide](TROUBLESHOOTING.md)
2. Review the [API Documentation](API.md)
3. Consult the [Examples](EXAMPLES.md) for common use cases

## 📄 License

This plugin is licensed under the GNU General Public License v3. See the LICENSE file for details. 