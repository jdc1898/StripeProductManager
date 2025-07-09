#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="stripe-product-manager-test"
APP_DIR="./$APP_NAME"
PACKAGE_NAME="fullstack/stripe-product-manager"

echo -e "${BLUE}🚀 Stripe Product Manager Package Test Setup${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""

# Check if Laravel installer is available
if ! command -v laravel &> /dev/null; then
    echo -e "${YELLOW}⚠️  Laravel installer not found. Installing via Composer...${NC}"
    composer global require laravel/installer
fi

# Clean up existing test app if it exists
if [ -d "$APP_DIR" ]; then
    echo -e "${YELLOW}🗑️  Removing existing test app...${NC}"
    rm -rf "$APP_DIR"
fi

# Create new Laravel app
echo -e "${BLUE}📦 Creating new Laravel application...${NC}"
laravel new "$APP_NAME" --quiet

if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}❌ Failed to create Laravel application${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Laravel application created successfully${NC}"

# Navigate to app directory
cd "$APP_DIR"

# Install required dependencies
echo -e "${BLUE}📥 Installing required dependencies...${NC}"
composer require spatie/laravel-permission
composer require stripe/stripe-php:^16.2
composer require filament/filament
composer require flowframe/laravel-trend

# Install Filament
echo -e "${BLUE}🎨 Installing Filament...${NC}"
php artisan filament:install --panels --no-interaction

# Install Cashier
echo -e "${BLUE}💳 Installing Cashier...${NC}"
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"

# Add local package repository
echo -e "${BLUE}🔧 Adding local package repository...${NC}"
composer config repositories.local '{"type": "path", "url": "../"}'

# Install the package
echo -e "${BLUE}📦 Installing Stripe Product Manager package...${NC}"
composer require "$PACKAGE_NAME:dev-main" --prefer-source

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to install package${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Package installed successfully${NC}"

# Clear autoload cache to ensure package classes are loaded
echo -e "${BLUE}🧹 Clearing autoload cache...${NC}"
composer dump-autoload

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo -e "${BLUE}⚙️  Creating .env file...${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Add Stripe configuration to .env
echo -e "${BLUE}⚙️  Adding Stripe configuration to .env...${NC}"
if ! grep -q "STRIPE_SECRET_KEY" .env; then
    echo "" >> .env
    echo "# Stripe Configuration" >> .env
    echo "STRIPE_SECRET_KEY=sk_test_your_test_key_here" >> .env
    echo "STRIPE_PUBLISHABLE_KEY=pk_test_your_test_key_here" >> .env
    echo "STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here" >> .env
    echo "STRIPE_API_VERSION=2024-06-20" >> .env
    echo "" >> .env
    echo "# Package Configuration" >> .env
    echo "STRIPE_PRODUCT_MANAGER_DB_PREFIX=stripe_" >> .env
    echo "STRIPE_SYNC_ENABLED=true" >> .env
    echo "STRIPE_FILAMENT_ENABLED=true" >> .env
    echo "STRIPE_FILAMENT_PANEL=admin" >> .env
fi

# Clean up any existing Filament files that might have been copied to app directory
echo -e "${BLUE}🧹 Cleaning up any existing Filament files from app directory...${NC}"
if [ -d "$APP_DIR/app/Providers/Filament" ]; then
    rm -rf "$APP_DIR/app/Providers/Filament"
fi
if [ -d "$APP_DIR/app/Filament" ]; then
    rm -rf "$APP_DIR/app/Filament"
fi

# DO NOT copy the panel providers into the app directory anymore
# DO NOT publish the panel providers
# Instead, ensure the correct lines are in bootstrap/providers.php
# PROVIDERS_FILE="$APP_DIR/bootstrap/providers.php"
# if [ -f "$PROVIDERS_FILE" ]; then
#     # Remove any old app panel providers
#     sed -i '' '/App\\\\Providers\\\\Filament\\\\SuperAdminPanelProvider::class/d' "$PROVIDERS_FILE"
#     sed -i '' '/App\\\\Providers\\\\Filament\\\\TenantAdminPanelProvider::class/d' "$PROVIDERS_FILE"
#     sed -i '' '/App\\\\Providers\\\\Filament\\\\MemberPanelProvider::class/d' "$PROVIDERS_FILE"
#     # Add package panel providers if not present
#     grep -q 'Fullstack\\\\StripeProductManager\\\\Providers\\\\Filament\\\\SuperAdminPanelProvider::class' "$PROVIDERS_FILE" || \
#         sed -i '' '/App\\\\Providers\\\\AppServiceProvider::class,/a\
#     Fullstack\\StripeProductManager\\Providers\\Filament\\SuperAdminPanelProvider::class,\
#     Fullstack\\StripeProductManager\\Providers\\Filament\\TenantAdminPanelProvider::class,\
#     Fullstack\\StripeProductManager\\Providers\\Filament\\MemberPanelProvider::class,' "$PROVIDERS_FILE"
# fi

# Run the package installer
echo -e "${BLUE}🔧 Running package installer...${NC}"
php artisan stripe-product-manager:install --force

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Package installation failed${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Package installation completed successfully${NC}"

# Create a test user
echo -e "${BLUE}👤 Creating test user...${NC}"
php artisan tinker --execute="
use App\Models\User;
use Spatie\Permission\Models\Role;

// Create test user
\$user = User::create([
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);

// Assign super-admin role
\$role = Role::where('name', 'super-admin')->where('guard_name', 'web')->first();
if (\$role) {
    \$user->assignRole(\$role);
    echo 'Test user created: admin@test.com / password';
} else {
    echo 'Role not found, user created without role';
}
"

# Test package registration
echo -e "${BLUE}🧪 Testing package registration...${NC}"
php artisan stripe-product-manager:test

# Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo -e "${GREEN}🎉 Setup completed successfully!${NC}"
echo ""
echo -e "${BLUE}📋 Test Information:${NC}"
echo -e "   App Directory: ${YELLOW}$APP_DIR${NC}"
echo -e "   URL: ${YELLOW}http://localhost:8000${NC}"
echo -e "   Admin Email: ${YELLOW}admin@test.com${NC}"
echo -e "   Admin Password: ${YELLOW}password${NC}"
echo ""
echo -e "${BLUE}🔗 Available URLs:${NC}"
echo -e "   ${YELLOW}http://localhost:8000${NC} - Welcome page"
echo -e "   ${YELLOW}http://localhost:8000/admin${NC} - Admin panel"
echo -e "   ${YELLOW}http://localhost:8000/super-admin${NC} - Super admin panel"
echo -e "   ${YELLOW}http://localhost:8000/tenant-admin${NC} - Tenant admin panel"
echo -e "   ${YELLOW}http://localhost:8000/member${NC} - Member panel"
echo ""

# Ask if user wants to start the server
read -p "Do you want to start the development server now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${BLUE}🚀 Starting development server...${NC}"
    echo -e "${GREEN}✅ Server running at http://localhost:8000${NC}"
    echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
    php artisan serve
else
    echo -e "${BLUE}📝 To start the server manually, run:${NC}"
    echo -e "${YELLOW}cd $APP_DIR && php artisan serve${NC}"
fi
