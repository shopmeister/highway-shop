# Highway Shop - Projekt Status

**Letzte Aktualisierung:** 2025-12-04

---

## ✅ Abgeschlossen

### 1. Git Repository Setup
- ✅ Repository initialisiert und mit GitHub verbunden
- ✅ Remote: `git@github.com:shopmeister/highway-shop.git`
- ✅ Initial Commit mit 8.910 Dateien (Shopware 6 Base + Plugins + Theme)
- ✅ `.gitignore` für Shopware 6 Production erstellt

### 2. Git Submodules für Plugin-Management
**6 eigene Plugins als Submodules konvertiert:**
- ✅ ShmBackendTweaks → `git@github.com:shopmeister/ShmBackendTweaks.git`
- ✅ ShmCustomTasks → `git@github.com:shopmeister/ShmCustomTasks.git`
- ✅ ShmKindsgutDocuments → `git@github.com:shopmeister/ShmKindsgutDocuments.git`
- ✅ ShmOrderPrinter → `git@github.com:shopmeister/ShmOrderPrinter.git`
- ✅ ShopmasterGDriveStockImportSix → `git@github.com:shopmeister/ShopmasterGDriveStockImportSix.git`
- ✅ ShopmasterZalandoConnectorSix → `git@github.com:shopmeister/ShopmasterZalandoConnectorSix.git`

**Vorteile:**
- Änderungen können direkt in Plugin-Repos gepusht werden
- Einfach Feature-Branches erstellen (z.B. `shopware-6.7`)
- Saubere Versionskontrolle pro Plugin

### 3. Docker Development Setup
- ✅ Produktionsreife `docker-compose.yml` erstellt
  - Dockware/dev:6.6.9.0 (PHP 8.2, Apache)
  - MariaDB 10.11 (separater Container)
  - phpMyAdmin (Port 8089)
  - Mailhog (Port 8025)
  - Xdebug vorkonfiguriert (Port 9003)
- ✅ MySQL Custom Config mit Performance-Tuning
- ✅ Automatischer DB-Import Setup
  - 2.6GB Produktions-Dump bereit: `docker/mysql/init/db_c1w7db1_2025-12-04_03-19.sql`
  - Import erfolgt automatisch beim ersten Start

### 4. Dokumentation
- ✅ **SUBMODULES.md** - Kompletter Workflow-Guide für Plugin-Updates & SW 6.7 Migration
- ✅ **DOCKER_SETUP.md** - Docker Quick Start & Troubleshooting
- ✅ **docker/README.md** - Detaillierte Docker-Dokumentation
- ✅ **docker/mysql/init/README.md** - DB-Import Anleitung
- ✅ **Makefile** - Helper-Commands für Docker & Submodules
- ✅ **docker-setup.sh** - Interaktives Setup-Script

### 5. Makefile Commands
**Docker Management:**
```bash
make up              # Container starten
make down            # Container stoppen
make logs            # Logs verfolgen
make shell           # Shell im App-Container
make db-shell        # MySQL Shell
make db-import       # DB-Dump importieren
make db-backup       # DB-Backup erstellen
make db-fix-urls     # URLs auf localhost:8088 ändern
make cache           # Shopware-Cache leeren
```

**Submodule Management:**
```bash
make submodule-update                    # Alle Plugins updaten
make submodule-status                    # Status aller Plugins
make submodule-branch BRANCH=name        # Branch in allen Plugins erstellen
make submodule-push MESSAGE="msg"        # Änderungen in alle Plugins pushen
make submodule-init                      # Nach Clone: Submodules initialisieren
```

---

## 🎯 Nächste Schritte

### Sofort möglich:
1. **Docker Container starten**
   ```bash
   make up
   # oder
   docker compose up -d
   ```

2. **DB-Import Status prüfen** (~5-10 Min für 2.6GB)
   ```bash
   docker logs -f highway-shop-db
   ```

3. **URLs für Localhost anpassen**
   ```bash
   make db-fix-urls
   ```

4. **Shop öffnen**
   - Frontend: http://localhost:8088
   - Admin: http://localhost:8088/admin
   - phpMyAdmin: http://localhost:8089
   - Mailhog: http://localhost:8025

