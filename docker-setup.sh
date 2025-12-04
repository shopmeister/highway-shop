#!/bin/bash
set -e

echo "=================================================="
echo "  Highway Shop - Docker Setup"
echo "=================================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker Desktop first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"

# Check for SQL dump
DUMP_FILES=$(ls docker/mysql/init/*.sql docker/mysql/init/*.sql.gz 2>/dev/null || true)

if [ -z "$DUMP_FILES" ]; then
    echo ""
    echo -e "${YELLOW}⚠️  No SQL dump found in docker/mysql/init/${NC}"
    echo ""
    echo "Please copy your database dump:"
    echo "  cp /path/to/your-dump.sql docker/mysql/init/"
    echo ""
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo -e "${GREEN}✓ SQL dump found:${NC}"
    for file in $DUMP_FILES; do
        SIZE=$(du -h "$file" | cut -f1)
        echo "  - $(basename $file) ($SIZE)"
    done
fi

# Check if containers are already running
if docker ps | grep -q "highway-shop"; then
    echo ""
    echo -e "${YELLOW}⚠️  Containers are already running${NC}"
    read -p "Restart? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Stopping containers..."
        docker-compose down
    else
        exit 0
    fi
fi

# Start containers
echo ""
echo "Starting Docker containers..."
docker-compose up -d

echo ""
echo -e "${GREEN}✓ Containers started!${NC}"
echo ""
echo "Services are available at:"
echo "  🌐 Shop Frontend:  http://localhost:8088"
echo "  🔧 Admin Panel:    http://localhost:8088/admin"
echo "  🗄️  phpMyAdmin:     http://localhost:8089"
echo "  📧 Mailhog:        http://localhost:8025"
echo ""

if [ -n "$DUMP_FILES" ]; then
    echo -e "${YELLOW}⏳ Database import is running in background...${NC}"
    echo "   This may take 5-10 minutes for large dumps."
    echo ""
    echo "Check import progress:"
    echo "  docker logs -f highway-shop-db"
    echo ""

    # Wait for DB to be ready
    echo "Waiting for database to be ready..."
    sleep 10

    # Check if import is complete
    for i in {1..30}; do
        if docker logs highway-shop-db 2>&1 | grep -q "ready for connections"; then
            echo -e "${GREEN}✓ Database is ready!${NC}"
            break
        fi
        echo -n "."
        sleep 2
    done
    echo ""
fi

echo ""
echo "Useful commands:"
echo "  make logs        - Show all logs"
echo "  make shell       - Open shell in app container"
echo "  make db-shell    - Open MySQL shell"
echo "  make cache       - Clear Shopware cache"
echo "  make help        - Show all available commands"
echo ""
echo -e "${GREEN}Setup complete! 🚀${NC}"
