# Highway Shop - Docker Setup Guide

## ✨ Verbesserungen

### 1. **Produktions-reife docker-compose.yml**
- ✅ Separater DB-Container (MariaDB 10.11)
- ✅ Dockware/dev:6.6.9.0 mit PHP 8.2
- ✅ Xdebug vorkonfiguriert (Port 9003)
- ✅ Healthchecks für DB-Container
- ✅ Performance-Tuning (2GB Buffer Pool)
- ✅ phpMyAdmin für DB-Management (Port 8089)
- ✅ Mailhog für E-Mail-Testing (Port 8025)
- ✅ Proper networking mit bridge-driver

### 2. **Automatischer DB-Import**
- SQL-Dump wird beim ersten Start automatisch importiert
- Unterstützt `.sql` und `.sql.gz` Formate
- Import-Status via `docker logs highway-shop-db` prüfbar
- Dein 2.6GB Dump ist bereits in `docker/mysql/init/` platziert

### 3. **MySQL Custom Config**
- UTF8MB4 Collation (Shopware-Standard)
- Optimierte Buffer-Größen
- Slow-Query-Log aktiviert
- SQL-Mode Shopware-kompatibel

### 4. **Makefile für einfache Bedienung**
```bash
make help           # Alle Commands anzeigen
make up             # Container starten
make logs           # Logs verfolgen
make shell          # Shell im App-Container
make db-import      # Manueller DB-Import
make db-fix-urls    # URLs auf localhost:8088 ändern
make cache          # Shopware-Cache leeren
```

### 5. **Setup-Script**
```bash
./docker-setup.sh   # Interaktives Setup mit Status-Checks
```

### 6. **Umgebungs-Konfiguration**
- `.env.docker` für lokale Entwicklung vorbereitet
- `.env.local` (Produktion) wird durch .gitignore geschützt
- Alle Secrets ausgeschlossen

---

## 🚀 Quick Start

### Schritt 1: DB-Dump ist bereits platziert
```bash
ls -lh docker/mysql/init/
# db_c1w7db1_2025-12-04_03-19.sql (2.6G) ✓
```

### Schritt 2: Container starten
```bash
# Option A: Mit Makefile
make up

# Option B: Mit Setup-Script
./docker-setup.sh

# Option C: Direkt mit Docker Compose
docker-compose up -d
```

### Schritt 3: Import-Status prüfen (2.6GB = ~5-10 Min)
```bash
docker logs -f highway-shop-db

# Warten auf:
# [Note] mysqld: ready for connections.
```

### Schritt 4: URLs für Localhost anpassen
```bash
make db-fix-urls
# Oder manuell:
docker exec -i highway-shop-db mysql -uroot -proot shopware -e "
  UPDATE sales_channel_domain SET url = 'http://localhost:8088';
  UPDATE system_config SET configuration_value = '\"http://localhost:8088\"' WHERE configuration_key = 'core.basicInformation.shopUrl';
"
```

### Schritt 5: Shop öffnen
- **Frontend:** http://localhost:8088
- **Admin:** http://localhost:8088/admin
- **phpMyAdmin:** http://localhost:8089 (user: `root`, pw: `root`)
- **Mailhog:** http://localhost:8025

---

## 📋 Service-Übersicht

| Service | Container | Host Port | Intern | Beschreibung |
|---------|-----------|-----------|--------|--------------|
| app | highway-shop-app | 8088 | 80 | Shopware 6.6.9 (PHP 8.2, Apache) |
| app | highway-shop-app | 9999 | 9999 | Xdebug Remote Debugging |
| app | highway-shop-app | 8888 | 22 | SSH (optional) |
| db | highway-shop-db | 3307 | 3306 | MariaDB 10.11 |
| phpmyadmin | highway-shop-phpmyadmin | 8089 | 80 | phpMyAdmin Web UI |
| mailhog | highway-shop-mailhog | 8025 | 8025 | Mailhog Web UI |
| mailhog | highway-shop-mailhog | 1025 | 1025 | SMTP Server |

---

## 🛠️ Häufige Aufgaben

### Cache leeren
```bash
make cache
# oder
docker exec -it highway-shop-app php bin/console cache:clear
```

### Admin-Passwort zurücksetzen
```bash
make admin-pw
# Login: admin / shopware
```

### DB-Backup erstellen
```bash
make db-backup
# Speichert in: backups/backup_YYYYMMDD_HHMMSS.sql
```

### Shell-Zugriff
```bash
# Shopware App Container
make shell
docker exec -it highway-shop-app bash

# MySQL Shell
make db-shell
docker exec -it highway-shop-db mysql -uroot -proot shopware
```

### Container neu starten
```bash
make restart
# oder
docker-compose restart
```

### Alles löschen und neu starten
```bash
make down-volumes  # ⚠️ Löscht DB-Daten!
make up
```

---

## 🔍 Troubleshooting

### Container startet nicht
```bash
# Logs prüfen
docker-compose logs

# Port 8088 schon belegt?
lsof -i :8088

# Container-Status
docker ps -a
```

### DB-Import hängt
```bash
# Container neu starten (Import wird fortgesetzt)
docker-compose restart db

# Oder manuell importieren:
docker exec -i highway-shop-db mysql -uroot -proot shopware < docker/mysql/init/db_c1w7db1_2025-12-04_03-19.sql
```

### Shopware-Fehler nach Import
```bash
# URLs anpassen
make db-fix-urls

# Cache leeren
make cache

# Migrations prüfen
docker exec -it highway-shop-app php bin/console database:migrate --all

# Permissions
docker exec -it highway-shop-app chown -R www-data:www-data /var/www/html/var
```

### Xdebug funktioniert nicht (PHPStorm)
1. **Settings → PHP → Servers**
   - Name: `highway-shop`
   - Host: `localhost`
   - Port: `8088`
   - Path Mappings: `/Users/matthias/Repositorys/highway-shop` → `/var/www/html`

2. **Start Listening** (Phone Icon)

3. **Browser:** [Xdebug Helper Extension](https://chrome.google.com/webstore/detail/xdebug-helper/)

---

## 📦 Volumes

```bash
# Volumes anzeigen
docker volume ls | grep highway

# Volume-Größe prüfen
docker system df -v

# Ungenutztes aufräumen
docker system prune -a --volumes
```

Volumes:
- `highway-shop_db_data` - MariaDB Daten (persistent)
- `highway-shop_app_cache` - Shopware Cache (persistent)
- `highway-shop_app_log` - Shopware Logs (persistent)

---

## 🎯 Nächste Schritte

1. ✅ Container starten: `make up`
2. ⏳ Warten auf DB-Import (5-10 Min)
3. ✅ URLs anpassen: `make db-fix-urls`
4. ✅ Shop testen: http://localhost:8088
5. ✅ Admin-Login testen: http://localhost:8088/admin

**Bei Problemen:**
- Logs checken: `make logs`
- Discord/Slack Support kontaktieren mit Logs

---

## 📚 Weitere Ressourcen

- **Docker Docs:** [docker/README.md](docker/README.md)
- **MySQL Init:** [docker/mysql/init/README.md](docker/mysql/init/README.md)
- **Makefile Commands:** `make help`
- **Dockware Docs:** https://docs.dockware.io/
