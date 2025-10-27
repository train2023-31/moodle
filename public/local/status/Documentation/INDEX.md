# Status Plugin Documentation Index

## Overview

This documentation folder contains comprehensive guides for understanding, developing with, and maintaining the Status Plugin (`local_status`). The Status Plugin serves as a centralized workflow management system for Moodle plugins.

## Documentation Files

### 📖 Main Documentation

#### [README.md](README.md)
**Primary plugin overview and quick start guide**
- Plugin information and version details
- Core features and capabilities overview
- Supported workflow types and integration list
- Quick start instructions
- Security model and admin interface guide

### 🔧 Development Documentation

#### [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
**Complete guide for developers working with the Status Plugin**
- Development environment setup
- Core architecture explanation
- Plugin integration patterns and examples
- Common workflow manager implementation
- Best practices and troubleshooting
- Performance considerations and error handling

#### [FILE_STRUCTURE.md](FILE_STRUCTURE.md)
**Detailed explanation of every file in the plugin**
- Complete file-by-file documentation
- Purpose and functionality of each file
- File dependencies and relationships
- Integration points and usage analysis
- Performance and security considerations

### 🔄 Integration Documentation

#### [PLUGIN_INTERACTIONS.md](PLUGIN_INTERACTIONS.md)
**How other plugins integrate with the Status Plugin**
- Currently integrated plugins (Finance, Residence, Computer Service, etc.)
- Integration patterns and database design
- Common code templates and examples
- Capability system integration
- Installation and upgrade procedures

#### [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md)
**Technical architecture of the workflow engine**
- Core workflow engine components
- Database schema relationships and design
- Workflow processing logic and state management
- Approval types and sequencing system
- Department-specific workflow implementation
- Advanced features (dynamic approvers, audit trails)
- Performance optimization and error handling

### 🔍 Analysis Documentation

#### [UNUSED_FILES.md](UNUSED_FILES.md)
**Analysis of file usage patterns in the plugin**
- Classification of actively used vs. diagnostic files
- Usage analysis by frequency and purpose
- File retention recommendations
- Cleanup and maintenance suggestions
- Performance monitoring recommendations

## Documentation Purpose by Audience

### 👨‍💼 For System Administrators
**Primary Documents:**
- [README.md](README.md) - Overview and quick start
- [PLUGIN_INTERACTIONS.md](PLUGIN_INTERACTIONS.md) - Understanding current integrations

**Secondary Documents:**
- [UNUSED_FILES.md](UNUSED_FILES.md) - Maintenance guidance
- [FILE_STRUCTURE.md](FILE_STRUCTURE.md) - Understanding plugin structure

### 👨‍💻 For Plugin Developers
**Primary Documents:**
- [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) - Complete development guide
- [PLUGIN_INTERACTIONS.md](PLUGIN_INTERACTIONS.md) - Integration patterns and examples

**Secondary Documents:**
- [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) - Technical architecture details
- [FILE_STRUCTURE.md](FILE_STRUCTURE.md) - Understanding file relationships

### 🏗️ For Core Contributors
**Primary Documents:**
- [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) - Engine architecture and logic
- [FILE_STRUCTURE.md](FILE_STRUCTURE.md) - Complete file documentation

**Secondary Documents:**
- [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) - Development workflows
- [UNUSED_FILES.md](UNUSED_FILES.md) - Maintenance analysis

## Quick Navigation

### Getting Started
1. Start with [README.md](README.md) for plugin overview
2. Review [PLUGIN_INTERACTIONS.md](PLUGIN_INTERACTIONS.md) to understand current integrations
3. Follow [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for development setup

### Integrating with Status Plugin
1. Read integration patterns in [PLUGIN_INTERACTIONS.md](PLUGIN_INTERACTIONS.md)
2. Follow step-by-step integration in [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
3. Reference [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) for advanced features

### Understanding Plugin Architecture
1. Review [FILE_STRUCTURE.md](FILE_STRUCTURE.md) for file organization
2. Study [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) for technical details
3. Check [UNUSED_FILES.md](UNUSED_FILES.md) for usage patterns

### Maintenance and Troubleshooting
1. Use [UNUSED_FILES.md](UNUSED_FILES.md) for maintenance guidance
2. Reference [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for troubleshooting
3. Review [FILE_STRUCTURE.md](FILE_STRUCTURE.md) for dependency understanding

## Additional Resources

### Related Documentation in Plugin Root
- **README.md** - Original plugin documentation (consider this Documentation folder as supplementary)
- **WORKFLOW_SEPARATION_SUMMARY.md** - Historical workflow manager separation
- **WORKFLOW_RENAMING_SUMMARY.md** - Historical class renaming documentation
- **DYNAMIC_APPROVER_README.md** - Dynamic approver system features
- **DEPARTMENT_CAPABILITIES.md** - Department structure and capabilities

### Diagnostic Tools
- **diagnostic_workflow.php** - CLI script for troubleshooting workflow issues
- Use as documented in [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)

### External Resources
- **Moodle Development Documentation**: https://moodledev.io/
- **Local Plugin Guidelines**: https://moodledev.io/docs/apis/plugintypes/local
- **Database API Documentation**: https://moodledev.io/docs/apis/core/dml

## Contributing to Documentation

### Updating Documentation
1. Keep documentation synchronized with code changes
2. Update version references when plugin version changes
3. Add examples for new integration patterns
4. Update file lists when plugin structure changes

### Documentation Standards
- Use clear, descriptive headings
- Include code examples with explanations
- Provide both overview and detailed technical information
- Cross-reference related documentation sections
- Maintain consistent formatting and style

### Adding New Documentation
- Place new files in this Documentation folder
- Update this INDEX.md file with new entries
- Follow existing naming conventions
- Include purpose statement and target audience

This documentation folder provides comprehensive coverage of the Status Plugin from multiple perspectives, ensuring that administrators, developers, and contributors can effectively work with the system. 