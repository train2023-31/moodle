# Documentation Changelog - Finance Calculator Plugin

## Version 1.0.2 - January 2025

### File Structure Reorganization

#### Pages Directory
- **Moved page files**: `report.php` and `clause_report.php` moved to `pages/` directory
- **Updated navigation**: All internal links updated to reflect new file locations
- **Added documentation**: Created `pages/README.md` to explain the new structure
- **Preserved functionality**: All navigation and links continue to work correctly

#### Documentation Updates
- **Clarified README structure**: Each README file now has a clear purpose
- **Updated file paths**: All documentation updated to reflect new page locations
- **Enhanced navigation**: Added cross-references between documentation files
- **Version bump**: Updated to version 2025082801 (1.0.1) to reflect structural changes

## Version 1.0.1 - January 2025

### Fixed Issues

#### API.md
- **Removed non-existent events**: Removed references to `\local_financecalc\event\data_refreshed` event that doesn't exist in the implementation
- **Removed non-existent external API**: Removed external API integration examples that aren't implemented
- **Updated database schema**: Added missing indexes (`yearidx`, `timecreatedidx`) to match actual database schema
- **Corrected method documentation**: Ensured all method names and parameters match the actual implementation
- **Added dependency information**: Documented that scheduled tasks require `local_financeservices` and `local_participant` plugins

#### INSTALLATION.md
- **Updated database schema**: Added missing indexes to match actual implementation
- **Added dependency troubleshooting**: Added section for scheduled task dependency issues
- **Enhanced verification checklist**: Added dependency verification items
- **Improved manual installation**: Clarified that tables are created automatically during installation

#### README.md
- **Updated caching strategy**: Changed from "Live Calculation" to "Hybrid Calculation" to better reflect the implementation
- **Added fallback mechanism**: Documented that the system falls back to live calculation if cache fails
- **Updated database schema**: Added missing indexes to match actual implementation
- **Added dependency information**: Documented scheduled task dependencies
- **Enhanced troubleshooting**: Added section for scheduled task dependency issues
- **Updated version history**: Added dependency checking to the feature list

#### USER_GUIDE.md
- **Clarified refresh button visibility**: Made it clear that refresh button is only visible to managers
- **Added dependency troubleshooting**: Added information about missing dependency plugins
- **Enhanced troubleshooting section**: Added section for when refresh button is not visible
- **Updated glossary**: Added terms for live calculation, hybrid mode, and dependencies
- **Improved permission explanations**: Better clarified who can see and use different features

### General Improvements

#### Accuracy
- All documentation now accurately reflects the current implementation
- Removed references to features that don't exist
- Corrected method names, parameters, and return values
- Updated database schema to match actual implementation

#### Completeness
- Added missing dependency information
- Enhanced troubleshooting sections
- Improved permission explanations
- Added more detailed error scenarios

#### Clarity
- Better explained the hybrid caching system
- Clarified who can access different features
- Improved step-by-step instructions
- Enhanced glossary with technical terms

### Technical Details

#### Database Schema Updates
- Added `INDEX yearidx (year)` to cache table
- Added `INDEX timecreatedidx (timecreated)` to cache table
- Updated all SQL examples to include proper indexes

#### API Documentation
- Removed non-existent event system
- Removed non-existent external API functions
- Updated method signatures to match implementation
- Added proper error handling examples

#### Installation Guide
- Added dependency verification steps
- Enhanced troubleshooting for missing plugins
- Improved manual installation instructions
- Added verification checklist items

#### User Guide
- Clarified permission requirements
- Added dependency-related troubleshooting
- Enhanced interface explanations
- Improved best practices section

### Files Modified

1. **API.md** - Major updates to remove non-existent features and correct implementation details
2. **INSTALLATION.md** - Added dependency information and enhanced troubleshooting
3. **README.md** - Updated caching strategy and added dependency documentation
4. **USER_GUIDE.md** - Clarified permissions and added dependency troubleshooting
5. **CHANGELOG.md** - New file documenting all changes

### Compatibility

- All documentation now accurately reflects the current plugin implementation
- No breaking changes to the actual plugin functionality
- Documentation is compatible with Moodle 4.1+ as specified
- All examples use current API methods and parameters

---

**Documentation Version**: 1.0.1  
**Plugin Version**: 2025082800  
**Last Updated**: January 2025  
**Compatible with**: Moodle 4.1+
