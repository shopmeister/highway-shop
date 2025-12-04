# 2.2.1
- Bugfix: Set default value for `product.min_purchase` in `Swkweb\HideSoldoutProducts\Core\Content\Product\DataAbstractionLayer\ProductAvailabilityUpdater`
- Bugfix: Require `shopware/elasticsearch` in the correct versions

# 2.2.0
- Feature: Add possibility to exempt categories
- Bugfix: Consider sales channel configuration for the product cms element
- Refactor: Internal optimizations

# 2.1.1
- Compatibility: Shopware 6.6
- Bugfix: Properly implement `ElasticsearchProductDefinition::buildTermQuery` to find products in search again with Elasticsearch enabled in all Shopware 6.5.x version

# 2.1.0
- Feature: Elasticsearch compatibility (after updating/installing the ES index has to be reset and reindexed due to changed mapping)

# 2.0.0
- Compatibility: Shopware 6.5
- Refactor: Remove deprecations

# 1.3.3
- Bugfix: Run product.indexer on plugin activation and not on plugin installation

# 1.3.2
- Bugfix: Run product.indexer on plugin installation
- Compatibility: Remove unneeded compatibility code for Shopware 6.4.0

# 1.3.1
- Compatibility: Rebuild JavaScript for Shopware 6.4.10 compatibility

# 1.3.0
- Compat: Increase minimum Shopware version to 6.4.0.0
- Refactor: Use criteria events for cross-selling elements
- Bugfix: Remove workaround for AntiJoinFilter for correct inheritance of the sales channels

# 1.2.2
- Consider sales channel configuration for places where the products should be hidden

# 1.2.1
- Don't call removed productStreamConditionService method in 6.4

# 1.2.0
- Shopware 6.4 compatibility

# 1.1.0
- Compatibility changes for Shopware 6.3

# 1.0.0
- First Shopware 6 release
