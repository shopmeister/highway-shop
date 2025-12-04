<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/**
 * React-based Amazon Variations Component Integration
 *
 * @var ML_Form_Controller_Widget_Form_PrepareWithVariationMatchingAbstract|ML_Form_Controller_Widget_Form_VariationsAbstract $this
 * @var array $aField
 */

if (!class_exists('ML', false)) throw new Exception();

$marketplaceName = MLModule::gi()->getMarketPlaceName();
$variationGroup = $this->getVariationGroup();
$sCustomIdentifier = $this->getCustomIdentifier();
$i18n = $this->getFormArray('aI18n');

// Get variation_theme from prepare table (structure: {"SIZE/COLOR": []})
$sVariationThemeCode = null;
$savedVariationThemes = $this->getRequestField('variationthemecode');
if (!empty($savedVariationThemes)) {
    $savedVariationTheme = is_array($savedVariationThemes) ? array_pop($savedVariationThemes) : null;

    // Decode JSON string to array (database stores as JSON string)
    if (is_string($savedVariationTheme)) {
        $savedVariationTheme = json_decode($savedVariationTheme, true);
    }

    if (is_array($savedVariationTheme) && !empty($savedVariationTheme)) {
        $sVariationThemeCode = key($savedVariationTheme); // Extract the key (e.g., "SIZE/COLOR")
    }
}

if (empty($variationGroup) || $variationGroup === 'none' || $variationGroup === 'new') {
    return;
}

// Get shop attributes
list($aShopCustomAttributes, $aShopAttributes) = $this->initializeShopAttributeSelections($this->getShopAttributes());

// Get Amazon marketplace attributes
$aMPAttributes = $this->getMPVariationAttributes($variationGroup);

// Get saved attribute values
$aSavedValues = $this->getAttributeValues($variationGroup, $sCustomIdentifier);

// Get conditional rules (if available from backend)
$aCategoryDetails = $this->callGetCategoryDetails($variationGroup);
$aConditionalRules = isset($aCategoryDetails['DATA']['conditional_rules']) ? $aCategoryDetails['DATA']['conditional_rules'] : [];

