# v3.0.11
- The first version of the magnalister plugin for Shopware 6 in the community store

# v3.0.12
- New directory for magnalister plugin

# v3.0.13
- Displaying marketplace logos for magnalister order in Shopware 6 order-overview

# v3.0.14
- Fixed: eBay - Order Import - A problem to import order when "Only paid order" is deactivated

# v3.0.15
- Compatibility with Shopware 6.4

# v3.0.16
- Rakuten FR / PriceMinister - Configuration - New mandatory field to specify "Shipping from country" due to VAT changes from July 1.
- AttributesMatching - Problems with special chars like "&amp;" in attributes fixed
- Image Generator - Fix for PHP 8

# v3.0.17
- Metro Invoice Upload
- Kaufland.de Invoice Upload
- Amazon ERP Invoice Upload
- Attributes Matching: After clicking + or - it is reloading the page and move to last position in the form
- Etsy: New Tracking Code Matching in Marketplace Configuration

# v3.0.18
- Shopware 6.4: 3rd Party Plugins Compatibility "NetSwitcher"
- Amazon - Fix B2B Configuration and Product Preparation errors when configured incorrectly
- Kaufland.de - Removed price field from product preparation
- OTTO - Configuration: Show at VAT Matching "Please Choose" if a value is not matched

# v3.0.19
- Shopware 6 Attributes: Sort by Position and Name
- Shopware 6: Remove debugging code (regarding MySQL)
- Attributes Matching Tab: Category dropdown now shows the last matched categories
- eBay Product Preparation: Speed up load of product preparation for already used categories

# v3.0.20
- Shopware 6 - Order Import: Fix where PhoneNumber is empty
- Idealo - Product Preparation and Product Upload - Fix Upload of prepared Item Title and add Option "Use always from Webshop"
- Check24 - Product Upload: New Feed Fields

# v3.0.21
- Shopware 6 - Order Import - Payment Status: Always use configured Payment Status from marketplace configuration
- Shopware 6 - Use SessionID from PHP session_id() function because CSRF token could change per request, like during AJAX calls
- Amazon - Order Import: Import of VAT ID if provided from Amazon

# v3.0.22
- Shopware 6 - Product Preparation : Fixed variations with empty pictures
- Hood.de - JSON Encoding Error PHP 7.4+ Compatibility
- Kaufland.de - Short Description: As alternative, we use now meta keywords if short description is empty in shop
- Rookie Plan Added

# v3.0.23
- Etsy - Inventory View - A problem to show prices in inventory is fixed
- Shopware 6 - Processing cover images of veriants as first images of the variant

# v3.0.24
- eBay - Importing, updating and merging Order - Preventing to import repetitive addresses
- eBay - Importing, updating and merging Order - Preventing to change delivery status by updating order
- eBay - Importing, updating and merging Order - Show shipping cost properly
- eBay - Importing, updating and merging Order - Fix a problem to update order payment status

# v3.0.25
- Shopware 6.3 - Release new fixes for Shopware 6.3

# v3.0.26
- Shopware 6 - Improvement by importing order by solving the conflict of SQL transactions

# v3.0.27
- Shopware 6 - Solve a problem with PHP 8

# v3.0.28
- eBay - Order Import: Added feature to avoid shipping notifications to eBay buyers
- eBay - Item Condition Description: It's now possible to add a text description of the item condition (up to 1000 characters) in the item preparation form
- Shopware 5: PHP 8 warning "Private function cannot be final" is solved
- Amazon - FBA Business OrderView: Logo added

# v3.0.29
- All Marketplaces - Product-List - Marketplace Status Filter: Improvement by getting inventory data, resuming the broken process
- OTTO: Prevent to displaying removed categories of OTTO in magnalister plugin

# v3.0.30
- All Marketplace: Fix the problem by using "If the product status is inactive, the stock will be set to 0" in global configuration by synchronizing of variants stock

# v3.0.31
- Shopware 6.3 - Release new fixes for Shopware 6.3

# v3.0.32
- Global Configuration: "Configuration couldn't be open to correct the wrong passphrase" is fixed, (Problem from v3.0.22)
- Check24 - Product Preparation: Prevent SQL error by saving multi product preparation

# v3.0.33
- All Marketplace - Product Upload - Price Synchronization: Supporting advanced pricing by submitting product price to marketplaces
- All Marketplace - Product List: Fix the problem of long loading of the product list caused by retrieving HTTP images
- All Marketplace - Attribute Matching: Display custom field with technical name for better distinction between custom field with same label
- Amazon - Buy Shipping Label - Filters: New Filter Options for Merchant Fulfillment Channel "SameDay, NextDay, SecondDay"
- Upload ERP Invoice: Additional Support for ".PDF" instead of only ".pdf"
- Optimization of magnalister database table performance for orders processes

# v3.0.34
- Cdiscount - Product Upload: Prevent to separate variants of a product
- All marketplaces - Order import: Fix a problem if existing product is not related to the sales channel
- Amazon - Preparation: Now it is possible to save "0" as EAN for a product
- All marketplaces - Order Synchronization: Correction by submitting the shipping date of orders to marketplaces

# v3.0.35
- Shopware 6.3 - Release new fixes for Shopware 6.3

# v3.0.36
- OTTO - Synchronization of order status: If multiple return keys (comma or semicolon separated) are specified, we will only submit the first one

# v3.0.37
- OTTO - Order Configuration: Add payment status configuration field

# v3.0.38
- Order Import - Products: Set correct "position" in database for each ordered product
- Order Status Synchronization: Fixed issue when Shopware OrderNumber is overwritten by individual programming (Order Number equals Marketplace Order Id)
- Amazon US - Order Status Synchronization: Fix a problem with time zone difference
- Image processing: Fix a problem to find images size in special cases

# v3.0.39
- Order Import: Trigger "Order Placed" event by order import
- Order Import - Products: Some inconsistencies in the database were corrected
- All Marketplaces - Order Cancel Status Configuration: Preselect the best status for cancellation, depends on the shop-system
- Image Processing: Handling image url with spaces and solved a problem by normalizing image url

# v3.0.40
- All Marketplaces - Order Import: Skip "Placed Order" event if there are some errors to execute flow of it
- All Marketplaces - Order Status Synchronization: Prevent the problem by getting shop order data
- Check24 - Preparation, Product Upload: Fix the problem with comma separated shipping cost
- Shopware 6.4.7: The compatibility issue is fixed

# v3.0.41
- Product Preparation - Attributes Matching: Dropdown search support
- Amazon - Product Preparation - Browse Nodes: Dropdown search support
- Errorlog: Sorting now by date descending by default
- PHP 8 Compatibility Fixes

# v3.0.42
- OTTO - Attributes Matching: CSS fixes
- Kaufland.de - Order Status Synchronization: A problem by sending shipping method as carrier is fixed

# v3.0.43
- Amazon - Import FBA orders: Added option to ignore FBA orders on import
- Kaufland.de - Price Synchronization: Minimum Price Feature - Disable Option "Use from Marketplace"
- Kaufland.de - Attributes Matching: Dropdown doesn't contain any values fix

# v3.0.44
- METRO Offer API v2 - Migration to the new API endpoint
- METRO: Submission of net price instead of gross price for products
- METRO: METRO Spain is now supported
- METRO: Min and max delivery time can now be entered

# v3.0.45
- Order Import: Fix some problems by executing "order.placed" flows
- Order Import: Import marketplace order date as order date in Shopware order

# v3.0.46
- Product Upload: Fix a problem by sending variant-product description

# v3.0.47
- Invoice Upload: Process 10 orders per Request - sometimes pdf files are so big that server post size exceeds
- Marketplace configuration - Check if the same order status is used multiple times - Example: For import and shipping
- METRO - Product Upload: Add missing required "max processing time" to configuration and product preparation

# v3.0.48
- Order Import: Fix a problem to get product tax by shipping address
- Order Import: Fix a problem if some flow rules are not properly saved in Shopware
- Product-List: Optimizing loading and selection of products

# v3.0.49
- All Marketplaces - Order Import: Fix a problem to import order-item without SKU from marketplaces

# v3.0.50
- All Marketplaces - Order Import: Fix a problem to import if marketplace order-address-name is longer than allowed

# v3.0.51
- All new changes for Shopware 6.3

# v3.0.52
- METRO Price Synchronization: Since METRO uses net prices the price markup or markdown from marketplace configuration was not applied
- OTTO - Product Upload: Fixed an issue where the product upload was stopped because a product has no price in the webshop
- Invoice Upload: Document type selection added in marketplace configuration

# v3.0.53
- All Marketplace - Order Import not working fix

# v3.0.54
- All Marketplace - Order Import: Fix the problem with importing wrong tax amount
- Productlist - Filter: Fix a rare problem by marketplace status filter

# v3.0.55
- All new changes for Shopware 6.3

# v3.0.56
- Shopware 6.3 - All Marketplace: Fix the problem of long loading and broken page by "Error-Log" and "Inventory-View"

# v3.0.57
- Shopware 6.4 - All Marketplace: Fix the problem of long loading and broken page by "Error-Log" and "Inventory-View"

# v3.0.58
- Cdiscount - Product Upload / Configuration: New Shipping Methods available

# v3.0.59
- Hood.de - Product Preparation: Fix a problem to uncheck all features in preparation form
- Hood.de - Translations in English: Update missing or wrong translations
- METRO - Product Upload - Max Processing Time: Removed option "0" because max processing time should be at least 1
- Idealo - Order Import: Import payment method from Idealo by default
- Kaufland.de - Submit "Weight" Shop-Attribute as Unit and Value instead of just the value
- Order Import: Second additional address field does not need to be filled with the Packstation customer ID if the first field is already filled with the Packstation customer ID

# v3.0.60
- eBay - Preparation: The error "Incorrect integer value: '' for column \`magnalister_ebay_prepare\`.\`ShippingLocalDiscount\` at row 1" is fixed
- Order Import - Fix a problem by inserting magnalister order data in to database with some special settings

# v3.0.61
- eBay - Product Template: Use custom field placeholder instead of free text field
- Amazon - Order Config - Duplicate Fields: Only 2 fields creatable bug fix
- Shopware 6 - Invoice Configuration: Fix a rare problem to load document types

# v3.0.62
- eBay, Hood, Cdiscount: Fixed Bug where shipping costs are not saved
- eBay Product Preparation: Fixed Error: "Field 'SecondaryCategoryName' doesn't have a default value (1364)"
- Order Import: New approach and global configuration to skip Shopware 6 "Flows" due to performance issues

# v3.0.63
- PriceMinister - Upload product: Changing the product title length to 200 characters and description to now a maximum of 4000 characters
- Amazon - Product Upload: Fixed problem where variations were not uploaded correctly
- Idealo - Price Comparison Marketplace (like Idealo) - Product upload: Product URL was wrong for variations
- eBay - Configuration - Refunds: Fixed issue where refund settings could not be saved correctly
- Shopware 6.4 and higher - Product prices: Fixed an issue where the wrong price of a currency was synced or uploaded

