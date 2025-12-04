# Highway Shop - Docker Development Setup

## Quick Start

### 1. DB-Dump vorbereiten

Kopiere deinen lokalen SQL-Dump in den Import-Ordner:

```bash
# Unkomprimiert
cp /pfad/zum/highway-shop-dump.sql docker/mysql/init/

# Oder komprimiert
cp /pfad/zum/highway-shop-dump.sql.gz docker/mysql/init/
```

### 2. Container starten

```bash
# Alle Services starten
docker-compose up -d

# Logs verfolgen
docker-compose logs -f
```

### 3. Warten auf DB-Import

Der initiale Import kann 5-10 Minuten dauern. Status prüfen:

```bash
docker logs -f highway-shop-db
```

### 4. Shop aufrufen

- **Frontend:** http://localhost:8088
- **Admin:** http://localhost:8088/admin
- **phpMyAdmin:** http://localhost:8089
- **Mailhog:** http://localhost:8025

## Verfügbare Services

| Service | Container | Port | Beschreibung |
|---------|-----------|------|--------------|
| app | highway-shop-app | 8088 | Shopware 6.6.9 (PHP 8.2) |
| db | highway-shop-db | 3307 | MariaDB 10.11 |
| phpmyadmin | highway-shop-phpmyadmin | 8089 | DB-Management UI |
| mailhog | highway-shop-mailhog | 8025 | E-Mail Testing |

## Nützliche Commands

### Container Management
```bash
# Alle Services starten
docker-compose up -d

# Services stoppen
docker-compose stop

# Services + Volumes löschen (ACHTUNG: DB-Daten weg!)
docker-compose down -v

# Nur Services löschen (DB bleibt erhalten)
docker-compose down

# Logs anzeigen
docker-compose logs -f app
docker-compose logs -f db
```

### Shell-Zugriff
```bash
# In App-Container (für Shopware CLI)
docker exec -it highway-shop-app bash

# In DB-Container
docker exec -it highway-shop-db bash
```

### Shopware Commands (im App-Container)
```bash
# Shell öffnen
docker exec -it highway-shop-app bash

# Im Container:
php bin/console cache:clear
php bin/console plugin:refresh
php bin/console plugin:install --activate PluginName
php bin/console theme:compile
```

### Database Management
```bash
# Dump erstellen
docker exec highway-shop-db mysqldump -uroot -proot shopware > backup_$(date +%Y%m%d).sql

# Manueller Import
docker exec -i highway-shop-db mysql -uroot -proot shopware < dump.sql

# Komprimierten Dump importieren
gunzip < dump.sql.gz | docker exec -i highway-shop-db mysql -uroot -proot shopware

# Direkter MySQL-Zugriff
docker exec -it highway-shop-db mysql -uroot -proot shopware
```

### DB-Import mit Host-Tool (TablePlus, Sequel Pro, etc.)
```
Host: 127.0.0.1
Port: 3307
User: shopware (oder root)
Password: shopware (oder root)
Database: shopware
```

## Produktions-Dump für Lokal anpassen

Nach Import eines Produktions-Dumps:

```bash
# In DB-Container
docker exec -it highway-shop-db mysql -uroot -proot shopware

# Oder mit phpMyAdmin: http://localhost:8089
```

SQL-Fixes ausführen:

```sql
-- URLs auf localhost ändern
UPDATE sales_channel_domain SET url = 'http://localhost:8088';

-- System Config anpassen
UPDATE system_config
SET configuration_value = '"http://localhost:8088"'
WHERE configuration_key = 'core.basicInformation.shopUrl';

-- Optional: Admin-User Passwort zurücksetzen
-- Passwort: shopware
UPDATE user
SET password = '$2y$10$7OuQlE8AehYk/pWR4CvGP.BVCqQZC8w5Iq1P5jtdKJU8xlQMZGIAa'
WHERE username = 'admin';
```

## Troubleshooting

### Container startet nicht
```bash
# Logs prüfen
docker-compose logs

# Ports belegt?
lsof -i :8088
lsof -i :3307

# Volumes neu aufbauen
docker-compose down -v
docker-compose up -d
```

### DB-Import funktioniert nicht
```bash
# Prüfen ob Datei im richtigen Ordner
ls -lh docker/mysql/init/

# Volume löschen (Import erfolgt nur bei fresh volume)
docker-compose down -v

# Neu starten und Logs verfolgen
docker-compose up -d
docker logs -f highway-shop-db
```

### Shopware zeigt Fehler
```bash
# Cache leeren
docker exec -it highway-shop-app php bin/console cache:clear

# Permissions prüfen
docker exec -it highway-shop-app chown -R www-data:www-data /var/www/html/var

# Datenbank-Migrations prüfen
docker exec -it highway-shop-app php bin/console database:migrate --all
```

### Performance-Probleme
```bash
# Mehr RAM für MariaDB (docker-compose.yml)
MYSQL_INNODB_BUFFER_POOL_SIZE: 4G  # statt 2G

# Docker Desktop RAM erhöhen
# Settings > Resources > Memory: mindestens 8GB
```

## Xdebug Setup (PHPStorm)

1. **PHPStorm > Preferences > PHP > Servers**
   - Name: `highway-shop`
   - Host: `localhost`
   - Port: `8088`
   - Debugger: `Xdebug`
   - Path Mappings:
     - `/Users/matthias/Repositorys/highway-shop` → `/var/www/html`

2. **Start Listening** (Phone Icon in PHPStorm)

3. **Browser:** [Xdebug Helper Chrome Extension](https://chrome.google.com/webstore/detail/xdebug-helper/)

## Environment Files

- `.env.local` - Produktion (nicht committen!)
- `.env.docker` - Docker Dev (wird ignoriert)
- `.env.dist` - Template

Für Docker: `.env.docker` → `.env` kopieren im Container (automatisch via dockware)

## Backup vor Experimenten

```bash
# Container + Volumes speichern
docker-compose stop
docker run --rm -v highway-shop_db_data:/data -v $(pwd):/backup ubuntu tar czf /backup/db_backup.tar.gz /data

# Wiederherstellen
docker run --rm -v highway-shop_db_data:/data -v $(pwd):/backup ubuntu tar xzf /backup/db_backup.tar.gz -C /
```