// Prepare React component props
$reactProps = array(
    'variationGroup' => $variationGroup,
    'customIdentifier' => $sCustomIdentifier,
    'variationTheme' => $sVariationThemeCode, // Pass variation theme code to React (e.g., "SIZE/COLOR")
    'marketplaceName' => $marketplaceName,
    'shopAttributes' => $aShopAttributes,
    'marketplaceAttributes' => $aMPAttributes,
    'savedValues' => $aSavedValues,
    'conditionalRules' => $aConditionalRules, // Pass conditional rules to React
    'neededFormFields' => MLHttp::gi()->getNeededFormFields(), // Platform-specific form fields (e.g., Magento form_key)
    'debugMode' => MLSetting::gi()->blDebug, // Enable debug mode when MLSetting debug is active
    'i18n' => array(
        'dontUse' => MLI18n::gi()->get('dont_use', "Don't use"),
        'webShopAttribute' => MLI18n::gi()->get('attributes_matching_web_shop_attribute'),
        'pleaseSelect' => MLI18n::gi()->get('form_select_option_firstoption'),
        'enterFreetext' => MLI18n::gi()->get('enterFreetext', 'Enter custom value'),
        'useAttributeValue' => MLI18n::gi()->get('use_attribute_value'),
        'selectAmazonValue' => MLI18n::gi()->get('form_select_option_firstoption'),
        'enterAmazonValue' => MLI18n::gi()->get('enter_amazon_value', 'Enter Amazon value'),
        'useShopValues' => MLI18n::gi()->get('use_shop_values', 'Use shop values'),
        'shopValue' => MLI18n::gi()->get('shop_value', 'Shop Value'),
        'marketplaceValue' => MLI18n::gi()->get('marketplace_value', 'Marketplace Value'),
        'autoMatching' => MLI18n::gi()->get('auto_matching', 'Auto Matching'),
        'manualMatching' => MLI18n::gi()->get('manual_matching', 'Manual Matching'),
        'requiredAttributesTitle'   => MLI18n::gi()->get('attributes_matching_required_attributes'),
        'attributesMatchingTitle'   => ' ',
        'optionalAttributesTitle'   => MLI18n::gi()->get('attributes_matching_optional_attributes'),
        'optionalAttributeMatching' => ' ',
        'mandatoryFieldsInfo' => MLI18n::gi()->get($marketplaceName . '_prepare_variations_mandatory_fields_info'),
        'requiredField' => MLI18n::gi()->get('required_field'),
        'fixErrors' => MLI18n::gi()->get('fix_errors'),
        'additionalOptions' => MLI18n::gi()->get('additional_options'),
        'freetext' => MLI18n::gi()->get('freetext', 'Enter custom value'),
        'submitButton' => MLI18n::gi()->get('submit', 'Save'),
        'loading' => MLI18n::gi()->get('loading', 'Loading...'),
        'saveSuccess'               => MLI18n::gi()->get('saveSuccess'),
        'saveFailed' => MLI18n::gi()->get('save_failed', 'Save failed'),
        'validationFailed' => MLI18n::gi()->get('validation_failed', 'Validation failed'),
        'clearAllMatchings' => MLI18n::gi()->get('clear_all_matchings'),
        'makeCustomEntry' => MLI18n::gi()->get('make_custom_entry'),
        'enterCustomAmazonValue' => MLI18n::gi()->get('enter_custom_amazon_value'),
        'useShopValuesCheckbox' => MLI18n::gi()->get('AttributeMatching_AutoMatching_UseShopValue'),
        'useShopValuesDescription' => MLI18n::gi()->get('useShopValuesDescription'),
        'addOptionalAttribute' => MLI18n::gi()->get('addOptionalAttribute'),
        'selectOptionalAttribute' => MLI18n::gi()->get('selectOptionalAttribute'),
        'valueMatchingDescription'  => MLI18n::gi()->get('valueMatchingDescription'),
        'autoMatchResults'          => MLI18n::gi()->get('autoMatchResults'),
        'exactMatches'              => MLI18n::gi()->get('exactMatches'),
        'noMatches'                 => MLI18n::gi()->get('noMatches'),
        'valueMatchingTitle'        => MLI18n::gi()->get('valueMatchingTitle'),
        'conditionalRulesAffectedBy' => MLI18n::gi()->get('conditionalRulesAffectedBy'),
        'conditionalRulesAffects'    => MLI18n::gi()->get('conditionalRulesAffects')
    )
);
// Generate unique component ID
$componentId = 'amazon-variations-' . md5($variationGroup . $sCustomIdentifier);




?>

<!-- React Component Container -->
<div id="<?php echo $componentId; ?>" class="amazon-variations-react-container">
    <!-- Fallback content while React loads -->
    <div class="loading-placeholder">
        <p><?php echo MLI18n::gi()->get('loading', 'Loading...'); ?></p>
    </div>
</div>

