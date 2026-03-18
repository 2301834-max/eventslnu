#!/bin/bash

# LNU Event Management API - Testing Script
# This script helps you quickly set up and test the Admin API

set -e

echo "================================"
echo "LNU Event Management API Setup"
echo "================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# API base URL (matches docker-compose default)
API_BASE_URL="${API_BASE_URL:-http://localhost:8000/api}"

# Check if Docker is running
echo -e "${BLUE}Checking Docker containers...${NC}"
if docker ps >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Docker is running${NC}"
else
    echo -e "${YELLOW}⚠ Docker is not running. Starting Docker...${NC}"
    docker-compose up -d
    sleep 10
fi

echo ""
echo -e "${BLUE}Setting up test user account...${NC}"

# Create a test user and generate token
RESULT=$(docker exec lnusystem_app php artisan tinker --execute="
\$user = \App\Models\User::firstOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'Admin User',
        'password' => bcrypt('password123'),
    ]
);
\$token = \$user->createToken('admin-token')->plainTextToken;
echo \$token;
" 2>/dev/null | grep -oE '[a-zA-Z0-9|]+$' || echo "")

if [ -z "$RESULT" ]; then
    echo -e "${YELLOW}⚠ Could not create test user. Please create manually.${NC}"
    echo ""
    echo "To create a test user manually, run:"
    echo "docker exec lnusystem_app php artisan tinker"
    echo ""
    echo "Then in the tinker shell:"
    echo "\$user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);"
    echo "\$token = \$user->createToken('token')->plainTextToken;"
    echo "echo \$token;"
    exit 1
fi

TOKEN=$RESULT
echo -e "${GREEN}✓ Test user created/found${NC}"
echo ""
echo -e "${YELLOW}API Token:${NC}"
echo -e "${BLUE}$TOKEN${NC}"
echo ""

# Save token to file for easy reference
echo "$TOKEN" > .api_token

echo -e "${BLUE}Testing API endpoints...${NC}"
echo ""

# Test 1: Get user info
echo "1. Testing authentication..."
RESPONSE=$(curl -s -X GET "$API_BASE_URL/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

if echo "$RESPONSE" | grep -q "\"id\""; then
    echo -e "${GREEN}✓ Authentication working${NC}"
else
    echo -e "${YELLOW}⚠ Authentication issue${NC}"
    echo "Response: $RESPONSE"
fi

echo ""

# Test 2: List events
echo "2. Testing GET /api/events..."
RESPONSE=$(curl -s -X GET "$API_BASE_URL/events?per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

if echo "$RESPONSE" | grep -q "\"success\""; then
    echo -e "${GREEN}✓ Events endpoint working${NC}"
else
    echo -e "${YELLOW}⚠ Events endpoint issue${NC}"
fi

echo ""

# Test 3: Create event
echo "3. Testing POST /api/events..."
RESPONSE=$(curl -s -X POST "$API_BASE_URL/events" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Event ' $(date +%s) '",
    "description": "Test event for API validation",
    "start_date": "2026-06-15T09:00:00Z",
    "end_date": "2026-06-15T17:00:00Z",
    "location": "Test Location",
    "max_participants": 100
  }')

if echo "$RESPONSE" | grep -q "\"id\""; then
    EVENT_ID=$(echo "$RESPONSE" | grep -o '"id":[0-9]*' | head -1 | cut -d: -f2)
    echo -e "${GREEN}✓ Event creation working${NC}"
    echo -e "  Event ID: ${BLUE}$EVENT_ID${NC}"
else
    echo -e "${YELLOW}⚠ Event creation issue${NC}"
    echo "Response: $RESPONSE"
fi

echo ""
echo -e "${YELLOW}================================${NC}"
echo -e "${YELLOW}Setup Complete!${NC}"
echo -e "${YELLOW}================================${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Save your token: $TOKEN"
echo "2. Use it in API requests with: -H \"Authorization: Bearer {token}\""
echo "3. Read API_DOCUMENTATION.md for full endpoint reference"
echo "4. Test with Postman or curl"
echo ""
echo -e "${BLUE}Useful URLs:${NC}"
echo "  API Base: $API_BASE_URL"
echo "  Database: http://localhost:8080 (Adminer)"
echo "  Credentials: admin@example.com / password123"
echo ""
echo -e "${BLUE}Quick test command:${NC}"
echo "curl -H \"Authorization: Bearer $TOKEN\" $API_BASE_URL/events"
echo ""