### Für Shopware 6.7 Migration:
```bash
# Branch in allen Plugins erstellen
make submodule-branch BRANCH=shopware-6.7

# Plugin anpassen
cd custom/plugins/ShmBackendTweaks
# ... Code ändern
git commit -m "feat: SW 6.7 compatibility"
git push origin shopware-6.7

# Hauptrepo aktualisieren
cd ../../..
git add custom/plugins/ShmBackendTweaks
git commit -m "Update ShmBackendTweaks to SW 6.7"
```

---

## 📂 Projekt-Struktur

```
highway-shop/
├── .gitmodules                  # Submodule-Konfiguration
├── docker-compose.yml           # Docker Services
├── Makefile                     # Helper Commands
├── docker-setup.sh              # Interaktives Setup
│
├── SUBMODULES.md               # Plugin-Management Guide
├── DOCKER_SETUP.md             # Docker Quick Start
├── PROJECT_STATUS.md           # Dieser Status (du bist hier)
│
├── docker/
│   ├── README.md               # Docker Details
│   └── mysql/
│       ├── conf.d/             # MySQL Config
│       │   └── custom.cnf
│       └── init/               # DB Auto-Import
│           ├── README.md
│           └── db_c1w7db1_2025-12-04_03-19.sql (2.6GB)
│
├── custom/
│   └── plugins/                # 6 Plugins als Submodules
│       ├── ShmBackendTweaks/
│       ├── ShmCustomTasks/
│       ├── ShmKindsgutDocuments/
│       ├── ShmOrderPrinter/
│       ├── ShopmasterGDriveStockImportSix/
│       └── ShopmasterZalandoConnectorSix/
│
├── config/                      # Shopware Config
├── composer.json
└── composer.lock
```

---

## 🔍 Wichtige Erkenntnisse

### Git Submodules
- **Detached HEAD:** Submodules zeigen immer auf spezifischen Commit, nicht Branch
- **Nach Clone:** `git submodule update --init --recursive` erforderlich
- **Nach Plugin-Update:** Änderungen im Hauptrepo committen!

### Docker
- **Port 8088:** Shopware Frontend/Admin (nicht 80, da bereits belegt)
- **Port 3307:** MariaDB (nicht 3306, da bereits belegt)
- **DB-Import:** Erfolgt nur beim ersten Volume-Start
- **Reset:** `make down-volumes` löscht ALLE Daten!

### Shopware 6.6 Spezifika
- `parent="shopware.repository"` Pattern ist in 6.6 ENTFERNT
- Event Dispatcher Service-ID: `event_dispatcher` (nicht Interface)
- Vue.js 3 Administration (von 2.x)
- PHP 8.2 minimum

---

## 📋 Backup & Wiederherstellung

### Plugin-Backup
Backup liegt in: `/tmp/highway-shop-plugin-backup/`
- Enthält alle 6 Plugins vor Submodule-Konvertierung
- Gültig bis System-Neustart

### DB-Backup erstellen
```bash
make db-backup
# Speichert in: backups/backup_YYYYMMDD_HHMMSS.sql
```

### Nach Clone wiederherstellen
```bash
git clone git@github.com:shopmeister/highway-shop.git
cd highway-shop
git submodule update --init --recursive
cp your-dump.sql docker/mysql/init/
make up
```

---

## 🐛 Bekannte Issues

### Docker Desktop startet langsam
- Kann 1-2 Minuten dauern
- Prüfen: Docker Icon in Menubar
- Fallback: `open -a Docker` und manuell warten

### Ports bereits belegt
- Port 8088 statt 80 (anderer Shopware-Container läuft)
- Port 3307 statt 3306 (anderer MySQL-Container läuft)
- Lösung: Andere Container stoppen oder Ports in docker-compose.yml ändern

### Submodule "dirty"
```bash
# In Submodule
cd custom/plugins/PluginName
git status
# Änderungen committen oder verwerfen
```

---

## 📞 Support

- **Makefile:** `make help` zeigt alle Commands
- **Submodules:** Siehe `SUBMODULES.md`
- **Docker:** Siehe `DOCKER_SETUP.md`
- **GitHub Issues:** https://github.com/shopmeister/highway-shop/issues

---

**Letzter Commit:** `0aaaa1f` - Convert plugins to Git submodules
**Branch:** `main`
**Remote:** `git@github.com:shopmeister/highway-shop.git`
