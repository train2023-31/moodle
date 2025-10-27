# Room Booking Plugin Documentation

Welcome to the comprehensive documentation for the Moodle Room Booking Plugin. This documentation covers everything you need to know about working with and developing this plugin.

## Documentation Index

### 📋 General Documentation
- **[Plugin Overview](plugin-overview.md)** - Introduction to the plugin, features, and capabilities
- **[Installation Guide](installation.md)** - How to install and configure the plugin
- **[User Guide](user-guide.md)** - How to use the plugin features

### 🛠️ Developer Documentation
- **[Development Guide](development-guide.md)** - How to set up development environment and contribute
- **[File Structure](file-structure.md)** - Detailed explanation of every file and directory
- **[Database Schema](database-schema.md)** - Database tables and relationships
- **[Code Architecture](code-architecture.md)** - Plugin architecture and design patterns
- **[API Reference](api-reference.md)** - Classes, methods, and functions reference

### 🔧 Technical Documentation
- **[Workflow Integration](workflow-integration.md)** - How the approval workflow system works
- **[Permissions & Capabilities](permissions.md)** - Role-based access control
- **[Customization Guide](customization.md)** - How to extend and customize the plugin

## Quick Start

1. Read the [Plugin Overview](plugin-overview.md) to understand what this plugin does
2. Check the [File Structure](file-structure.md) to understand the codebase organization
3. Review the [Development Guide](development-guide.md) to start contributing
4. Refer to the [API Reference](api-reference.md) when working with the code

## Plugin Information

- **Version**: 2.0 (approval workflow)
- **Version Code**: 2025061600
- **Moodle Requirement**: 4.1 minimum
- **Maturity**: MATURITY_STABLE
- **Component**: local_roombooking

## Recent Updates

- **Workflow System Updated**: Rejection behavior modified so that rejections from Leader 2, 3, and Boss levels return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status

## Support and Contributing

This plugin includes a comprehensive room booking system with workflow integration. See the development guide for information on how to contribute to the project. 