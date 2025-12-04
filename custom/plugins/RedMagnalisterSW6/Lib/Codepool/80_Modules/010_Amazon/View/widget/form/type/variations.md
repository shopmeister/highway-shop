# Amazon Variations Attribute Matching Implementation

## Overview
The `variations.php` file implements a dynamic attribute matching interface for Amazon product variations. It allows users to map shop attributes to Amazon marketplace attributes with sophisticated matching capabilities.

## File Location
```
/magnalister/Codepool/80_Modules/010_Amazon/View/widget/form/type/variations.php
```

## Key Components

### 1. PHP Backend Integration
- **Variation Group Management**: Handles variation groups from Amazon categories
- **Shop Attributes**: Retrieves and processes shop-specific attributes
- **Marketplace Attributes**: Fetches Amazon marketplace required and optional attributes
- **Saved Values**: Loads previously saved attribute mappings

### 2. HTML Templates
The file contains several hidden HTML templates used for dynamic content generation:

#### Core Templates
- **Attribute Row Template**: Used for both required and optional attributes
  - Contains shop attribute selector dropdown
  - Includes attribute matching content area
  - Shows attribute name, description, and required status

#### Matching Templates
- **Freetext Template**: Text input with "Use shop values" checkbox for text/selectandtext shop attributes
- **Attribute Value Template**: Direct Amazon value selection dropdown
- **Attribute Value Freetext Template**: Text input for Amazon attributes with dataType 'text' (no predefined values)
- **Shop Matching Template**: Table for mapping shop values to Amazon values
- **Matching Row Template**: Individual row for value-to-value mapping

#### Utility Templates
- **Option Template**: Reusable template for generating select options
- **Delete Button Template**: Template for the + button used to add optional attributes
- **Optional Attributes Selector Template**: Dropdown for selecting hidden optional attributes

### 3. JavaScript Implementation

#### Main Class: `AmazonAttributeMatcher`
The core JavaScript class that manages the entire attribute matching interface.

##### Key Methods:

**`init()`**
- Initializes the component
- Loads data and renders attributes
- Binds event handlers

**`renderAttributes()`**
- Renders required attributes in the required section
- Renders optional attributes with saved values visible
- Adds optional attribute selector for unused attributes
- Controls visibility of optional attributes section

**`renderAttributeRow(key, attribute, $container, isVisible)`**
- Creates individual attribute rows
- Handles both required and optional attributes
- Sets up shop attribute dropdown
- Manages visibility for optional attributes

**`handleShopAttributeChange($row, attributeKey, selectedValue)`**
Handles different attribute selection types:
- **'freetext'**: Shows text input with "Use shop values" checkbox
- **'attribute_value'**:
  - Shows Amazon value dropdown for attributes with predefined values
  - Shows text input for attributes with dataType 'text' (no values)
  - Only enabled for Amazon attributes with dataType 'text' or 'selectandtext'
- **Shop attributes (select/multiSelect)**: Shows value matching table
- **Shop attributes (text/selectandtext)**: Shows freetext input with "Use shop values" checkbox

**`renderShopAttributeMatching($container, attributeKey, shopAttrKey, amazonAttribute)`**
- Creates matching table for shop-to-Amazon value mapping
- Loads saved matching values if available
- Manages initial row creation with proper button states

**`addMatchingRow($tableBody, attributeKey, savedValue, hasSavedMatchingValues)`**
Key features:
- Creates new matching rows dynamically
- **Minus Button Logic**:
  - Disabled for first empty row when no saved values exist
  - Enabled when row has saved values
  - Enabled for all manually added rows
  - Enabled when user selects values in dropdowns
- Populates shop and Amazon value options
- Restores saved value selections

**`renderOptionalAttributesSelector($container)`**
- Creates dropdown for selecting optional attributes
- Only shows if optional attributes are available
- Dynamically shows/hides optional attribute rows

**`renderFreetextInputForAttributeValue(attributeKey, $container, amazonAttribute)`**
- Renders text input for Amazon attributes with dataType 'text'
- Used when "attribute_value" is selected for text-type Amazon attributes
- Includes placeholder for entering Amazon values directly

## Special Features

### Template-Based HTML Generation
All HTML generation is now handled through PHP templates rather than JavaScript string concatenation:
- Improves maintainability and separation of concerns
- Templates use placeholder tokens (e.g., `{{VALUE}}`, `{{TEXT}}`) for dynamic replacement
- JavaScript uses `$('#template-id').html()` to get templates and `.replace()` for token substitution

### Event Initialization
- Change events are triggered immediately after element initialization
- No setTimeout usage - events fire synchronously as elements are created
- Ensures dependent UI elements update correctly on page load

### Conditional Feature Enabling
- **"Amazon Attribute Value" Option**:
  - Disabled for Amazon attributes with dataType other than 'text' or 'selectandtext'
  - Prevents invalid selections for structured attribute types

### Checkbox Implementation
- "Use shop values" checkboxes added for text/selectandtext attributes
- Hidden input ensures form value submission even when unchecked
- Proper label association for accessibility

