# Development Guide

This document provides guidelines for developing and maintaining the External Lecturer Management plugin.

## Development Environment Setup

### Prerequisites
- Moodle 3.11+ development environment
- PHP 7.4+ with debugging enabled
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Code editor with PHP support

### Local Development Setup
1. Clone/copy the plugin to your Moodle development site
2. Enable developer debugging in Moodle
3. Set up your IDE with Moodle coding standards
4. Install browser developer tools for JavaScript debugging

## Coding Standards

### PHP Standards
Follow Moodle's coding standards:
- Use proper indentation (4 spaces)
- Include proper docblocks for all functions and classes
- Use Moodle's database API exclusively
- Implement proper error handling
- Validate all input parameters

### JavaScript Standards
- Use modern JavaScript (ES6+) where appropriate
- Include proper error handling for AJAX calls
- Follow Moodle's JavaScript module patterns
- Use consistent variable naming conventions

### CSS/HTML Standards
- Use Moodle's Bootstrap-based CSS framework
- Ensure responsive design compatibility
- Follow accessibility guidelines
- Use semantic HTML structure

## Database Development

### Schema Changes
When modifying the database schema:
1. Update `db/install.xml` for new installations
2. Create upgrade steps in `db/upgrade.php` for existing installations
3. Increment the version number in `version.php`
4. Test both fresh installations and upgrades

### Database Queries
- Always use Moodle's database abstraction layer (`$DB`)
- Use parameterized queries to prevent SQL injection
- Implement proper error handling for database operations
- Consider performance implications of queries

## Frontend Development

### Mustache Templates
When working with templates:
- Keep logic minimal in templates
- Use proper context variables
- Ensure proper escaping of output
- Test with different data sets

### JavaScript Development
- Use AMD modules for complex JavaScript
- Implement proper event handling
- Test AJAX endpoints thoroughly
- Handle loading states and errors gracefully

## Adding New Features

### New Action Files
When adding new CRUD operations:
1. Create new PHP file in `/actions/` directory
2. Implement proper parameter validation
3. Use JSON responses for AJAX compatibility
4. Include error handling and user feedback
5. Update main.js to handle new endpoints

### New Templates
For new UI components:
1. Create Mustache template in `/templates/`
2. Follow existing naming conventions
3. Use consistent styling classes
4. Test with various data scenarios

### New Language Strings
When adding new text:
1. Add strings to `/lang/en/local_externallecturer.php`
2. Use descriptive string identifiers
3. Consider internationalization from the start
4. Test string rendering in templates

## Testing Procedures

### Manual Testing
1. **Lecturer Management**:
   - Add new lecturers with various data combinations
   - Edit existing lecturer information
   - Delete lecturers and verify cascade effects
   - Test form validation

2. **Course Enrollment**:
   - Enroll lecturers in courses
   - Test cost tracking functionality
   - Verify enrollment counting
   - Test duplicate enrollment prevention

3. **Interface Testing**:
   - Test pagination with different page sizes
   - Verify tab switching functionality
   - Test modal dialogs
   - Validate CSV export functionality

4. **Cross-browser Testing**:
   - Test in Chrome, Firefox, Safari, Edge
   - Verify mobile responsiveness
   - Check JavaScript functionality across browsers

### Database Testing
- Test with empty database
- Test with large datasets for performance
- Verify foreign key constraints
- Test upgrade scenarios

## Debugging

### PHP Debugging
- Enable Moodle's debugging mode
- Use `debugging()` function for temporary debug output
- Check Moodle logs for errors
- Use `var_dump()` or `print_r()` for data inspection

### JavaScript Debugging
- Use browser developer tools
- Check console for errors
- Use `console.log()` for debugging AJAX responses
- Test network requests in browser dev tools

### Database Debugging
- Enable database query logging
- Use phpMyAdmin or similar tools for direct database inspection
- Check query performance with EXPLAIN

## Performance Considerations

### Database Performance
- Use appropriate indexes on frequently queried fields
- Implement pagination for large datasets
- Avoid N+1 query problems
- Consider caching for expensive operations

### Frontend Performance
- Minimize JavaScript file sizes
- Use efficient DOM manipulation
- Implement proper loading states
- Consider lazy loading for large datasets

## Version Control

### Git Workflow
- Use descriptive commit messages
- Create feature branches for new development
- Test thoroughly before merging
- Tag releases with version numbers

### File Organization
- Keep related changes in single commits
- Don't commit debugging code
- Update documentation with code changes
- Maintain clean file structure

## Security Considerations

### Input Validation
- Validate all user inputs using Moodle's parameter functions
- Sanitize data before database insertion
- Escape output in templates
- Implement proper access controls

### AJAX Security
- Verify user permissions in action files
- Use CSRF tokens where appropriate
- Validate request methods (POST/GET)
- Return appropriate error codes

## Deployment

### Pre-deployment Checklist
- [ ] All tests passing
- [ ] Documentation updated
- [ ] Version number incremented
- [ ] Database changes tested
- [ ] Performance tested with production-like data
- [ ] Security review completed

### Deployment Process
1. Backup current version
2. Deploy new code
3. Run database upgrades if needed
4. Clear Moodle caches
5. Test critical functionality
6. Monitor for errors

## Troubleshooting Common Issues

### Database Errors
- Check table structure matches install.xml
- Verify foreign key constraints
- Check for data type mismatches

### Template Errors
- Verify context variables are passed correctly
- Check for typos in variable names
- Ensure proper template syntax

### JavaScript Errors
- Check browser console for syntax errors
- Verify AJAX endpoints are accessible
- Check for missing JavaScript dependencies

## Contributing

When contributing to this plugin:
1. Follow established coding patterns
2. Write clear, documented code
3. Test thoroughly in different scenarios
4. Update documentation as needed
5. Consider backward compatibility 