<!-- Initialize React Component -->
<script>
    // Global configuration for the React component
    window.magnalisterReactConfig = window.magnalisterReactConfig || {};
    window.magnalisterReactConfig.amazonVariations = {
        componentId: '<?php echo $componentId; ?>',
        props: <?php echo json_encode($reactProps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
        apiEndpoint: '<?php echo MLHttp::gi()->getCurrentUrl(); ?>',
        formSelector: '.magnalisterForm'
    };

    // Initialize React component when DOM is ready and script is loaded
    function initializeAmazonVariationsReact() {
        var config = window.magnalisterReactConfig.amazonVariations;
        var container = document.getElementById(config.componentId);

        if (!container) {
            console.error('Amazon Variations React container not found:', config.componentId);
            return;
        }

        if (typeof window.MagnalisterAmazonVariations === 'undefined') {
            console.log('MagnalisterAmazonVariations not loaded yet, retrying...');
            setTimeout(initializeAmazonVariationsReact, 100);
            return;
        }

        // Check if React and ReactDOM are available after bundle loads
        if (typeof window.React === 'undefined' || typeof window.ReactDOM === 'undefined') {
            console.log('React/ReactDOM not available globally yet, retrying...');
            setTimeout(initializeAmazonVariationsReact, 100);
            return;
        }

        try {
            console.log('Initializing Amazon Variations React component...');
            console.log('Container found:', container);
            console.log('Component available:', typeof window.MagnalisterAmazonVariations.AmazonVariations);
            console.log('React available:', typeof window.React);
            console.log('ReactDOM available:', typeof window.ReactDOM);
            console.log('ReactDOM.createRoot available:', typeof window.ReactDOM.createRoot);
            console.log('ReactDOM.render available:', typeof window.ReactDOM.render);

            // Enhanced props with event handlers
            var enhancedProps = Object.assign({}, config.props, {
                onValuesChange: function(values) {
                    // Trigger custom event for other components to listen to
                    var event = new CustomEvent('amazon-variations-changed', { detail: { values: values } });
                    document.dispatchEvent(event);
                },

                onValidationError: function(errors) {
                    // Store validation errors for form submission
                    window.amazonVariationsValidationErrors = errors;

                    // Trigger validation event
                    var event = new CustomEvent('amazon-variations-validation', { detail: { errors: errors } });
                    document.dispatchEvent(event);
                },

                // API endpoint configuration for React components to make their own AJAX calls
                apiEndpoint: config.apiEndpoint
            });

            // Render the React component
            var AmazonVariations = window.MagnalisterAmazonVariations.AmazonVariations;
            
            if (window.ReactDOM && window.ReactDOM.createRoot) {
                // React 18 way
                console.log('Using React 18 createRoot');
                var root = window.ReactDOM.createRoot(container);
                root.render(window.React.createElement(AmazonVariations, enhancedProps));
            } else if (window.ReactDOM && window.ReactDOM.render) {
                // React 17 fallback
                console.log('Using React 17 render');
                window.ReactDOM.render(
                    window.React.createElement(AmazonVariations, enhancedProps),
                    container
                );
            } else {
                console.error('ReactDOM not available or render methods not found');
                console.log('Available ReactDOM methods:', Object.keys(window.ReactDOM || {}));
                return;
            }

            console.log('Amazon Variations React component initialized successfully');

        } catch (error) {
            console.error('Failed to initialize Amazon Variations React component:', error);
            // Show fallback message
            container.innerHTML = '<div class="error-message" style="padding: 20px; border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24; border-radius: 4px;">' +
                '<strong>Error:</strong> Failed to load the variations component. Please refresh the page or contact support if the problem persists.' +
                '</div>';
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAmazonVariationsReact);
    } else {
        initializeAmazonVariationsReact();
    }

    // Intercept submitSerializedForm to check validation BEFORE submission
    // Note: React save is handled by magnalister.prepareform.recursive.ajax.js
    // This wrapper only handles validation errors for the React component
    (function () {
        if (typeof mlSerializer === 'undefined' || !mlSerializer.submitSerializedForm) {
            console.warn('[Amazon Variations] mlSerializer not available, validation interception disabled');
            return;
        }

        // Store original function
        var originalSubmitSerializedForm = mlSerializer.submitSerializedForm;

        // Override with validation check only
        mlSerializer.submitSerializedForm = function (form, aExtraData, $btn) {
            // Check if React component exists
            var hasReactComponent = typeof window.magnalisterReactConfig !== 'undefined'
                && typeof window.magnalisterReactConfig.amazonVariations !== 'undefined';

            if (hasReactComponent) {
                // Check for validation errors
                if (typeof window.amazonVariationsValidationErrors !== 'undefined' &&
                    window.amazonVariationsValidationErrors.length > 0) {
                    console.log('[Amazon Variations] Validation errors found, blocking submit and scrolling to error');

                    // Trigger React to scroll to first error
                    var scrollEvent = new CustomEvent('amazon-variations-scroll-to-error');
                    document.dispatchEvent(scrollEvent);

                    // Block submission completely
                    return false;
                }
            }

            // No errors or no React component - call original function
            return originalSubmitSerializedForm.call(this, form, aExtraData, $btn);
        };

        console.log('[Amazon Variations] submitSerializedForm validation wrapper installed');
    })();
</script>


