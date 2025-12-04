# Lazy Loading Implementation for Shop Attribute Values

## Overview

The ValueMatchingTable component now supports lazy loading of shop attribute values for optimal performance. Instead of loading all shop attribute values upfront, they are fetched only when a select-type shop attribute is chosen.

## Implementation

### 1. Frontend Integration

When using the AmazonVariationsNative component, provide a `onFetchShopAttributeValues` function:

```tsx
// Example implementation using existing apiEndpoint with ml[] parameters
const fetchShopAttributeValues = async (attributeCode: string): Promise<{ [key: string]: string }> => {
  try {
    // Use the existing apiEndpoint from the system
    const formData = new FormData();
    formData.append('ml[action]', 'getShopAttributeValues'); // Action parameter to identify the request
    formData.append('ml[attributeCode]', attributeCode);
    formData.append('ml[variationGroup]', currentVariationGroup); // Pass current context
    // Add any other required parameters from your system in ml[] format

    const response = await fetch(apiEndpoint, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();

    if (data.success) {
      return data.values; // Expected format: { "key1": "Label 1", "key2": "Label 2" }
    } else {
      throw new Error(data.message || 'Failed to load shop attribute values');
    }
  } catch (error) {
    console.error('Error fetching shop attribute values:', error);
    throw error;
  }
};

// Complete frontend integration example
const MyAmazonVariationsComponent = () => {
  // Your existing state and props
  const [variationGroup, setVariationGroup] = useState('SHIRT');
  const [attributeValues, setAttributeValues] = useState({});

  // Implementation of the fetch function
  const handleFetchShopAttributeValues = async (attributeCode: string) => {
    console.log('Fetching values for attribute:', attributeCode);

    const formData = new FormData();
    formData.append('ml[action]', 'getShopAttributeValues');
    formData.append('ml[attributeCode]', attributeCode);
    formData.append('ml[variationGroup]', variationGroup);

    try {
      const response = await fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        console.log('Loaded values:', data.values);
        return data.values;
      } else {
        throw new Error(data.message || 'Failed to load values');
      }
    } catch (error) {
      console.error('Error:', error);
      throw error;
    }
  };

  return (
    <AmazonVariationsNative
      variationGroup={variationGroup}
      customIdentifier="product-1"
      shopAttributes={shopAttributes}
      marketplaceAttributes={marketplaceAttributes}
      savedValues={attributeValues}
      i18n={i18nStrings}
      onValuesChange={setAttributeValues}
      onFetchShopAttributeValues={handleFetchShopAttributeValues}
    />
  );
};
```

### 2. Backend Implementation

Add support for the `getShopAttributeValues` action in your existing API endpoint:

```php
// In your existing API endpoint handler
<?php
// Add this case to your existing action handler
if ($_POST['ml']['action'] === 'getShopAttributeValues') {
    // Validate request
    if (!isset($_POST['ml']['attributeCode'])) {
        echo json_encode(['success' => false, 'message' => 'Missing attributeCode']);
        exit;
    }

    $attributeCode = $_POST['ml']['attributeCode'];
    $variationGroup = $_POST['ml']['variationGroup'] ?? null;

    try {
        // Your existing shop attribute loading logic
        $shopAttributeValues = [];

        // Example: Load from database or shop system
        if ($attributeCode === 'sp_Supplier') {
            // Load suppliers from your shop system
            $suppliers = getSuppliers(); // Your implementation
            foreach ($suppliers as $id => $name) {
                $shopAttributeValues[$id] = $name;
            }
        } elseif ($attributeCode === 'a_productstatus') {
            // Load product statuses
            $statuses = getProductStatuses(); // Your implementation
            foreach ($statuses as $id => $name) {
                $shopAttributeValues[$id] = $name;
            }
        }
        // Add more attribute types as needed...

        echo json_encode([
            'success' => true,
            'values' => $shopAttributeValues
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error loading shop attribute values: ' . $e->getMessage()
        ]);
    }
    exit; // Important: exit after handling this action
}

// Your existing action handlers continue here...
?>
```

### 3. User Experience Flow

1. **Initial Load**: Only shop attribute metadata (names and types) are loaded
2. **Select Attribute**: User selects a shop attribute with `type: "select"`
3. **Loading State**: "Loading shop attribute values..." message appears
4. **AJAX Request**: System fetches values for the specific attribute
5. **Display Table**: Value matching table appears with loaded values
6. **Error Handling**: If loading fails, error message is shown

### 4. Loading States

The component automatically handles these states:

- **Loading**: Shows "Loading shop attribute values..."
- **Success**: Displays the value matching table with fetched values
- **Error**: Shows error message with retry option
- **No Values**: Shows informative message if attribute has no values

### 5. Performance Benefits

- **Faster Initial Load**: No need to transfer all shop attribute values upfront
- **Reduced Memory Usage**: Only load values when needed
- **Better UX**: Progressive disclosure of complexity
- **Scalable**: Works with thousands of attribute values

### 6. Error Scenarios

The implementation handles:

- **Network errors**: Connection issues, timeouts
- **Server errors**: 500 errors, invalid responses
- **Validation errors**: Missing parameters, invalid data
- **Empty results**: When attribute has no values configured

### 7. Integration Notes

- **Backward Compatibility**: If no `onFetchShopAttributeValues` is provided, component works with pre-loaded values
- **Caching**: Frontend automatically caches loaded values per session
- **Security**: Always validate attribute codes on backend to prevent data access issues
- **Performance**: Consider implementing server-side caching for frequently accessed values

## Testing & Debugging

### Frontend Testing

To test the lazy loading:

1. **Browser Console**: Open Developer Tools and watch console logs
2. **Select Attribute**: Choose a shop attribute with `type: "select"` (like "Hersteller" or "Status")
3. **Watch Network**: Check Network tab for the AJAX request with `ml[action]=getShopAttributeValues`
4. **Verify Response**: Confirm the response contains `success: true` and `values` object

### Console Output

You should see detailed logs like:
```
[AttributeRow brand__value] RENDERING ValueMatchingTable!
[ValueMatchingTable] Loading shop attribute values for: sp_Supplier
Fetching values for attribute: sp_Supplier
[ValueMatchingTable] Loaded shop values: { "1": "Supplier A", "2": "Supplier B" }
```

### Network Request

The AJAX request should look like:
```
POST [your-api-endpoint]
Content-Type: multipart/form-data

ml[action]: getShopAttributeValues
ml[attributeCode]: sp_Supplier
ml[variationGroup]: SHIRT
```

### Response Format

Your backend should return:
```json
{
  "success": true,
  "values": {
    "supplier_1": "Acme Corp",
    "supplier_2": "Best Products Ltd",
    "supplier_3": "Global Supplies Inc"
  }
}
```

### Error Testing

Test error scenarios:
1. **Network Error**: Disconnect internet, should show error message
2. **Server Error**: Return 500 error, should show error message
3. **Invalid Response**: Return malformed JSON, should show error message
4. **Empty Values**: Return empty values object, should show "no values" message

### Debugging Tips

1. **Check Parameters**: Verify `ml[]` parameters are correctly formatted
2. **Backend Logs**: Add logging to your PHP endpoint to see received parameters
3. **Response Format**: Ensure your response exactly matches the expected JSON format
4. **CORS Issues**: Make sure your API endpoint allows the request
5. **Authentication**: Include any required authentication headers/cookies