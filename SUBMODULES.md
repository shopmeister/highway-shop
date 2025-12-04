# Git Submodules - Plugin Management

## 📦 Submodules Overview

Die folgenden Plugins werden als Git Submodules verwaltet:

1. **ShmBackendTweaks** - Backend UI Tweaks
2. **ShmCustomTasks** - Custom Tasks & SEO Optimization
3. **ShmKindsgutDocuments** - Kindsgut Document Management
4. **ShmOrderPrinter** - Order Printer with Custom Settings
5. **ShopmasterGDriveStockImportSix** - Google Drive Stock Import
6. **ShopmasterZalandoConnectorSix** - Zalando API Connector

---

## 🚀 Quick Start

### Nach dem Clonen des Repos

```bash
git clone git@github.com:shopmeister/highway-shop.git
cd highway-shop

# Submodules initialisieren und pullen
git submodule update --init --recursive
```

### Makefile Commands

```bash
make submodule-update       # Update alle Submodules
make submodule-status       # Status aller Plugins
make submodule-pull         # Pull latest changes
```

---

## 🔧 Workflow: Plugin Updates & Shopware 6.7 Migration

### 1. **Plugin für Update vorbereiten**

```bash
# In Plugin-Ordner wechseln
cd custom/plugins/ShmBackendTweaks

# Aktuellen Branch checken
git branch
# * (HEAD detached at abc123)

# Neuen Branch für Shopware 6.7 erstellen
git checkout -b shopware-6.7

# Oder existierenden Branch auschecken
git checkout main
```

### 2. **Plugin anpassen**

```bash
# Code ändern für Shopware 6.7 Kompatibilität
vim src/Service/MyService.php

# Änderungen im PLUGIN-Repo committen
git add .
git commit -m "feat: Shopware 6.7 compatibility

- Update deprecated API calls
- Fix service registrations for SW 6.7
- Update composer dependencies"

# In PLUGIN-Repo pushen
git push origin shopware-6.7
```

### 3. **Hauptrepo aktualisieren**

```bash
# Zurück ins Hauptrepo
cd /Users/matthias/Repositorys/highway-shop

# Hauptrepo trackt jetzt neuen Commit
git add custom/plugins/ShmBackendTweaks
git commit -m "Update ShmBackendTweaks to Shopware 6.7 version"
git push
```

### 4. **Für alle Plugins wiederholen**

```bash
# Automatisch für alle Plugins einen Branch erstellen
make submodule-branch BRANCH=shopware-6.7

# Oder manuell:
cd custom/plugins/ShmCustomTasks
git checkout -b shopware-6.7
# ... Änderungen machen
git push origin shopware-6.7
```

---

## 📝 Häufige Operationen

### Alle Plugins auf latest Version updaten

```bash
# Alle Submodules auf main/master aktualisieren
git submodule update --remote --merge

# Änderungen im Hauptrepo committen
git add .
git commit -m "Update all plugins to latest versions"
git push
```

### Ein spezifisches Plugin updaten

```bash
# Plugin auschecken
cd custom/plugins/ShmBackendTweaks

# Latest changes holen
git checkout main
git pull origin main

# Zurück ins Hauptrepo
cd /Users/matthias/Repositorys/highway-shop

# Update im Hauptrepo tracken
git add custom/plugins/ShmBackendTweaks
git commit -m "Update ShmBackendTweaks to latest version"
```

### Status aller Plugins prüfen

```bash
# Übersicht aller Submodules
git submodule status

# Oder mit Makefile:
make submodule-status

# Detaillierter Status für jedes Plugin
git submodule foreach 'echo "=== $name ===" && git status'
```

### Branch in allen Plugins erstellen

```bash
# Shopware 6.7 Branch in allen Plugins
git submodule foreach 'git checkout -b shopware-6.7 || git checkout shopware-6.7'

# Oder mit Makefile:
make submodule-branch BRANCH=shopware-6.7
```

### Änderungen in allen Plugins pushen

```bash
# Alle Plugins committen und pushen
git submodule foreach 'git add -A && git commit -m "feat: Shopware 6.7 compatibility" && git push origin HEAD'

# Oder mit Makefile:
make submodule-push MESSAGE="feat: Shopware 6.7 compatibility"
```

---

## 🎯 Shopware 6.7 Migration Workflow

### Schritt 1: Branches erstellen

```bash
# In allen Plugins shopware-6.7 Branch erstellen
make submodule-branch BRANCH=shopware-6.7

# Oder manuell für jedes Plugin:
cd custom/plugins/ShmBackendTweaks
git checkout -b shopware-6.7
cd -
```

