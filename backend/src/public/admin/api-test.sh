#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# API endpoints to test
endpoints=(
    "/api/employees"
    "/api/departments"
    "/api/positions"
    "/api/performances"
    "/api/payroll"
    "/api/leaves"
    "/api/trainings"
    "/api/tasks"
)

# Base URL - adjust this to your local setup
BASE_URL="http://localhost/qlnhansu_V2/backend/src/public"

# Function to test an endpoint
test_endpoint() {
    local endpoint=$1
    local full_url="${BASE_URL}${endpoint}"
    
    echo -e "${YELLOW}Testing ${endpoint}...${NC}"
    
    # Make the request and capture response and status code
    response=$(curl -s -w "\n%{http_code}" "$full_url")
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    # Check if the status code is successful (2xx)
    if [[ $status_code =~ ^2[0-9]{2}$ ]]; then
        echo -e "${GREEN}✓ Success (${status_code})${NC}"
        echo "Response:"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}✗ Error (${status_code})${NC}"
        echo "Response:"
        echo "$body"
    fi
    
    echo "----------------------------------------"
}

# Main test function
run_tests() {
    echo -e "${YELLOW}Starting API tests...${NC}"
    echo "Base URL: ${BASE_URL}"
    echo "----------------------------------------"
    
    for endpoint in "${endpoints[@]}"; do
        test_endpoint "$endpoint"
    done
    
    echo -e "${YELLOW}All tests completed.${NC}"
}

# Run the tests
run_tests 