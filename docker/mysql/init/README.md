# MySQL Database Import

## Automatischer Import beim ersten Start

Alle `.sql` oder `.sql.gz` Dateien in diesem Ordner werden automatisch beim ersten Start des DB-Containers importiert.

## Verwendung:

1. **SQL-Dump hierher kopieren:**
   ```bash
   # Unkomprimiert
   cp /pfad/zum/dump.sql docker/mysql/init/

   # Oder komprimiert (wird automatisch entpackt)
   cp /pfad/zum/dump.sql.gz docker/mysql/init/
   ```

2. **Container neu aufbauen:**
   ```bash
   # Volumes löschen (wichtig, sonst wird nicht neu importiert)
   docker-compose down -v

   # Container starten
   docker-compose up -d
   ```

3. **Import-Status prüfen:**
   ```bash
   docker logs highway-shop-db
   ```

## Wichtig:

- Der Import erfolgt NUR beim **ersten Start** eines neuen Volumes
- Existierende Datenbanken werden NICHT überschrieben
- Bei erneutem Import: Volume mit `docker-compose down -v` löschen
- Große Dumps (>500MB) können 5-10 Minuten dauern

## Produktions-Dump Preparation:

Wenn der Dump von Produktion kommt, vorher bereinigen:

```sql
-- Änderungen für lokale Entwicklung
UPDATE sales_channel_domain SET url = 'http://localhost:8088';
UPDATE system_config SET configuration_value = '"http://localhost:8088"' WHERE configuration_key = 'core.basicInformation.shopUrl';
```

## Alternativer manueller Import:

```bash
# In laufenden Container
docker exec -i highway-shop-db mysql -uroot -proot shopware < dump.sql

# Oder mit Kompression
gunzip < dump.sql.gz | docker exec -i highway-shop-db mysql -uroot -proot shopware
```