### Schritt 2: Plugins anpassen

```bash
# Plugin für Plugin durchgehen
cd custom/plugins/ShmBackendTweaks

# Änderungen machen (deprecated APIs fixen, etc.)
vim src/Service/MyService.php

# Committen
git add .
git commit -m "feat: Shopware 6.7 compatibility"
git push origin shopware-6.7

# Nächstes Plugin
cd ../ShmCustomTasks
# ... repeat
```

### Schritt 3: Hauptrepo aktualisieren

```bash
# Zurück ins Hauptrepo
cd /Users/matthias/Repositorys/highway-shop

# Alle Plugin-Updates tracken
git add custom/plugins/
git commit -m "Update all plugins for Shopware 6.7"
git push
```

### Schritt 4: Nach Testing - Branches mergen

```bash
# In jedem Plugin main-Branch updaten
git submodule foreach '
  git checkout main &&
  git merge shopware-6.7 &&
  git push origin main &&
  git tag v2.0.0-sw67 &&
  git push --tags
'
```

---

## ⚠️ Wichtige Hinweise

### Detached HEAD State

Submodules zeigen **immer auf einen spezifischen Commit**, nicht auf einen Branch:

```bash
cd custom/plugins/ShmBackendTweaks
git status
# HEAD detached at abc123

# Branch erstellen zum Arbeiten
git checkout -b my-feature
# oder existierenden Branch auschecken
git checkout main
```

### Submodule verweisen auf Commit, nicht Branch

```bash
# Hauptrepo sagt:
# "Nutze ShmBackendTweaks Commit abc123"

# NICHT:
# "Nutze ShmBackendTweaks main-Branch"
```

Das bedeutet: **Änderungen müssen aktiv ins Hauptrepo übernommen werden!**

```bash
# Nach Plugin-Update:
cd /Users/matthias/Repositorys/highway-shop
git add custom/plugins/PluginName
git commit -m "Update PluginName to latest version"
```

---

## 🛠️ Troubleshooting

### Submodule nach Clone leer

```bash
git submodule update --init --recursive
```

### Merge-Konflikte in .gitmodules

```bash
# Datei manuell bearbeiten
vim .gitmodules

# Konflikt-Marker entfernen
git add .gitmodules
git commit
```

### Plugin zeigt nicht den erwarteten Code

```bash
# Prüfen auf welchem Commit das Submodule ist
git submodule status

# Plugin auf aktuellen Stand bringen
cd custom/plugins/PluginName
git checkout main
git pull
cd ../..

# Im Hauptrepo committen
git add custom/plugins/PluginName
git commit -m "Update PluginName to latest"
```

### Lokale Änderungen im Submodule gehen verloren

```bash
# ACHTUNG: git submodule update überschreibt lokale Änderungen!

# Vor update: Änderungen committen
cd custom/plugins/PluginName
git add .
git commit -m "WIP: local changes"
git push origin HEAD:my-feature-branch

# Dann erst update
cd ../..
git submodule update
```

---

## 📚 Weiterführende Infos

### Git Submodule Basics

```bash
# Submodule hinzufügen
git submodule add <repo-url> <path>

# Submodules initialisieren
git submodule init

# Submodules updaten
git submodule update

# Submodule entfernen
git submodule deinit <path>
git rm <path>
```

### Nützliche Aliases

```bash
# In ~/.gitconfig
[alias]
    sup = submodule update --remote --merge
    spush = submodule foreach 'git push origin HEAD'
    sst = submodule status
```

---

## 🎓 Best Practices

1. **Immer Branch erstellen** für Änderungen im Submodule
2. **Commits im Plugin-Repo** machen, nicht im Hauptrepo
3. **Nach Plugin-Update** immer im Hauptrepo committen
4. **Vor Shopware-Upgrade** eigenen Branch erstellen (`shopware-6.7`)
5. **Nach Testing** Feature-Branch in main mergen
6. **Tags verwenden** für Versionen (`v2.0.0-sw67`)

---

## 🚀 Makefile Commands

Siehe `make help` für alle verfügbaren Commands:

```bash
make submodule-update        # Update all submodules to latest
make submodule-status        # Show status of all submodules
make submodule-pull          # Pull latest changes in all submodules
make submodule-branch        # Create branch in all submodules
make submodule-push          # Push all submodule changes
make submodule-sync          # Sync submodule URLs from .gitmodules
```
