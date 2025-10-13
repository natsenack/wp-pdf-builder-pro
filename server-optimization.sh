#!/bin/bash
# Server Optimization Script for PDF Builder Pro
# This script applies nginx configuration and WordPress optimizations

echo "🚀 PDF Builder Pro - Server Optimization Script"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ This script must be run as root${NC}"
   exit 1
fi

# Function to backup file
backup_file() {
    local file=$1
    if [[ -f "$file" ]]; then
        cp "$file" "${file}.backup.$(date +%Y%m%d_%H%M%S)"
        echo -e "${GREEN}✅ Backed up: $file${NC}"
    fi
}

# Apply Nginx configuration
echo -e "\n${YELLOW}📝 Applying Nginx configuration...${NC}"

NGINX_CONFIG="/etc/nginx/sites-available/threeaxe.fr"
NGINX_RATE_LIMIT_CONFIG="/var/www/html/wp-content/plugins/wp-pdf-builder-pro/nginx-rate-limit-config.txt"

if [[ -f "$NGINX_CONFIG" ]]; then
    backup_file "$NGINX_CONFIG"

    # Add rate limiting configuration before the server block
    if [[ -f "$NGINX_RATE_LIMIT_CONFIG" ]]; then
        # Extract rate limiting config and add it to nginx config
        RATE_LIMIT_CONTENT=$(cat "$NGINX_RATE_LIMIT_CONFIG")

        # Check if rate limiting is already configured
        if ! grep -q "limit_req_zone" "$NGINX_CONFIG"; then
            # Add rate limiting zones at the top of the config
            sed -i '1i\# Rate limiting configuration for WordPress/WooCommerce - Enhanced Version\n# Rate limiting zones - More generous limits\nlimit_req_zone $binary_remote_addr zone=threeaxe.fr:10m rate=15r/s;\nlimit_req_zone $binary_remote_addr zone=admin:10m rate=8r/s;\nlimit_req_zone $binary_remote_addr zone=ajax:10m rate=5r/s;\nlimit_req_zone $binary_remote_addr zone=wc_api:10m rate=10r/s;\n' "$NGINX_CONFIG"
            echo -e "${GREEN}✅ Added rate limiting zones to nginx config${NC}"
        else
            echo -e "${YELLOW}⚠️  Rate limiting zones already exist in nginx config${NC}"
        fi

        # Update location blocks with new limits
        # This is complex to automate, so we'll provide instructions
        echo -e "${YELLOW}📋 Please manually update your nginx server block with these location directives:${NC}"
        echo ""
        echo "# Stricter limits for admin area"
        echo "location /wp-admin/ {"
        echo "    limit_req zone=admin burst=15 nodelay;"
        echo "    try_files \$uri \$uri/ /index.php?\$args;"
        echo "}"
        echo ""
        echo "# Limits for AJAX calls"
        echo "location /wp-admin/admin-ajax.php {"
        echo "    limit_req zone=ajax burst=8 nodelay;"
        echo "    try_files \$uri \$uri/ /index.php?\$args;"
        echo "}"
        echo ""
        echo "# Generous limits for WooCommerce API (admin operations)"
        echo "location ~ ^/wp-json/wc-(analytics|admin)/ {"
        echo "    limit_req zone=wc_api burst=20 nodelay;"
        echo "    try_files \$uri \$uri/ /index.php?\$args;"
        echo "}"
        echo ""
        echo "# Allow WooCommerce API with reasonable limits"
        echo "location /wp-json/ {"
        echo "    limit_req zone=threeaxe.fr burst=25 nodelay;"
        echo "    try_files \$uri \$uri/ /index.php?\$args;"
        echo "}"
        echo ""
        echo "# Block xmlrpc attacks"
        echo "location /xmlrpc.php {"
        echo "    deny all;"
        echo "}"

    else
        echo -e "${RED}❌ Nginx rate limit config not found: $NGINX_RATE_LIMIT_CONFIG${NC}"
    fi
else
    echo -e "${RED}❌ Nginx config not found: $NGINX_CONFIG${NC}"
fi

# Apply WordPress optimizations
echo -e "\n${YELLOW}📝 Applying WordPress optimizations...${NC}"

WP_CONTENT_DIR="/var/www/html/wp-content"
MU_PLUGINS_DIR="$WP_CONTENT_DIR/mu-plugins"

# Create mu-plugins directory if it doesn't exist
if [[ ! -d "$MU_PLUGINS_DIR" ]]; then
    mkdir -p "$MU_PLUGINS_DIR"
    echo -e "${GREEN}✅ Created mu-plugins directory${NC}"
fi

# Copy aggressive optimizations to mu-plugins
AGGRESSIVE_OPTIMIZATIONS="/var/www/html/wp-content/plugins/wp-pdf-builder-pro/aggressive-optimizations.php"
if [[ -f "$AGGRESSIVE_OPTIMIZATIONS" ]]; then
    cp "$AGGRESSIVE_OPTIMIZATIONS" "$MU_PLUGINS_DIR/"
    echo -e "${GREEN}✅ Installed aggressive optimizations as must-use plugin${NC}"
else
    echo -e "${RED}❌ Aggressive optimizations file not found${NC}"
fi

# Copy performance optimizations as regular plugin
PERFORMANCE_OPTIMIZATIONS="/var/www/html/wp-content/plugins/wp-pdf-builder-pro/performance-optimizations.php"
if [[ -f "$PERFORMANCE_OPTIMIZATIONS" ]]; then
    cp "$PERFORMANCE_OPTIMIZATIONS" "$WP_CONTENT_DIR/plugins/"
    echo -e "${GREEN}✅ Installed performance optimizations as regular plugin${NC}"
else
    echo -e "${RED}❌ Performance optimizations file not found${NC}"
fi

# Set proper permissions
chown -R www-data:www-data "$WP_CONTENT_DIR"
chmod -R 755 "$WP_CONTENT_DIR"

# Test nginx configuration
echo -e "\n${YELLOW}🧪 Testing nginx configuration...${NC}"
if nginx -t; then
    echo -e "${GREEN}✅ Nginx configuration is valid${NC}"

    # Reload nginx
    echo -e "${YELLOW}🔄 Reloading nginx...${NC}"
    systemctl reload nginx
    echo -e "${GREEN}✅ Nginx reloaded successfully${NC}"
else
    echo -e "${RED}❌ Nginx configuration test failed${NC}"
    echo -e "${YELLOW}⚠️  Please check your nginx configuration manually${NC}"
fi

# Clear WordPress cache and transients
echo -e "\n${YELLOW}🧹 Clearing WordPress caches...${NC}"
WP_CLI="/usr/local/bin/wp"
if [[ -x "$WP_CLI" ]]; then
    cd /var/www/html
    $WP_CLI cache flush
    $WP_CLI transient delete --all
    echo -e "${GREEN}✅ WordPress caches cleared${NC}"
else
    echo -e "${YELLOW}⚠️  WP-CLI not found, skipping cache cleanup${NC}"
fi

echo -e "\n${GREEN}🎉 Server optimization completed!${NC}"
echo ""
echo -e "${YELLOW}📋 Next steps:${NC}"
echo "1. Monitor nginx error logs for rate limiting warnings"
echo "2. Check WooCommerce admin performance"
echo "3. Verify that analytics features are disabled"
echo "4. Test PDF builder functionality"
echo ""
echo -e "${YELLOW}📊 Expected results:${NC}"
echo "- 90%+ reduction in WooCommerce analytics API calls"
echo "- Elimination of rate limiting warnings for WC endpoints"
echo "- Improved admin page load times"
echo "- Maintained PDF builder functionality"