### Minus Button Control Logic
The implementation includes sophisticated control of the remove (minus) button:

1. **Initial State**:
   - First row's minus button is disabled if no saved values exist
   - Prevents accidental removal of required empty row

2. **Enable Conditions**:
   - When saved values are loaded into the row
   - When user selects a value in shop or Amazon dropdown
   - For any manually added rows (beyond the first)

3. **Event Handlers**:
   ```javascript
   // Click handler prevents removal if button is disabled
   $(document).on('click', '.remove-matching-row', function () {
       if (!$button.prop('disabled')) {
           $(this).closest('tr').remove();
       }
   });

   // Change handler enables button when values are selected
   $(document).on('change', '.shop-value-select, .amazon-matching-select', function () {
       if (shopVal && shopVal !== 'noselection' ||
           amazonVal && amazonVal !== 'noselection') {
           $removeButton.prop('disabled', false).css('opacity', '1');
       }
   });
   ```

### Translation Keys
The implementation uses centralized I18n translation keys:
- `attributes_matching_title` - Section header
- `attributes_matching_shop_value` - Shop value column
- `attributes_matching_marketplace_value` - Marketplace value column
- `attributes_matching_web_shop_attribute` - Shop attribute label
- `AttributeMatching_AutoMatching_UseShopValue` - Checkbox label
- `form_type_matching_select_optional` - "Please select" option
- `ML_LABEL_DONT_USE` - "Do not use" option

### Select2 Integration
Performance-optimized Select2 initialization:
- Standard selects: Search shown for >10 options
- Large lists (>200 options): Requires 2 characters for search
- Matching table selects: Custom settings for shop/Amazon values

### Data Flow

1. **Page Load**:
   ```
   PHP Backend → Shop Attributes → JSON Encoding → JavaScript
                → MP Attributes  →               ↓
                → Saved Values   →     AmazonAttributeMatcher.init()
   ```

2. **User Interaction**:
   ```
   User selects shop attribute → handleShopAttributeChange()
                                          ↓
                    Determines type → Renders appropriate template
                                          ↓
                                   Updates form values
   ```

3. **Form Submission**:
   ```
   Validation → Check required fields → Submit if valid
                                     → Show errors if invalid
   ```

## Configuration Structure

### Saved Values Format
```javascript
{
    "attribute_key": {
        "Code": "shop_attribute_code",
        "Kind": "Matching|FreeText|AttributeValue",
        "Values": [
            {
                "Shop": {"Key": "shop_value"},
                "Marketplace": {"Key": "amazon_value"}
            }
        ]
    }
}
```

### Shop Attributes Structure
```javascript
{
    "group_name": {
        "optGroupClass": "class_name",
        "attribute_code": {
            "name": "Attribute Name",
            "type": "select|multiSelect|text|selectandtext",
            "values": {...} // Optional
        }
    }
}
```

### Marketplace Attributes Structure
```javascript
{
    "attribute_key": {
        "value": "Display Name",
        "required": true|false,
        "dataType": "text|select|multiselect",
        "values": {...}, // Amazon allowed values
        "desc": "Description"
    }
}
```

## Debugging Features

The implementation includes comprehensive console logging (currently active):
- Tracks addMatchingRow calls with parameters
- Shows button enable/disable decisions
- Displays value changes in selects
- Reports saved value processing

To disable logging, remove or comment out console.log statements.

## Browser Compatibility

- jQuery 1.8+ compatible
- Select2 integration
- Cross-browser event handling
- Graceful degradation for older browsers

## Recent Enhancements

### Completed Improvements
1. **Template-Based HTML Generation** (Completed)
   - Moved all HTML string concatenation to PHP templates
   - Created reusable templates for options, buttons, and forms
   - Improved code maintainability and separation of concerns

2. **Enhanced Attribute Handling** (Completed)
   - Added support for text-type Amazon attributes without predefined values
   - Implemented conditional enabling of "attribute_value" option
   - Added "Use shop values" checkboxes for text/selectandtext attributes

3. **Improved Event Handling** (Completed)
   - Removed setTimeout dependencies
   - Implemented immediate event triggering on element creation
   - Better initialization of dependent UI elements

4. **Translation System Integration** (Completed)
   - Using existing I18n translation keys
   - Proper multilingual support for all UI elements

### Future Improvements

1. Consider removing console.log statements in production
2. Add AJAX loading for large attribute value lists
3. Implement bulk value mapping features
4. Add undo/redo functionality for complex mappings
5. Consider using modern JavaScript (ES6+) with transpilation
6. Add unit tests for JavaScript components
7. Implement attribute validation before form submission

## Related Files

- Backend attribute handling classes
- I18n translation files in `/I18n/De/`, `/I18n/En/`, etc.
- Parent form controller classes
- Amazon API integration modules

## Maintenance Notes

When modifying this file:
1. Test with both saved and new attribute configurations
2. Verify minus button behavior in all scenarios
3. Check translation keys are properly defined
4. Test with large attribute lists for performance
5. Ensure Select2 initialization works correctly
6. Validate form submission with required fields