# v3.0.64
- Order Import - Salutation: If default salutations are removed or renamed, we are using now some fallbacks
- Order Import - Duplicate Addresses for customer: Fixed bug that multiple customer addresses have been imported even if the same address details were already in the shop

# v3.0.65
- Custom fields selection for uploading invoice number to Amazon, METRO and Kaufland.de is now available
- Custom fields selection for sending shipping method, carrier and ... to Amazon and OTTO is now available

# v3.0.66
- Amazon/eBay - Attribute Matching: Fix a problem to send matched own value
- Marketplace Configuration - Order Custom Field: Translations correction

# v3.0.67
- Idealo - Configuration: Disable forwarding fee fields, if shipping method is not forwarding
- eBay - Configuration / Product Preparation: HitCounter removed
- Attribute Matching: Fixed issue where not all matched attributes were saved
- Order Import: Fixed a problem with the language of the sales channel that prevented the creation of invoices
- Invoice Generation: Fixed a problem with the language of the sales channel that prevented the creation of invoices

# v3.0.68
- Order Import: Prevent to insert different customer number in customer and order_customer table of Shopware
- eBay - Product - Upload: Transferring weight of product by upload
- eBay "Synchronize External Items" checkbox removed
- Import Orders: In rare cases, orders were no longer imported (fixed)

# v3.0.69
- All Marketplaces - Show weight and dimensions extra unit attributes
- eBay - Configuration: Don't show any error if refund status configuration is same as another status configuration
- eBay - Preparation: Fix white page problem after saving preparation
- Hood.de - Inventory: Product links to hood.de fixed
- eBay - Preparation: A rare problem by loading a category attribute is fixed
- OTTO - Configuration: Fix PHP notices by new configuration

# v3.0.70
- All Marketplaces - Product Preparation: Only jpg, jpeg, png and gifs supported as product images
- All Marketplaces - Image Processing: Supporting to get product image stored in external media server (e.g. Amazon S3)

# v3.0.71
- All Marketplaces - Price Calculation: Fix a problem with the calculation of the price in a currency other than the default currency

# v3.0.72
- All Marketplaces - Images Processing: Fix the error when "exif_imagetype"-function doesn't exist
- Amazon - Configuration - Credentials: Field MarketplaceId is removed and will be set by us based on given Amazon Site
- Cdiscount - Order Status Synchronization: Submission of Carrier and Carrier Matching in Configuration added
- eBay Order Import: Buyer's Message
- Hood - Product Preparation: The problem with using "=GEWICHT" placeholder as shipping cost for foreign countries is fixed

# 3.0.73
- All Marketplaces - Order Import: Internal total product price data at order fixed

# 3.0.74
- Amazon new Authorization Flow (especially for Amazon SP-API Migration)

# 3.0.75
- All Marketplaces - Product List: Remove repetitive time from "Prepared on" filter
- Amazon - Quantity - Product Upload / Synchronization: Added expert option "Limitation for Number of Items"
- Amazon - Shipping Label: Show list of orders very faster than before
- Hood.de - Synchronization - Config "Transfer variations" deactivated: Fix that it is still possible to synchronize every single variation with single products on Hood.de
- METRO: Solving GTIN Restriction (Product without GTIN can be uploaded also with Manufacture and MPN)

# 3.0.76
- Etsy: Fixing a problem where you got stuck in the configuration

# 3.0.77
- eBay - Product Preparation - Product Upload: Use prepared title also for variations
- eBay - Product Preparation: Fix a problem by showing attribute by multi product preparation

