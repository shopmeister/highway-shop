# 2.2.1
- Fehlerbehebung: Standardwert für `product.min_purchase` in `Swkweb\HideSoldoutProducts\Core\Content\Product\DataAbstractionLayer\ProductAvailabilityUpdater` definieren
- Fehlerbehebung: `shopware/elasticsearch` in den richtigen Versionen voraussetzen

# 2.2.0
- Feature: Möglichkeit hinzugefügt Kategorien Ausnahmen zu definieren
- Fehlerbehebung: Berücksichtige Verkaufskanal Konfiguration für das Produkt Erlebniswelten Element
- Refaktorisierung: Interne Optimierungen

# 2.1.1
- Kompatibilität: Shopware 6.6
- Fehlerbehebung: Korrekte Implementierung von `ElasticsearchProductDefinition::buildTermQuery` um wieder Produkte in allen Shopware 6.5.x Versionen in der Suche zu finden

# 2.1.0
- Feature: Elasticsearch Kompatibilität (Nach dem Update oder der Installation muss der ES Index zurückgesetzt werden und neu aufgebaut werden aufgrund eines geänderten Mappings)

# 2.0.0
- Kompatibilität: Shopware 6.5
- Refaktorisierung: Entferne deprecations

# 1.3.3
- Fehlerbehebung: product.indexer wird nun bei der Plugin Aktivierung anstatt der Installation ausgeführt

# 1.3.2
- Kompatibilität: Entferne unnötigen Kompatibilitäts Code für Shopware 6.4.0
- Fehlerbehebung: product.indexer wird nun bei der Plugin Installation ausgeführt

# 1.3.1
- Kompatibilität: Baue JavaScript neu für Kompatibilität mit Shopware 6.4.10

# 1.3.0
- Kompatibilität: Erhöhe minimale Shopware Version auf 6.4.0.0
- Refaktorisierung: Verwende Criteria Events für Cross-Selling Elemente
- Fehlerbehebung: Entferne Workaround für AntiJoinFilter für korrekte Vererbung der Verkaufskanäle

# 1.2.2
- Berücksichtige Verkaufskanal Konfiguration für Bereiche in denen Produkte ausgeblendet werden sollen

# 1.2.1
- Rufe in Shopware 6.4 nicht mehr die entfernte Methode des productStreamConditionService auf

# 1.2.0
- Shopware 6.4 Kompatibilität

# 1.1.0
- Kompatibilitäts Anpassungen für Shopware 6.3

# 1.0.0
- Erstveröffentlichung für Shopware 6