# 3.0.78
- Invoice Upload: Fix a problem by getting correct invoice number from the shop
- Shopware - Product Preparation: Fix broken page due to .mp4 files
- Amazon - Order Import: Not defined shipping group options should not prevent the plugin from importing orders
- Amazon - Product Preparation: Fix a SQL error (Field 'AIdentID' doesn't have a default value (1364))
- Amazon - Product Matching: Only shipping templates are allowed from now on
- Amazon - Product Matching: Fix SQL Error "Field 'MainCategory' doesn't have a default value (1364)"
- Amazon - Product Upload: Matched products now show the configured and matched tax code
- eBay - Product Preparation: Fix a problem by preparing products with long eBay category name
- Hood.de - Configuration - Order Cancellation: Allow to not set a status for order cancellation (this option is now optional)
- METRO - Inventory View: Fix if no shipping costs are set in product preparation
- METRO - Product Preparation: Fix a problem by showing prepared shipping profile in product preparation

# 3.0.79
- Order Import: If the company name is provided by the marketplace, the type of customer account is now business
- Error message "Invalid character set was provided" fixed

# 3.0.80
- All Marketplaces - Order Import - Use maximum tax of product for shipping cost (Correction in some special cases)
- Amazon - Product Matching: Don't save the matching for a product that is selected to be not matched
- Amazon - Fixed the missing currency code
- eBay - Product Properties: Show product properties just like Shopware front end
- Etsy - Generating Shipping Template - Add required field and correction of messages
- Amazon - Shipping Label - Order List - Correct the problem to select orders from several pages

# 3.0.81
- Hood - Configuration - Order Status Synchronization - Fix problem to configure and synchronization of correct cancel status
- eBay - Preparation and Uploading Product - Fix a problem to prevent wrong replacement of prepared title
- Hood - Product Preparation: Prevent to show not available free text placeholder in product description

# 3.0.82
- General Performance: Improve session storage and deletion
- OTTO - Preparation - Fix a problem by brand matching

# 3.0.83
- PHP 8.1: Fixed some compatibility (not fully tested)

# 3.0.84
- Amazon - Configuration - Fix a broken configuration field

# 3.0.85
- Order Import: Street address field can not be empty - check for and set to "--" when empty
- Synchronization - PHP 8 Fix: If a SKU has problems and throws an exception, the entire synchronization process must not be interrupted

# 3.0.86
- METRO - Product Preparation: Fix issue with showing that manufacturer and mpn field are set as "always use from webshop"

# 3.0.87
- Amazon BOPIS - Inventory synchronization: Fixed issue with submitting store stock for unprepared products

# 3.0.88
- Amazon BOPIS - Inventory synchronization: Fixed issue with submitting store stock for non BOPIS products
- Amazon BOPIS - Refund order status synchronization: Fixed issue that refund could not be triggered via order status

# 3.0.89
- Hood - Synchronize order status: Fixed issue where orders are no longer synchronized as of version 3.0.88
- Statistics: Now show up to 14 months back
- Etsy: new values for Item Creation Time

# 3.0.90
- All Marketplaces - Product Preparation - Choose Marketplace Category - Fix the problem in the previous version
- eBay, Etsy - Request token button - Fix the problem in the previous version

# 3.0.91
- All Marketplaces - Order Import - Fix a problem by creating customer in Shopware 6.4.18.1

# 3.0.92
- All Marketplaces - Order Import - Use not specified gender if gender is not provided from the marketplace
- All Marketplaces - Inventory tab: Text "Price" to "Shop Price / Marketplace Price" corrected
- All Marketplaces - Product preparation and Upload: Error "Column 'MarketplaceIdentSku' cannot be null (1048)" fixed
- All Marketplaces - Product images: Resolve an issue by resizing and sending images with names that contain spaces
- Amazon "Click & Collect in store": Create and Update store concept improved
- Amazon "Click & Collect in store": Status of orders could not be changed in "Orders" tab because the submit button had an error
- eBay: Variation error for foreign languages corrected
- METRO - Support for volume prices
- idealo - Product preparation: Payment method is now a mandatory field

# 3.0.93
- OTTO and METRO - Product Preparation: Fixed issue that preparation could not be opened

# 3.0.94
- Shopware 6 - CSRF token issue fixed in AJAX mode
- All Marketplaces - Attributes Matching: No display of attributes after selecting the category is fixed
- All Marketplaces - Image Processing - Fix a problem of long loading by resizing images
- Global Configuration - Fix a rare PHP-Fatal error by first configuration
- eBay - Attributes Matching: PHP 8.1+ compatibility
- Etsy - Attribute Matching - Fix several problems to match different attributes
- Etsy - Attribute Matching - Fix a problem to match text field attribute
- METRO - Volume prices: Add markup or markdown setting for "Webshop" option

# 3.0.95
- Amazon - Prepare Product List - Fix problem with product list being displayed twice
- Image Processing - Supporting Webp images only

# 3.0.96
- Amazon - First Configuration - Fix the error message "There is a problem to get the currency of Amazon"

# 3.0.97
- Fix security issue by magnalister admin page

# 3.0.98
- Generic: Fixed problem when opening magnalister with IPV6 or using proxies
- All Marketplaces - Image processing - Fix a problem by generating images with some special servers configuration
- OTTO - Product preparation + upload: Problem with marketplace value "true" and "false" fixed + Support for "comma separated" numbers
- OTTO - Preparation - Prevent to fail by saving preparation because of missing image error

# 3.0.99
- eBay - Synchronise Order Status - Refund: Fix PHP error if refund status is not configured
- Etsy - Compatibility with the newest Etsy API
- Kaufland.de - Product deletion: Problem solved that after deleting a product a white page appears
- OTTO - Product upload: Support for uploading shop attributes with more than one value (e.g. Shopware properties)

# 3.1.00
- Check24 - Configuration: "Image size" option added
- Etsy: Problem with update stuck at about 95 percent is fixed
- OTTO - Product preparation: When preparing multiple products, optional category-independent attributes were not displayed

# 3.1.01
- All Marketplaces - Fix an issue by clicking on marketplace tabs when using PHP 8 - The issue occurs from the previous version of magnalister

# 3.1.02
- All Marketplaces - Image Processing - Fix the problem to show Shopware images in magnalister by wrong server configuration

# 3.1.03
- Database structure: Remove all ZERO DATES database structure options from magnalister
- All Marketplaces - Image processing - Fix problem with http image URLs automatically redirected to https URL
- All Marketplaces - Inventory View and Error-Log - Fix problem using delete button in French language
- Kaufland.de: Shipping Time becomes Handling Time

# 3.1.04
- Kaufland.de - Handling Time: More transparency by content

# 3.1.05
- Fix a problem by loading magnalister plugin if in storefront sales channel of Shopware shop a domain with a virtual
  URL is used
- All Marketplaces - Attribute matching - Adding scale unit and seller unit as separate attribute
- OTTO - Price Synchronization - Prevent re-sending price to OTTO if shop price has more than 2 decimals

# 3.1.06
- Fix the problem of loading the magnalister admin page if the Shopware admin user doesn't have a language in the user
  profile.
- Fix the problem of loading the magnalister admin page if some sales channel of Shopware is in maintenance mode

# 3.1.07
- All marketplaces - Fix an issue in version 3.1.06 to show the magnalister admin page in the language of the user
  profile.

# 3.1.08
- Fix a problem with loading magnalister plugin when a sales channel of Shopware shop has different similar domain URL
- Etsy - Possibility to cancel preparation partially (e.g. Reset "When made")

# 3.1.09
- Plugin Update: Fixed a problem where the update was stuck at about 92 percent
- eBay - Configuration - Product Upload: New configuration to always show "Incl. VAT"
- Kaufland.de: Shipping Time removed
- OTTO - Product Upload: Sending not variant image of master product at the of variants images

# 3.1.10
- METRO - Supporting new shipping and origin countries (ES, IT, PT, NL, FR)
- Hood - Product Preparation - Supporting Item name length up to 85 Characters

# 3.1.11
- Compatibility with Shopware 6.5

# 3.1.12
- Amazon - Fix the problem with "Truncated incorrect INTEGER value: '1 - 2 working days'"
- eBay - Product Upload - Fix problem to show always tax included for single product

# 3.1.13
- Shopware 6.5.X - Fix a problem to load magnalister plugin for shops with sale channels with http- and https-domain url
  for same host

# 3.1.14
Shopware 6.4 specific changes:
- Amazon - Manual Matching: Fix a problem to display variation detail in manual matching
- Kaufland - Error: Truncated incorrect INTEGER value: 'a ' fixed

# 3.1.15
Shopware 6.5 specific changes:
- Amazon - Manual Matching: Fix a problem to display variation detail in manual matching
- Kaufland - Error: Truncated incorrect INTEGER value: 'a ' fixed

# 3.1.16
Shopware 6.4 specific changes:
- Update sometimes stuck at 94.76% if the customer has Etsy
- Shopware 6 - Base price: Problem solved where only the base price value was filled in, but no unit (base price is then not valid)
- Amazon - Product Matching: Fix issue with "1 to 2 Workings Days" could not be saved and uploaded
- Amazon - Product Matching: When opening the old preparation, preselect the corresponding ASIN to a searched product
- METRO - Product Preparation: Fixed error "Data truncated for column BusinessModel"
- OTTO - Order Status Synchronization: Inclusion of the time zone in the shipping date when confirming the shipment to OTTO

# 3.1.17
Shopware 6.5 specific changes:
- Update sometimes stuck at 94.76% if the customer has Etsy
- Shopware 6 - Base price: Problem solved where only the base price value was filled in, but no unit (base price is then not valid)
- Amazon - Product Matching: Fix issue with "1 to 2 Workings Days" could not be saved and uploaded
- Amazon - Product Matching: When opening the old preparation, preselect the corresponding ASIN to a
  searched product
- METRO - Product Preparation: Fixed error "Data truncated for column BusinessModel"
- OTTO - Order Status Synchronization: Inclusion of the time zone in the shipping date when confirming the shipment to OTTO

# 3.1.18
Shopware 6.4 specific changes:
- Amazon - Import Order: Settings for promotions are no longer under expert settings
- Fixing a problem caused by partial use of https or http url by magnalister plugin (Docker hosted Shopware 6)

# 3.1.19
Shopware 6.5 specific changes:
- Amazon - Import Order: Settings for promotions are no longer under expert settings
- Fixing a problem caused by partial use of https or http url by magnalister plugin (Docker hosted Shopware 6)

# 3.1.20
Shopware 6.4 specific changes:
- Fix for that some marketplace tabs could not be opened, due to missing required configuration settings
- Amazon - Import Order: Settings for promotions are no longer under expert settings
- METRO: French translations

# 3.1.21
Shopware 6.5 specific changes:
- Fix for that some marketplace tabs could not be opened, due to missing required configuration settings
- Amazon - Import Order: Settings for promotions are no longer under expert settings
- METRO: French translations

# 3.1.22
Shopware 6.4 specific changes:
- General - Fix a problem by displaying magnalister plugin if an url is set for headless and active sales-channel
- Order Import: Fixed issue with order item position in database
- Amazon - Product Matching: Fixed issue where manual matching was not possible
- Amazon B2B Configuration: Price and quantity tiers are no longer prefilled

# 3.1.23
Shopware 6.5 specific changes:
- General - Fix a problem by displaying magnalister plugin if an url is set for headless and active sales-channel
- Order Import: Fixed issue with order item position in database
- Amazon - Product Matching: Fixed issue where manual matching was not possible
- Amazon B2B Configuration: Price and quantity tiers are no longer prefilled

# 3.1.24
Shopware 6.4 specific changes:
- Order Import - Fix a problem of duplicated orders by Shopware shop which hosted in different physically
  server

# 3.1.25
Shopware 6.5 specific changes:
- Order Import - Fix a problem of duplicated orders by Shopware shop which hosted in different physically
  server

# 3.1.26
Shopware 6.4 specific changes:
- Etsy - Product Upload - Fix the problem with "invalid property_id"
- Kaufland.de: Shipping Groups

# 3.1.27
Shopware 6.5 specific changes:
- Etsy - Product Upload - Fix the problem with "invalid property_id"
- Kaufland.de: Shipping Groups

# 3.1.28
Shopware 6.4 specific changes:
- All Marketplace - Order Import - Use dash "-" if postcode in order address is empty
- eBay - Preparation - Fix a problem to prepare a product if master product price is zero

# 3.1.29
Shopware 6.5 specific changes:
- All Marketplace - Order Import - Use dash "-" if postcode in order address is empty
- eBay - Preparation - Fix a problem to prepare a product if master product price is zero

# 3.1.30
Shopware 6.4 specific changes:
- Database Update got stuck at 89.19% fix
- Performance improved in Attributes Matching (incl. product preparation)
- Amazon - Product Upload: Sending generic prepared product-image with variant images
- Idealo Campaign Link

# 3.1.31
Shopware 6.5 specific changes:
- Database Update got stuck at 89.19% fix
- Performance improved in Attributes Matching (incl. product preparation)
- Amazon - Product Upload: Sending generic prepared product-image with variant images
- Idealo Campaign Link

# 3.1.32
Shopware 6.4 specific changes:
- Cronjobs: Fix issue when using NGINX as webserver
- Hood.de - Configuration: Fix problem when stuck in configuration

# 3.1.33
Shopware 6.5 specific changes:
- Cronjobs: Fix issue when using NGINX as webserver
- Hood.de - Configuration: Fix problem when stuck in configuration

# 3.1.34
Shopware 6.4 specific changes:
- Amazon - Product Preparation / Matching: Improved handling time concept
- eBay - Product Upload: Support of TecDoc KType
- Kaufland: Configuration cannot be exited due to a mandatory field "Shipping groups"
- OTTO - Product Preparation and Upload: Problem with "selected=" after saving a product preparation for the second time

# 3.1.35
Shopware 6.5 specific changes:
- Amazon - Product Preparation / Matching: Improved handling time concept
- eBay - Product Upload: Support of TecDoc KType
- Kaufland: Configuration cannot be exited due to a mandatory field "Shipping groups"
- OTTO - Product Preparation and Upload: Problem with "selected=" after saving a product preparation for the second time

# 3.1.36
Shopware 6.4 specific changes:
- All Marketplace - Attribute Matching: Fixed display of custom field attribute value when label is not provided
- Amazon - Preparation - Multi-product preparation: Specify only the product that has a missing field (e.g. manufacturer)
- Amazon - Product Matching: Problem solved that automatic matching does not work
- eBay Condition Grading

# 3.1.37
Shopware 6.5 specific changes:
- All Marketplace - Attribute Matching: Fixed display of custom field attribute value when label is not provided
- Amazon - Preparation - Multi-product preparation: Specify only the product that has a missing field (e.g. manufacturer)
- Amazon - Product Matching: Problem solved that automatic matching does not work
- eBay Condition Grading

# 3.1.38
Shopware 6.4 specific changes:
- eBay - Product Preparation: Fixed error "Column 'ConditionDescriptors' cannot be null"

# 3.1.39
Shopware 6.5 specific changes:
- eBay - Product Preparation: Fixed error "Column 'ConditionDescriptors' cannot be null"

# 3.1.40
Shopware 6.4 specific changes:
- Order Import - Stock reduction - Fix a problem to reduce stock twice
- Synchronization of order status: Improved performance
- Cdiscount - Synchronization of order status: Problem fixed for orders that have already been shipped
- eBay - Item Synchronization: Fixed issue when preparation is Chinese auction but product on marketplace is Buy Now
- eBay - Product Template - Custom Field Placeholder: Use technical name instead of position
- Etsy - Product Preparation: Add possibility to select all levels of categories
- METRO Shipping Groups

# 3.1.41
Shopware 6.5 specific changes:
- Order Import - Stock reduction - Fix a problem to reduce stock twice
- Synchronization of order status: Improved performance
- Cdiscount - Synchronization of order status: Problem fixed for orders that have already been shipped
- eBay - Item Synchronization: Fixed issue when preparation is Chinese auction but product on marketplace is Buy Now
- eBay - Product Template - Custom Field Placeholder: Use technical name instead of position
- Etsy - Product Preparation: Add possibility to select all levels of categories
- METRO Shipping Groups

# 3.1.42
Shopware 6.4 specific changes:
- All Marketplaces - Configuration - Correction of redirecting and showing missing fields
- Kaufland.de - Lowest Price

# 3.1.43
Shopware 6.5 specific changes:
- All Marketplaces - Configuration - Correction of redirecting and showing missing fields
- Kaufland.de - Lowest Price

# 3.2.00
Shopware 6.4 specific changes:
- Revamped and polished user interface

# 3.2.01
Shopware 6.5 specific changes:
- Revamped and polished user interface

# 3.2.02
Shopware 6.4 specific changes:
- User Interface corrections
- METRO - Product Upload / Configuration: Shipping group ID is no longer used, use name instead

# 3.2.03
Shopware 6.5 specific changes:
- User Interface corrections
- METRO - Product Upload / Configuration: Shipping group ID is no longer used, use name instead

# 3.2.04
Shopware 6.4 specific changes:
- All Marketplaces - Inventory View - Fix a problem with viewing products
- Import order: If no VAT-ID is available, leave field "null"
- Product Preparation: Better display of many product images
- Prepare / Upload Product-List: Filter settings are retained until logout
- Amazon - Product Upload: Sending brand information independently of the manufacturer field
- Amazon - Product Matching: Fix a problem by saving matching
- Hood.de: Use VAT as stored in product data
- Kaufland.de - Product Matching: Automatic Matching optimized

# 3.2.05
Shopware 6.5 specific changes:
- All Marketplaces - Inventory View - Fix a problem with viewing products
- Import order: If no VAT-ID is available, leave field "null"
- Product Preparation: Better display of many product images
- Prepare / Upload Product-List: Filter settings are retained until logout
- Amazon - Product Upload: Sending brand information independently of the manufacturer field
- Amazon - Product Matching: Fix a problem by saving matching
- Hood.de: Use VAT as stored in product data
- Kaufland.de - Product Matching: Automatic Matching optimized

# 3.2.06
Shopware 6.4 specific changes:
- All Marketplaces - Product Preparation: Fix for the case where not all images of the product are shown
- All Marketplaces - Product Upload - Use the standard translation of attribute values and names if they are not
  translated for the respective language
- All Marketplaces - Product List - Search Filter - Finding all SKU that contains the searched string
- Amazon - Product Upload - Prevent to send duplicated image for variations
- Cdiscount - Product Preparation: Images can now be used always from webshop
- Kaufland.de: Also Kaufland.cz and Kaufland.sk
- Ricardo - Product Preparation: Fix SQL error "Incorrect decimal value: '' for
  column `magnalister_ricardo_prepare`.`PriceForAuction` at row 1"

# 3.2.07
Shopware 6.5 specific changes:
- All Marketplaces - Product Preparation: Fix for the case where not all images of the product are shown
- All Marketplaces - Product Upload - Use the standard translation of attribute values and names if they are not translated for the respective language
- All Marketplaces - Product List - Search Filter - Finding all SKU that contains the searched string
- Amazon - Product Upload - Prevent to send duplicated image for variations
- Cdiscount - Product Preparation: Images can now be used always from webshop
- Kaufland.de: Also Kaufland.cz and Kaufland.sk
- Ricardo - Product Preparation: Fix SQL error "Incorrect decimal value: '' for
  column `magnalister_ricardo_prepare`.`PriceForAuction` at row 1"

# 3.2.08
Shopware 6.4 specific changes:
- All Marketplaces - Product Preparation: Images are not shown correctly fixed
- Kaufland.de - Inventory: Fixed a problem with displaying products
- OTTO - Product Upload: Fixed issue when a server error occurred during product upload

# 3.2.09
Shopware 6.5 specific changes:
- All Marketplaces - Product Preparation: Images are not shown correctly fixed
- Kaufland.de - Inventory: Fixed a problem with displaying products
- OTTO - Product Upload: Fixed issue when a server error occurred during product upload

# 3.2.10
Shopware 6.4 specific changes:
- Amazon - Product Preparation - Product list: SQL error "master.ProductsSku' isn't in GROUP BY (1055)" fixed
- eBay - Preparation/Attribute Matching - Categories Dropdown - displaying more in list and loading faster
- Etsy - The entry "When made" for 2020-2023 is no longer valid. Replaced by 2020-2024
- Etsy - Product Upload - Fix a problem with uploading attributes that matched the shop attribute containing "-"
- Kaufland.de - Product Upload: Attribute "weight" with option "weight incl. unit" the unit will be written in lower
  case from now on (like KG will be written as kg)
- OTTO - Product Preparation / Attributes Matching: Fixed the issue with fetching category independent attribute values
- Ricardo - Product Preparation: Problem fixed with the duration of the offer that the time cannot be selected

# 3.2.11
Shopware 6.5 specific changes:
- Amazon - Product Preparation - Product list: SQL error "master.ProductsSku' isn't in GROUP BY (1055)" fixed
- eBay - Preparation/Attribute Matching - Categories Dropdown - displaying more in list and loading faster
- Etsy - The entry "When made" for 2020-2023 is no longer valid. Replaced by 2020-2024
- Etsy - Product Upload - Fix a problem with uploading attributes that matched the shop attribute containing "-"
- Kaufland.de - Product Upload: Attribute "weight" with option "weight incl. unit" the unit will be written in lower
  case from now on (like KG will be written as kg)
- OTTO - Product Preparation / Attributes Matching: Fixed the issue with fetching category independent attribute values
- Ricardo - Product Preparation: Problem fixed with the duration of the offer that the time cannot be selected

# 3.2.12
Shopware 6.4 specific changes:
- Price and Stock Synchronization: Fixed problem with synchronization where a migration from Shopware 5 to 6 was performed
- Product Preparation: Fixed performance issues when loading product images
- Amazon - Product List / Manual Matching: Fixed a problem that causes an SQL error after saving the product matching
- eBay - Inventory Tab - Deleted: Default sorting changed to display last ended items first
- Kaufland - Inventory: Show new Marketplace Unit Id and product URL
- OTTO - Credentials: New OAuth2 Flow implemented
- OTTO - Product Preparation - Category Selection - Error message by selecting a new category

# 3.2.13
Shopware 6.5 specific changes:
- Price and Stock Synchronization: Fixed problem with synchronization where a migration from Shopware 5 to 6 was performed
- Product Preparation: Fixed performance issues when loading product images
- Amazon - Product List / Manual Matching: Fixed a problem that causes an SQL error after saving the product matching
- eBay - Inventory Tab - Deleted: Default sorting changed to display last ended items first
- Kaufland - Inventory: Show new Marketplace Unit Id and product URL
- OTTO - Credentials: New OAuth2 Flow implemented
- OTTO - Product Preparation - Category Selection - Error message by selecting a new category

# 3.2.14
Shopware 6.4 specific changes:
- Amazon - Product Matching - Auto-Matching: The problem of matching several products with auto matching has been fixed
- Amazon - Product Matching - Auto-Matching: Confirmation button added to return to the preparation view after auto matching
- eBay - Prepare Items: Fixed category "Choose" button with French language
- Etsy - Preparation Form: Add option to get always images from webshop
- Etsy - Product Upload: Fix the error "There was a problem with /title/1 : : can only be use once"
- Kaufland: Added Austria and Poland to the list of available storefronts and let only select configured storefronts
- Kaufland - Content Update: Replaced "Kaufland.de" with "Kaufland"

# 3.2.15
Shopware 6.5 specific changes:
- Amazon - Product Matching - Auto-Matching: The problem of matching several products with auto matching has been fixed
- Amazon - Product Matching - Auto-Matching: Confirmation button added to return to the preparation view after auto matching
- eBay - Prepare Items: Fixed category "Choose" button with French language
- Etsy - Preparation Form: Add option to get always images from webshop
- Etsy - Product Upload: Fix the error "There was a problem with /title/1 : : can only be use once"
- Kaufland: Added Austria and Poland to the list of available storefronts and let only select configured storefronts
- Kaufland - Content Update: Replaced "Kaufland.de" with "Kaufland"

# 3.2.16
Shopware 6.6 Compatibility

# 3.2.17
Shopware 6.4 specific changes:
- All Marketplaces - Variation Matching / Product Upload - Correctly match variations when uploading products if the
  attribute name is not set in the marketplace language
- All Marketplaces - Invoice Upload - Fix fatal error when invoice document could not be retrieved (when corrupted)
- Amazon - Email to Buyer - Content update
- Amazon - Configuration - B2B Content Update (+moving the settings to the price settings)
- Amazon - Shipping Service Feature - Problems with the appearance fixed

# 3.2.18
Shopware 6.5 specific changes:
- All Marketplaces - Variation Matching / Product Upload - Correctly match variations when uploading products if the
  attribute name is not set in the marketplace language
- All Marketplaces - Invoice Upload - Fix fatal error when invoice document could not be retrieved (when corrupted)
- Amazon - Email to Buyer - Content update
- Amazon - Configuration - B2B Content Update (+moving the settings to the price settings)
- Amazon - Shipping Service Feature - Problems with the appearance fixed

# 3.2.19
Shopware 6.6 specific changes:
- All Marketplaces - Cron Url - Fix a problem by automatic cron( e.g. Order import, Inventory synchronization)
- All Marketplaces - Variation Matching / Product Upload - Correctly match variations when uploading products if the
  attribute name is not set in the marketplace language
- All Marketplaces - Invoice Upload - Fix fatal error when invoice document could not be retrieved (when corrupted)
- Amazon - Email to Buyer - Content update
- Amazon - Configuration - B2B Content Update (+moving the settings to the price settings)
- Amazon - Shipping Service Feature - Problems with the appearance fixed

# 3.2.20
Shopware 6.6 specific changes:
- Order Status Synchronization: Compatibility problems with Shopware 6.6 fixed

# 3.2.21
Shopware 6.4 specific changes:
- Kaufland: Fulfillment by Kaufland Implemented

# 3.2.22
Shopware 6.5 specific changes:
- Kaufland: Fulfillment by Kaufland Implemented

# 3.2.23
Shopware 6.6 specific changes:
- Kaufland: Fulfillment by Kaufland Implemented

# 3.2.24
Shopware 6.6 specific changes:
- All Marketplaces - Order Import - Fix a problem by importing order

# 3.2.25
Shopware 6.4 specific changes:
- All Marketplaces - Fix a problem by CRON urls in magnslister
- All Marketlaces - Attribute Matching - Auto matching improvement for freetext attribute
- All Marketplaces - Fix a problem with getting custom field value for second language
- All Marketlaces - Attribute Matching - Fix a problem by sending attribute value that contain comma ","
- Hood - Product Upload: Allow upload of product variations without stock
- Idealo - Possibility to configure a currency

# 3.2.26
Shopware 6.5 specific changes:
- All Marketplaces - Fix a problem by CRON urls in magnslister
- All Marketlaces - Attribute Matching - Auto matching improvement for freetext attribute
- All Marketplaces - Fix a problem with getting custom field value for second language
- All Marketlaces - Attribute Matching - Fix a problem by sending attribute value that contain comma ","
- Hood - Product Upload: Allow upload of product variations without stock
- Idealo - Possibility to configure a currency

# 3.2.27
Shopware 6.6 specific changes:
- A solution to fixing a conflict with other plugins
- All Marketplaces - Order Import - Fix issue that order were not imported because we could not get the tax from a
  Shopware system
- All Marketplaces - Fix a problem with getting custom field value for second language
- All Marketlaces - Attribute Matching - Auto matching improvement for freetext attribute
- All Marketlaces - Attribute Matching - Fix a problem by sending attribute value that contain comma ","
- Hood - Product Upload: Allow upload of product variations without stock

# 3.2.28
Shopware 6.5 specific changes:
- All Marketplaces - Fix a problem with automatic order improt and product synchronization (It affected only a part of
  Shopware shop)

# 3.2.29
Shopware 6.4 specific changes:
- Cdiscount - Product Upload: Fix fatal error on PHP 8

# 3.2.30
Shopware 6.5 specific changes:
- Cdiscount - Product Upload: Fix fatal error on PHP 8

# 3.2.31
Shopware 6.6 specific changes:
- All Marketplaces - Order Status Synchronization - Fix a problem to send shipping date
- Cdiscount - Product Upload: Fix fatal error on PHP 8

# 3.2.32
Shopware 6.6 specific changes:
- All Marketplaces - Order Status Synchronization - Fix a rare problem to send shipping date - It is safer solution than last change (Recommended to update)

# 3.2.33
Shopware 6.4 specific changes:
- All Marketplaces - Available items in shipping and payment method configuration can change, according to sales channel
  configuration.
- Amazon - Auto Matching: Use correct default value for B2B Sell to from configuration
- Kaufland - New logo

# 3.2.34
Shopware 6.5 specific changes:
- All Marketplaces - Available items in shipping and payment method configuration can change, according to sales channel
  configuration.
- Amazon - Auto Matching: Use correct default value for B2B Sell to from configuration
- Kaufland - New logo

# 3.2.35
Shopware 6.6 specific changes:
- All Marketplaces - Available items in shipping and payment method configuration can change, according to sales channel
  configuration.
- Amazon - Auto Matching: Use correct default value for B2B Sell to from configuration
- Kaufland - New logo

# 3.2.36
Shopware 6.4 specific changes:
- Amazon - Inventory - Show correct label for column Business Feature (B2B, B2B and B2C, Standard)
- Amazon - Prepare - Fix wrong database value for B2B setting (Bug from 3.2.33)

# 3.2.37
Shopware 6.5 specific changes:
- Amazon - Inventory - Show correct label for column Business Feature (B2B, B2B and B2C, Standard)
- Amazon - Prepare - Fix wrong database value for B2B setting (Bug from 3.2.34)

# 3.2.38
Shopware 6.6 specific changes:
- Amazon - Inventory - Show correct label for column Business Feature (B2B, B2B and B2C, Standard)
- Amazon - Prepare - Fix wrong database value for B2B setting (Bug from 3.2.35)

# 3.2.39
Shopware 6.4 specific changes:
- OBI - Connect your shop to OBI marketplace

# 3.2.40
Shopware 6.5 specific changes:
- OBI - Connect your shop to OBI marketplace

# 3.2.41
Shopware 6.6 specific changes:
- OBI - Connect your shop to OBI marketplace

# 3.2.42
Shopware 6.4 specific changes:
- All marketplaces - Show error message, if product language, sales channel or shipping- or payment method are invalid UUIDs
- Amazon - Product Upload - Adding master product images not assigned to any variant to all variants
- Cdiscount - Attribute Matching - Fix a problem by getting value from shop-system
- METRO - Implemented Cross Borders Trade
- OBI - Configuration - Order Import - Showing shipping and payment method fields and payment status field
- OBI - Configuration - Field to configure delivery time attribute and delivery fallback value

# 3.2.43
Shopware 6.5 specific changes:
- All marketplaces - Show error message, if product language, sales channel or shipping- or payment method are invalid UUIDs
- Amazon - Product Upload - Adding master product images not assigned to any variant to all variants
- Cdiscount - Attribute Matching - Fix a problem by getting value from shop-system
- METRO - Implemented Cross Borders Trade
- OBI - Configuration - Order Import - Showing shipping and payment method fields and payment status field
- OBI - Configuration - Field to configure delivery time attribute and delivery fallback value

# 3.2.44
Shopware 6.6 specific changes:
- All marketplaces - Show error message, if product language, sales channel or shipping- or payment method are invalid UUIDs
- Amazon - Product Upload - Adding master product images not assigned to any variant to all variants
- Cdiscount - Attribute Matching - Fix a problem by getting value from shop-system
- METRO - Implemented Cross Borders Trade
- OBI - Configuration - Order Import - Showing shipping and payment method fields and payment status field
- OBI - Configuration - Field to configure delivery time attribute and delivery fallback value

# 3.2.45
Shopware 6.4 specific changes:
- All marketplaces - Database Connection: Fallback to hostname if provided socket does not exist
- Amazon - Auto Matching: Process one variant in one ajax call to prevent timeouts
- eBay - Preparation/Upload: Save edited Title, if store default is not used
- eBay - Preparation/Upload: Save edited description, if store default is not used

# 3.2.46
Shopware 6.5 specific changes:
- All marketplaces - Database Connection: Fallback to hostname if provided socket does not exist
- Amazon - Auto Matching: Process one variant in one ajax call to prevent timeouts
- eBay - Preparation/Upload: Save edited Title, if store default is not used
- eBay - Preparation/Upload: Save edited description, if store default is not used

# 3.2.47
Shopware 6.6 specific changes:
- All marketplaces - Database Connection: Fallback to hostname if provided socket does not exist
- Amazon - Auto Matching: Process one variant in one ajax call to prevent timeouts
- eBay - Preparation/Upload: Save edited Title, if store default is not used
- eBay - Preparation/Upload: Save edited description, if store default is not used

# 3.2.48
Shopware 6.4 specific changes:
- All marketplaces - Attribute-Matching: Fixed an issue where matched values could be incorrectly saved as custom entries, leading to upload errors
- Amazon - Manual Prepare Match: Added variation item search queue that the webserver won't be overloaded
- Amazon - Upload product: “SKU missing” issue fixed
- eBay - Preparation/Upload: Save edited dispatch max time, if store default is not used

# 3.2.49
Shopware 6.5 specific changes:
- All marketplaces - Attribute-Matching: Fixed an issue where matched values could be incorrectly saved as custom entries, leading to upload errors
- Amazon - Manual Prepare Match: Added variation item search queue that the webserver won't be overloaded
- Amazon - Upload product: “SKU missing” issue fixed
- eBay - Preparation/Upload: Save edited dispatch max time, if store default is not used

# 3.2.50
Shopware 6.6 specific changes:
- All marketplaces - Attribute-Matching: Fixed an issue where matched values could be incorrectly saved as custom entries, leading to upload errors
- Amazon - Manual Prepare Match: Added variation item search queue that the webserver won't be overloaded
- Amazon - Upload product: “SKU missing” issue fixed
- eBay - Preparation/Upload: Save edited dispatch max time, if store default is not used

# 3.2.51
Shopware 6.6 specific changes:
- Shopware 6.6.5.0 and 6.6.5.1 compatibility

# 3.2.52
Shopware 6.4 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Hood - Preparation/Upload: Save edited title and dispatch max time, if store default is not used
- Etsy - Upload: Set correct attribute name from shop
- OBI - Price and stock synchronization: Fixed a problem where the price was not submitted during synchronization
- OBI - Order Status Synchronization: Fixed issue for all non-configured order statuses where a refund was triggered
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OTTO - Order Status Synchronization: Improved log output if the configuration is wrong

# 3.2.53
Shopware 6.5 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Hood - Preparation/Upload: Save edited title and dispatch max time, if store default is not used
- Etsy - Upload: Set correct attribute name from shop
- OBI - Price and stock synchronization: Fixed a problem where the price was not submitted during synchronization
- OBI - Order Status Synchronization: Fixed issue for all non-configured order statuses where a refund was triggered
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OTTO - Order Status Synchronization: Improved log output if the configuration is wrong

# 3.2.54
Shopware 6.6 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Hood - Preparation/Upload: Save edited title and dispatch max time, if store default is not used
- Etsy - Upload: Set correct attribute name from shop
- OBI - Price and stock synchronization: Fixed a problem where the price was not submitted during synchronization
- OBI - Order Status Synchronization: Fixed issue for all non-configured order statuses where a refund was triggered
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OTTO - Order Status Synchronization: Improved log output if the configuration is wrong

# 3.2.55
Shopware 6.4 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Amazon - Click and Colect - Remove all options and configurations from the plugin
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Cdiscount - Product Upload: Fixed product package generator when dealing with variants
- eBay - Upload: Don't show replace inventory button
- eBay - Upload/prepare product - Use the same placeholder as the product description for the product title template
- eBay - Category Import - Also for Store Categories, remove outdated categories from Plugin
- Idealo - Fix a problem to use Ideal (from last package)
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OBI - Configuration: Field WarehouseId is required
-
# 3.2.56
Shopware 6.5 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Amazon - Click and Colect - Remove all options and configurations from the plugin
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Cdiscount - Product Upload: Fixed product package generator when dealing with variants
- eBay - Upload: Don't show replace inventory button
- eBay - Upload/prepare product - Use the same placeholder as the product description for the product title template
- eBay - Category Import - Also for Store Categories, remove outdated categories from Plugin
- Idealo - Fix a problem to use Ideal (from last package)
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OBI - Configuration: Field WarehouseId is required

# 3.2.57
Shopware 6.6 specific changes:
- Amazon - Manual Prepare Match: Don't block the ui if a product is already loaded
- Amazon - Click and Colect - Remove all options and configurations from the plugin
- Cdiscount - Product Upload: Problem fixed when uploading variants (these were just ignored)
- Cdiscount - Product Upload: Solve the problem by displaying the number of successfully uploaded products
- Cdiscount - Product Upload: Fixed product package generator when dealing with variants
- eBay - Upload: Don't show replace inventory button
- eBay - Upload/prepare product - Use the same placeholder as the product description for the product title template
- eBay - Category Import - Also for Store Categories, remove outdated categories from Plugin
- Idealo - Fix a problem to use Ideal (from last package)
- OBI - Order Status Synchronization: Option to choose between the carriers supported by the marketplace or match the supported carriers with the carriers defined in the shop
- OBI - Configuration: Field WarehouseId is required in settings, added API error in an error message for better understanding

# 3.2.58
Shopware 6.4 specific changes:
- All Marketplaces - Preparation - Use the progress bar and pagination to prepare lots of products at once
- Cdiscount - Upload: Fix JSON error when variation images don't exist
- OTTO - Preparation - New field to specify the main product image
-
# 3.2.59
Shopware 6.5 specific changes:
- All Marketplaces - Preparation - Use the progress bar and pagination to prepare lots of products at once
- Cdiscount - Upload: Fix JSON error when variation images don't exist
- OTTO - Preparation - New field to specify the main product image

# 3.2.60
Shopware 6.6 specific changes:
- All Marketplaces - Preparation - Use the progress bar and pagination to prepare lots of products at once
- Cdiscount - Upload: Fix JSON error when variation images don't exist
- OTTO - Preparation - New field to specify the main product image

# 3.2.61
Shopware 6.4 specific changes:
- All Marketplaces - Product List - Prepare / Upload: Fixed an SQL error when sorting products by name
- Cronjob: Improved frontend error message if no parameters were given for the cronjob url
- eBay - Product Preparation: The view was not responsive during product preparation
- Hood - Product Preparation: Problem fixed with the preparation of variant products

# 3.2.62
Shopware 6.5 specific changes:
- All Marketplaces - Product List - Prepare / Upload: Fixed an SQL error when sorting products by name
- Cronjob: Improved frontend error message if no parameters were given for the cronjob url
- eBay - Product Preparation: The view was not responsive during product preparation
- Hood - Product Preparation: Problem fixed with the preparation of variant products

# 3.2.63
Shopware 6.6 specific changes:
- All Marketplaces - Product List - Prepare / Upload: Fixed an SQL error when sorting products by name
- Cronjob: Improved frontend error message if no parameters were given for the cronjob url
- eBay - Product Preparation: The view was not responsive during product preparation
- Hood - Product Preparation: Problem fixed with the preparation of variant products

# 3.2.64
Shopware 6.4 specific changes:
- eBay - Preparation - Fix a problem by saving preparation with category doesn't support variation
- METRO: Upload Product fixed problem with not allowed HTML tags

# 3.2.65
Shopware 6.5 specific changes:
- eBay - Preparation - Fix a problem by saving preparation with category doesn't support variation
- METRO: Upload Product fixed problem with not allowed HTML tags

# 3.2.66
Shopware 6.6 specific changes:
- eBay - Preparation - Fix a problem by saving preparation with category doesn't support variation
- METRO: Upload Product fixed problem with not allowed HTML tags

# 3.2.67
Shopware 6.4 specific changes:
- Amazon - Product Preparation: Fixed problem where the product could not be successfully prepared if an empty B2B tier discount was entered
- Amazon / eBay - Product Preparation - Performance improvement by loading forms
- eBay - Update Orders: PHP warning fixed "Trying to access array offset on value of type null"
- Kaufland - Prepare Product: In the Comment field, the character length has been limited to the maximum value of 250 characters
- METRO - Prepare Product: Updated hint text for "important features"

# 3.2.68
Shopware 6.5 specific changes:
- Amazon - Product Preparation: Fixed problem where the product could not be successfully prepared if an empty B2B tier discount was entered
- Amazon / eBay - Product Preparation - Performance improvement by loading forms
- eBay - Update Orders: PHP warning fixed "Trying to access array offset on value of type null"
- Kaufland - Prepare Product: In the Comment field, the character length has been limited to the maximum value of 250 characters
- METRO - Prepare Product: Updated hint text for "important features"

# 3.2.69
Shopware 6.6 specific changes:
- Amazon - Product Preparation: Fixed problem where the product could not be successfully prepared if an empty B2B tier discount was entered
- Amazon / eBay - Product Preparation - Performance improvement by loading forms
- eBay - Update Orders: PHP warning fixed "Trying to access array offset on value of type null"
- Kaufland - Prepare Product: In the Comment field, the character length has been limited to the maximum value of 250 characters
- METRO - Prepare Product: Updated hint text for "important features"

# 3.2.70
Shopware 6.4 specific changes:
- Support Shopware role "view" permission for magnalister
- eBay - Preparation - Fix a problem with redirecting to the product list after successful preparation

# 3.2.71
Shopware 6.5 specific changes:
- Support Shopware role "view" permission for magnalister
- eBay - Preparation - Fix a problem with redirecting to the product list after successful preparation

# 3.2.72
Shopware 6.6 specific changes:
- Support Shopware role "view" permission for magnalister
- eBay - Preparation - Fix a problem with redirecting to the product list after successful preparation

# 3.2.73
Shopware 6.4 specific changes:
- All Marketplaces - Order Import - If the customer has a company, they will be marked as a business customer in order import
- All Marketplaces - Attribute Matching - Fix JSON encode error for PHP8, fix matching for multiselect custom fields
- All Marketplaces - Attribute Matching - Display keyword and meta-description in list attributes
- Etsy - Product Preparation/Upload - Supporting Product Attributes
- Idealo - Configuration - New field to specify product and image url

# 3.2.74
Shopware 6.5 specific changes:
- All Marketplaces - Order Import - If the customer has a company, they will be marked as a business customer in order import
- All Marketplaces - Attribute Matching - Fix JSON encode error for PHP8, fix matching for multiselect custom fields
- All Marketplaces - Attribute Matching - Display keyword and meta-description in list attributes
- Etsy - Product Preparation/Upload - Supporting Product Attributes
- Idealo - Configuration - New field to specify product and image url

# 3.2.75
Shopware 6.6 specific changes:
- All Marketplaces - Order Import - If the customer has a company, they will be marked as a business customer in order import
- All Marketplaces - Attribute Matching - Fix JSON encode error for PHP8, fix matching for multiselect custom fields
- All Marketplaces - Attribute Matching - Display keyword and meta-description in list attributes
- Etsy - Product Preparation/Upload - Supporting Product Attributes
- Idealo - Configuration - New field to specify product and image url

# 3.2.76
Shopware 6.4 specific changes:
- All Marketplaces - Attribute Matching - Show keyword and meta field in list attributes
- All Marketplaces - Attribute Matching - Problem with matching and sending manufacturer number attribute is fixed
- All Marketplaces - Image Processing - Fix a rare problem by updating to new version of Shopware(6.6.8.2)
- All Marketplaces - Load magnalister admin - Creating a solution for the problem with strange domain configuration
- All Marketplaces - Attribute Matching - Use URL of media custom field instead of their id
- Amazon - Shipping Confirmation - Fixed a problem
- Check24 - Product Preparation -  Add GPSR fields
- Hood.de - GPSR - Add attribute matching functionality for GPSR attributes
- Idealo - Configuration - New field to specify product and image url
- Metro - Product Preparation - Because the text editor uses < strong > tags, we convert them into < b > tags (METRO does not support 'strong')
- METRO ErrorLog - Show Additional Information
- PriceMinister - Prepare Form - Add an option to "Always use the latest title from web shop" for item title

# 3.2.77
Shopware 6.5 specific changes:

- All Marketplaces - Attribute Matching - Show keyword and meta field in list attributes
- All Marketplaces - Attribute Matching - Problem with matching and sending manufacturer number attribute is fixed
- All Marketplaces - Image Processing - Fix a rare problem by updating to new version of Shopware(6.6.8.2)
- All Marketplaces - Load magnalister admin - Creating a solution for the problem with strange domain configuration
- All Marketplaces - Attribute Matching - Use URL of media custom field instead of their id
- Amazon - Shipping Confirmation - Fixed a problem
- Check24 - Product Preparation -  Add GPSR fields
- Hood.de - GPSR - Add attribute matching functionality for GPSR attributes
- Idealo - Configuration - New field to specify product and image url
- Metro - Product Preparation - Because the text editor uses < strong > tags, we convert them into < b > tags (METRO does not support 'strong')
- METRO ErrorLog - Show Additional Information
- PriceMinister - Prepare Form - Add an option to "Always use the latest title from web shop" for item title

# 3.2.78
Shopware 6.6 specific changes:

- All Marketplaces - Attribute Matching - Show keyword and meta field in list attributes
- All Marketplaces - Attribute Matching - Problem with matching and sending manufacturer number attribute is fixed
- All Marketplaces - Image Processing - Fix a rare problem by updating to new version of Shopware(6.6.8.2)
- All Marketplaces - Load magnalister admin - Creating a solution for the problem with strange domain configuration
- All Marketplaces - Attribute Matching - Use URL of media custom field instead of their id
- Amazon - Shipping Confirmation - Fixed a problem
- Check24 - Product Preparation -  Add GPSR fields
- Hood.de - GPSR - Add attribute matching functionality for GPSR attributes
- Idealo - Configuration - New field to specify product and image url
- Metro - Product Preparation - Because the text editor uses < strong > tags, we convert them into < b > tags (METRO does not support 'strong')
- METRO ErrorLog - Show Additional Information
- PriceMinister - Prepare Form - Add an option to "Always use the latest title from web shop" for item title

# 3.2.79
Shopware 6.4 specific changes:
- Loading magnalister - Fix "Warning: Cannot modify header information - headers already sent by ..."

# 3.2.80
Shopware 6.5 specific changes:
- Loading magnalister - Fix "Warning: Cannot modify header information - headers already sent by ..."

# 3.2.81
Shopware 6.6 specific changes:
- Loading magnalister - Fix "Warning: Cannot modify header information - headers already sent by ..."

# 3.2.82
Shopware 6.4 specific changes:
- All Marketplaces: Product Price Calculation - Fixed an issue with calculating the price with a currency other than the default currency.
- All Marketplaces - Order import: Fill VatId also in order address and order customer
- Amazon: Fixed a small problem that warnings are written in the error-log (Warning: Undefined array key "DE")
- eBay - Order Refunds: Info Text corrected
- eBay - Order Refunds: Info Text - No partial refunds possible
- Kaufland - Product Upload: Fix PHP 8 warning when short subtitle is null
- METRO - OrderImport: Help text for carrier has been adapted for a clearer and better understanding

# 3.2.83
Shopware 6.5 specific changes:
- All Marketplaces: Product Price Calculation - Fixed an issue with calculating the price with a currency other than the default currency.
- All Marketplaces - Order import: Fill VatId also in order address and order customer
- Amazon: Fixed a small problem that warnings are written in the errorlog (Warning: Undefined array key "DE")
- eBay - Order Refunds: Info Text corrected
- eBay - Order Refunds: Info Text - No partial refunds possible
- Kaufland - Product Upload: Fix PHP 8 warning when short subtitle is null
- METRO - OrderImport: Help text for carrier has been adapted for a clearer and better understanding


# 3.2.84
Shopware 6.6 specific changes:
- All Marketplaces: Make magnalister compatible with some third-party plugins
- All Marketplaces: Product Price Calculation - Fixed an issue with calculating the price with a currency other than the default currency.
- All Marketplaces - Order import: Fill VatId also in order address and order customer
- Amazon: Fixed a small problem that warnings are written in the error-log (Warning: Undefined array key "DE")
- eBay - Order Refunds: Info Text corrected
- eBay - Order Refunds: Info Text - No partial refunds possible
- Kaufland - Product Upload: Fix PHP 8 warning when short subtitle is null
- METRO - OrderImport: Help text for carrier has been adapted for a clearer and better understanding

# 3.2.85
Shopware 6.4 specific changes:
- All Marketplace - Productlist: Small Fix

# 3.2.86
Shopware 6.5 specific changes:
- All Marketplace - Productlist: Small Fix

# 3.2.87
Shopware 6.6 specific changes:
- All Marketplace - Productlist: Small Fix

# 3.2.88
Shopware 6.4 specific changes:
- Fixed bug where the slider for marketplaces did not work correctly in the Chrome browser
- All Marketplaces: Image Processing - Fixed constant regeneration of images on a CDN
- All Marketplaces: Added compatibility with PHP-PDO Database Connection
- Amazon - Manual Product Matching: Show variant title, SKU and price at the top of each block for better distinction
- Amazon - Prepare: Removed unneeded auto_increment from magnalister_amazon_prepare table
- eBay - Product Preparation: For the French-language version, set the filter for marketplace status to "Filtre : statut de la place de marché" so all products are displayed by default regardless of status
- eBay - Order Status Synchronization: Fixed issue with shipping method as carrier
- Metro - Prepare: Don't get value if no brand field exists

# 3.2.89
Shopware 6.5 specific changes:
- Fixed bug where the slider for marketplaces did not work correctly in the Chrome browser
- All Marketplaces: Image Processing - Fixed constant regeneration of images on a CDN
- All Marketplaces: Added compatibility with PHP-PDO Database Connection
- Amazon - Manual Product Matching: Show variant title, SKU and price at the top of each block for better distinction
- Amazon - Prepare: Removed unneeded auto_increment from magnalister_amazon_prepare table
- eBay - Product Preparation: For the French-language version, set the filter for marketplace status to "Filtre : statut de la place de marché" so all products are displayed by default regardless of status
- eBay - Order Status Synchronization: Fixed issue with shipping method as carrier
- Metro - Prepare: Don't get value if no brand field exists
- 

# 3.2.90
Shopware 6.6 specific changes:
- Fixed bug where the slider for marketplaces did not work correctly in the Chrome browser
- All Marketplaces: Order Import: Fixed an issue when running flows
- All Marketplaces: Image Processing - Fixed constant regeneration of images on a CDN
- All Marketplaces: Added compatibility with PHP-PDO Database Connection
- Amazon - Manual Product Matching: Show variant title, SKU and price at the top of each block for better distinction
- Amazon - Prepare: Removed unneeded auto_increment from magnalister_amazon_prepare table
- eBay - Product Preparation: For the French-language version, set the filter for marketplace status to "Filtre : statut de la place de marché" so all products are displayed by default regardless of status
- eBay - Order Status Synchronization: Fixed issue with shipping method as carrier
- Metro - Prepare: Don't get value if no brand field exists

# 3.2.91
Shopware 6.4 specific changes:
- All Marketplaces - Prepare Product: The sorting order has been changed so that the most recently added product now comes first.
- eBay - Prepare Product: Fixed a bug where the default filter setting for the marketplace status filter was not set to 'all' for Spanish.
- eBay - Product Description - Supporting 4 bytes characters like emojis
- OBI - Inventory: Button to delete inventory disabled because it is not supported by the marketplace
- OTTO: Support for Shipping Profiles and New OTTO App Migration

# 3.2.92
Shopware 6.5 specific changes:
- All Marketplaces - Prepare Product: The sorting order has been changed so that the most recently added product now comes first.
- eBay - Prepare Product: Fixed a bug where the default filter setting for the marketplace status filter was not set to 'all' for Spanish.
- eBay - Product Description - Supporting 4 bytes characters like emojis
- OBI - Inventory: Button to delete inventory disabled because it is not supported by the marketplace
- OTTO: Support for Shipping Profiles and New OTTO App Migration

# 3.2.93
Shopware 6.6 specific changes:
- All Marketplaces - Prepare Product: The sorting order has been changed so that the most recently added product now comes first.
- eBay - Prepare Product: Fixed a bug where the default filter setting for the marketplace status filter was not set to 'all' for Spanish.
- eBay - Product Description - Supporting 4 bytes characters like emojis
- OBI - Inventory: Button to delete inventory disabled because it is not supported by the marketplace
- OTTO: Support for Shipping Profiles and New OTTO App Migration

# 3.2.94
Shopware 6.4 specific changes:
- All Marketplaces: Fixed an issue causing the marketplace page to display incorrectly on PHP versions lower than 8
- All Marketplaces - Images: Use correct method for file size in Shopware before v6.6
- Cdiscount: Migration to Octopia API (changes in the configuration)
- OTTO - Product Preparation: Fixed error "Column 'DeliveryType' cannot be null (1048)"
- OTTO - Product Preparation: OTTO - Prepare Product: Fixed a bug where the delivery time was being checked during product preparation, which is no longer necessary for new products since shipping profiles are now used

# 3.2.95
Shopware 6.5 specific changes:
- All Marketplaces: Fixed an issue causing the marketplace page to display incorrectly on PHP versions lower than 8
- All Marketplaces - Images: Fixed to use the correct method for determining file size in Shopware versions prior to v6.6
- Cdiscount: Migration to Octopia API (changes in the configuration)
- OTTO - Product Preparation: Fixed error "Column 'DeliveryType' cannot be null (1048)"
- OTTO - Product Preparation: OTTO - Prepare Product: Fixed a bug where the delivery time was being checked during product preparation, which is no longer necessary for new products since shipping profiles are now used

# 3.2.96
Shopware 6.6 specific changes:
- All Marketplaces: Fixed an issue causing the marketplace page to display incorrectly on PHP versions lower than 8
- Cdiscount: Migration to Octopia API (changes in the configuration)
- OTTO - Product Preparation: Fixed error "Column 'DeliveryType' cannot be null (1048)"
- OTTO - Product Preparation: OTTO - Prepare Product: Fixed a bug where the delivery time was being checked during product preparation, which is no longer necessary for new products since shipping profiles are now used

# 3.2.97
Shopware 6.4 specific changes:
- All marketplaces - Order Import: Fixed an issue where some orders were imported multiple times
- All marketplaces - Order Import: Set purchase date in correct timezone for all shop systems
- All marketplaces - Order Import - Configuration: Check if the order status is used more than once and show an error message if it is
- All marketplaces - Prepare / Upload: Product list should load faster (image loading optimization)
- Hood - Inventory View: Marketplace link corrected
- Idealo - Prepare: Read image URLs correctly from CDN domains
- Kaufland - Prepare: Fixed the ajax auto matching

# 3.2.98
Shopware 6.5 specific changes:
- All marketplaces - Order Import: Fixed an issue where some orders were imported multiple times
- All marketplaces - Order Import: Set purchase date in correct timezone for all shop systems
- All marketplaces - Order Import - Configuration: Check if the order status is used more than once and show an error message if it is
- All marketplaces - Prepare / Upload: Product list should load faster (image loading optimization)
- Hood - Inventory View: Marketplace link corrected
- Idealo - Prepare: Read image URLs correctly from CDN domains
- Kaufland - Prepare: Fixed the ajax auto matching

# 3.2.99
Shopware 6.6 specific changes:
- All marketplaces - Order Import: Fixed an issue where some orders were imported multiple times
- All marketplaces - Order Import: Set purchase date in correct timezone for all shop systems
- All marketplaces - Order Import - Configuration: Check if the order status is used more than once and show an error message if it is
- All marketplaces - Prepare / Upload: Product list should load faster (image loading optimization)
- Hood - Inventory View: Marketplace link corrected
- Idealo - Prepare: Read image URLs correctly from CDN domains
- Kaufland - Prepare: Fixed the ajax auto matching

# 3.2.100
Shopware 6.4 specific changes:
- Fixed the problem that the CSS was not completely and correctly loaded and therefore errors occurred when displaying the page
- All Marketplaces - Product-List: Add an option to reset attribute matching
- All Marketplaces - Product List — Improved performance when loading new images.
- All Marketplaces - Order Import: Add default value for city in customer address
- Amazon - Product Preparation: Fixed an error that occurred when search keywords were empty in a product
- eBay: TecDoc KType Compatibility Notes (Constraints)
- eBay – Price/Stock Synchronization – Fix an issue syncing eBay variations with simple products in the shop
- Idealo - Product Upload: Fixed bug that the campaign link was not created correctly according to Shopware6 specifications
- Kaufland - Prepare Product: The help text for the meta-keywords has been adjusted, no text is allowed, but only tags separated by a comma


# 3.3.101
Shopware 6.5 specific changes:
- Fixed the problem that the CSS was not completely and correctly loaded and therefore errors occurred when displaying the page
- All Marketplaces - Product-List: Add an option to reset attribute matching
- All Marketplaces - Product List — Improved performance when loading new images.
- All Marketplace - Order Import: Add default value for city in customer address
- Amazon - Product Preparation: Fixed an error that occurred when search keywords were empty in a product
- eBay: TecDoc KType Compatibility Notes (Constraints)
- eBay – Price/Stock Synchronization – Fix an issue syncing eBay variations with simple products in the shop
- Idealo - Product Upload: Fixed bug that the campaign link was not created correctly according to Shopware6 specifications
- Kaufland - Prepare Product: The help text for the meta-keywords has been adjusted, no text is allowed, but only tags separated by a comma

# 3.3.102
Shopware 6.6 specific changes:
- All marketplaces - Prepare / Upload: Product List should load faster (image loading optimization)
- Fixed the problem that the CSS was not completely and correctly loaded and therefore errors occurred when displaying the page
- All Marketplace - Product-List: Add an option to reset attribute matching
- All Marketplaces - Product List — Improved performance when loading new images.
- All Marketplace - Order Import: Add default value for city in customer address
- Amazon - Product Preparation: Fixed an error that occurred when search keywords were empty in a product
- eBay: TecDoc KType Compatibility Notes (Constraints)
- eBay – Price/Stock Synchronization – Fix an issue syncing eBay variations with simple products in the shop
- Idealo - Product Upload: Fixed bug that the campaign link was not created correctly according to Shopware6 specifications
- Kaufland - Prepare Product: The help text for the meta-keywords has been adjusted, no text is allowed, but only tags separated by a comma

# 3.3.00
Shopware 6.7 specific changes:
- Compatibility with 6.7

# 3.2.103
Shopware 6.4 specific changes:
- Fixed product loading issue introduced in the previous plugin version, which occurred only when certain PHP debug options were enabled

# 3.3.01
Shopware 6.5 specific changes:
- Fixed product loading issue introduced in the previous plugin version, which occurred only when certain PHP debug options were enabled

# 3.3.02
Shopware 6.6 specific changes:
- Fixed product loading issue introduced in the previous plugin version, which occurred only when certain PHP debug options were enabled

# 3.3.03
Shopware 6.7 specific changes:
- Fixed product loading issue introduced in the previous plugin version, which occurred only when certain PHP debug options were enabled

# 3.3.04 - 3.3.07
Shopware 6.4 - 6.7 specific changes:
- All Marketplaces - Order Import: Reverted changes that caused the error "Transaction commit failed because the transaction has been marked for rollback only" in some cases

# 3.3.08
Shopware 6.7 specific changes:
- All Marketplaces - Order Import: Fix a problem to run the placed order flow

# 3.3.09
Shopware 6.7 specific changes:
- All Marketplaces – Order Import: Fixed an issue introduced in the previous version

# 3.3.10
Shopware 6.5 specific changes:
- Amazon - Listing API: Migration changes

# 3.3.11
Shopware 6.6 specific changes:
- Amazon - Listing API: Migration changes

# 3.3.12
Shopware 6.7 specific changes:
- Amazon - Listing API: Migration changes

# 3.3.13
Shopware 6.5 specific changes:
- All shop systems – Amazon: Fixed an issue where the message 'The magnalister plugin needs to be updated to support the latest Amazon API requirements.' still appeared even after the update.

# 3.3.14
Shopware 6.6 specific changes:
- All shop systems – Amazon: Fixed an issue where the message 'The magnalister plugin needs to be updated to support the latest Amazon API requirements.' still appeared even after the update.

# 3.3.15
Shopware 6.7 specific changes:
- All shop systems – Amazon: Fixed an issue where the message 'The magnalister plugin needs to be updated to support the latest Amazon API requirements.' still appeared even after the update.

# 3.3.16
Shopware 6.7.1 specific changes:
- Shopware 6.7.1+ – Order Import: Fixed an issue where the delivery filter in order overview did not work for magnalister orders

# 3.3.17
Shopware 6.5 specific changes:
- Amazon - Product Preparation: Fixed an issue with populating attribute matching from the attribute matching tab

# 3.3.18
Shopware 6.6 specific changes:
- Amazon - Product Preparation: Fixed an issue with populating attribute matching from the attribute matching tab

# 3.3.19
Shopware 6.7 specific changes:
- Amazon - Product Preparation: Fixed an issue with populating attribute matching from the attribute matching tab

# 3.3.20
Shopware 6.5 specific changes:
- Amazon - Fix a problem by saving product preparation after changing the length of variation themes from API

# 3.3.21
Shopware 6.6 specific changes:
- Amazon - Fix a problem by saving product preparation after changing the length of variation themes from API

# 3.3.22
Shopware 6.7 specific changes:
- Amazon - Fix a problem by saving product preparation after changing the length of variation themes from API

# 3.3.23
Shopware 6.5 specific changes:
- Amazon – Product Preparation: Resolve an issue by refreshing the attributes after changing the variation theme.

# 3.3.24
Shopware 6.6 specific changes:
- Amazon – Product Preparation: Resolve an issue by refreshing the attributes after changing the variation theme.

# 3.3.25
Shopware 6.7 specific changes:
- Amazon – Product Preparation: Resolve an issue by refreshing the attributes after changing the variation theme.

# 3.3.26
Shopware 6.5 specific changes:
- Amazon / OTTO - Configuration: Fixed a bug that prevented the order page from opening in the configuration due to a PHP error (Undefined array key “label”).
- Amazon - Sync-Inventory - PHP Warning "Warning: foreach() argument must be of type array|object, null given" fixed
- eBay - Orderimport: If eBay had to pay sales tax, this is now shown in the order comment from magnalister: “Tax: Tax/VAT handled by eBay.”
- Etsy - Product Upload: Added processing profiles. You can now configure profiles and use them during upload
- OTTO - Configuration: Added missing labels and help texts for price calculation for the english language setting

# 3.3.27
Shopware 6.6 specific changes:
- Amazon / OTTO - Configuration: Fixed a bug that prevented the order page from opening in the configuration due to a PHP error (Undefined array key “label”).
- Amazon - Sync-Inventory - PHP Warning "Warning: foreach() argument must be of type array|object, null given" fixed
- eBay - Orderimport: If eBay had to pay sales tax, this is now shown in the order comment from magnalister: “Tax: Tax/VAT handled by eBay.”
- Etsy - Product Upload: Added processing profiles. You can now configure profiles and use them during upload
- OTTO - Configuration: Added missing labels and help texts for price calculation for the english language setting

# 3.3.28
Shopware 6.7 specific changes:
- Amazon / OTTO - Configuration: Fixed a bug that prevented the order page from opening in the configuration due to a PHP error (Undefined array key “label”).
- Amazon - Sync-Inventory - PHP Warning "Warning: foreach() argument must be of type array|object, null given" fixed
- eBay - Orderimport: If eBay had to pay sales tax, this is now shown in the order comment from magnalister: “Tax: Tax/VAT handled by eBay.”
- Etsy - Product Upload: Added processing profiles. You can now configure profiles and use them during upload
- OTTO - Configuration: Added missing labels and help texts for price calculation for the english language setting

# 3.3.29
Shopware 6.5 specific changes:
- Improved order import compatibility with third-party plugins

# 3.3.30
Shopware 6.6 specific changes:
- Improved order import compatibility with third-party plugins

# 3.3.31
Shopware 6.7 specific changes:
- Shopware 6.7.3 compatibility
- Improved order import compatibility with third-party plugins

# 3.3.32
Shopware 6.5 specific changes:
- Amazon: Attribute Matching improvement

# 3.3.33
Shopware 6.6 specific changes:
- Amazon: Attribute Matching improvement

# 3.3.34
Shopware 6.7 specific changes:
- Amazon: Attribute Matching improvement

# 3.3.35
Shopware 6.5 specific changes:
- Amazon – Prepare: Fix an SQL error

# 3.3.36
Shopware 6.6 specific changes:
- Amazon – Prepare: Fix an SQL error

# 3.3.37
Shopware 6.7 specific changes:
- Amazon – Prepare: Fix an SQL error

# 3.3.38
Shopware 6.5 specific changes:
- Amazon: B2B prices can now be updated independently of B2C prices
- Amazon – Preparation: Fix for saving attribute matching when there are more than five products or variations.

# 3.3.39
Shopware 6.6 specific changes:
- Amazon: B2B prices can now be updated independently of B2C prices
- Amazon – Preparation: Fix for saving attribute matching when there are more than five products or variations.

# 3.3.40
Shopware 6.7 specific changes:
- Amazon: B2B prices can now be updated independently of B2C prices
- Amazon – Preparation: Fix for saving attribute matching when there are more than five products or variations.

# 3.3.41
Shopware 6.5 specific changes:
- Amazon - Product Matching: Fixed issue where products could not be matched due to a PHP error

# 3.3.42
Shopware 6.6 specific changes:
- Amazon - Product Matching: Fixed issue where products could not be matched due to a PHP error

# 3.3.43
Shopware 6.7 specific changes:
- Amazon - Product Matching: Fixed issue where products could not be matched due to a PHP error

# 3.3.44
Shopware 6.5 specific changes:
- Fixed bug where the plugin was communicating with the wrong API server, causing price updates, inventory updates, and order status updates to fail

# 3.3.45
Shopware 6.6 specific changes:
- Fixed bug where the plugin was communicating with the wrong API server, causing price updates, inventory updates, and order status updates to fail

# 3.3.46
Shopware 6.7 specific changes:
- Fixed bug where the plugin was communicating with the wrong API server, causing price updates, inventory updates, and order status updates to fail

# 3.3.47
Shopware 6.5 specific changes:
- All Marketplaces - Order Import: Added missing information to the sales channel description: Orders are now explicitly documented as being assigned to this sales channel.
- All Marketplaces: The issue that led to incorrect page rendering and improperly positioned thumbnails during product preparation or upload has been resolved.
- All Marketplaces - Improved Category Import Performance: The category import limit has been optimized from 3,000 to 500 categories per batch
- Amazon - Product Preparation: Fix a problem by saving preparation with long browse nodes
- Amazon – Product Preparation: Fixed a problem where attribute values saved prior to the new attribute matching implementation were not stored correctly
- eBay - Configuration - Credentials: Improved UI for requesting new authentication tokens with clearer instructions
- Kaufland - Product Upload: Fixed processing of product properties and resolved upload errors
- OTTO - Configuration: Fix app authorization issues and add multilingual support for upgrade flow

# 3.3.48
Shopware 6.6 specific changes:
- All Marketplaces - Order Import: Added missing information to the sales channel description: Orders are now explicitly documented as being assigned to this sales channel.
- All Marketplaces: The issue that led to incorrect page rendering and improperly positioned thumbnails during product preparation or upload has been resolved.
- All Marketplaces - Improved Category Import Performance: The category import limit has been optimized from 3,000 to 500 categories per batch
- Amazon - Product Preparation: Fix a problem by saving preparation with long browse nodes
- Amazon – Product Preparation: Fixed a problem where attribute values saved prior to the new attribute matching implementation were not stored correctly
- eBay - Configuration - Credentials: Improved UI for requesting new authentication tokens with clearer instructions
- Kaufland - Product Upload: Fixed processing of product properties and resolved upload errors
- OTTO - Configuration: Fix app authorization issues and add multilingual support for upgrade flow

# 3.3.49
Shopware 6.7 specific changes:
- Shopware 6.7.3.X - Order Import: Fixed duplicate entry error for shipping method technical_name by adding fallback search and version-aware field handling for Shopware 6.5.7+.
- All Marketplaces - Order Import: Added missing information to the sales channel description: Orders are now explicitly documented as being assigned to this sales channel.
- All Marketplaces: The issue that led to incorrect page rendering and improperly positioned thumbnails during product preparation or upload has been resolved.
- All Marketplaces - Improved Category Import Performance: The category import limit has been optimized from 3,000 to 500 categories per batch
- Amazon - Product Preparation: Fix a problem by saving preparation with long browse nodes
- Amazon – Product Preparation: Fixed a problem where attribute values saved prior to the new attribute matching implementation were not stored correctly
- eBay - Configuration - Credentials: Improved UI for requesting new authentication tokens with clearer instructions
- Kaufland - Product Upload: Fixed processing of product properties and resolved upload errors
- OTTO - Configuration: Fix app authorization issues and add multilingual support for upgrade flow

# 3.3.50
Shopware 6.5 specific changes:
- All Marketplaces - Fixed currency texts in configuration: Shop-specific descriptions for order import with different currencies (e.g. Kaufland CZ/CZK)

# 3.3.51
Shopware 6.6 specific changes:
- All Marketplaces - Fixed currency texts in configuration: Shop-specific descriptions for order import with different currencies (e.g. Kaufland CZ/CZK)

# 3.3.52
Shopware 6.7 specific changes:
- Shopware 6.7.4+ compatibility
- All Marketplaces - Fixed currency texts in configuration: Shop-specific descriptions for order import with different currencies (e.g. Kaufland CZ/CZK)

# 3.3.53
Shopware 6.5 specific changes:
- All Marketplaces – Order Import: Fixed issue where address changes created duplicate customer addresses
- Amazon – Product Upload: Ignore images that exceed Amazon’s limit and write the details to the error log
- Amazon: Fix the error "Column 'TopBrowseNode1' cannot be null (1048)" by entering into Amazon tab
- Amazon - Product Preparation: Clickable attribute links in error messages – scrolls to the attribute row and auto-adds optional attributes if needed.
- eBay: Fix For Numeric Variation Values

# 3.3.54
Shopware 6.6 specific changes:
- All Marketplaces – Order Import: Fixed issue where address changes created duplicate customer addresses
- Amazon – Product Upload: Ignore images that exceed Amazon’s limit and write the details to the error log
- Amazon: Fix the error "Column 'TopBrowseNode1' cannot be null (1048)" by entering into Amazon tab
- Amazon - Product Preparation: Clickable attribute links in error messages – scrolls to the attribute row and auto-adds optional attributes if needed.
- eBay: Fix For Numeric Variation Values

# 3.3.55
Shopware 6.7 specific changes:
- All Marketplaces – Order Import: Fixed issue where address changes created duplicate customer addresses
- Amazon – Product Upload: Ignore images that exceed Amazon’s limit and write the details to the error log
- Amazon: Fix the error "Column 'TopBrowseNode1' cannot be null (1048)" by entering into Amazon tab
- Amazon - Product Preparation: Clickable attribute links in error messages – scrolls to the attribute row and auto-adds optional attributes if needed.
- eBay: Fix For Numeric Variation Values
