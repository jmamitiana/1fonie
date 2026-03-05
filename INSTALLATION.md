# TechConnect - B2B Service Provider Platform

A comprehensive platform connecting companies with local service providers for technical interventions.

## Features

- **Multi-user System**: Company, Provider, and Admin roles
- **Mission Management**: Create, browse, and manage technical missions
- **Application System**: Providers can apply to missions with proposals
- **Secure Escrow Payments**: Stripe Connect integration for secure payments
- **Internal Messaging**: Built-in messaging between companies and providers
- **Ratings & Reviews**: Post-mission rating system
- **Real-time Notifications**: Stay updated on all activities
- **Dashboard Analytics**: Comprehensive statistics for all user types

## Tech Stack

### Backend
- Laravel 11
- MySQL 8.0
- PHP 8.2
- Composer
- Stripe SDK

### Frontend
- React 18
- Next.js 14
- TypeScript
- Tailwind CSS 4
- Zustand (State Management)
- Bun (Package Manager)

## Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+ (ou SQLite pour le développement)
- Node.js 18+
- Bun
- Git
- (Optional) Stripe Account
- (Optional) Google Maps API Key

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd techconnect
```

### 2. Configure Environment Variables

Copy the example environment file and update it:

```bash
# Backend
cp backend/.env.example backend/.env

# Update these values in backend/.env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techconnect
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

### 3. Install Backend Dependencies

```bash
cd backend
composer install
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed the Database (Optional)

```bash
php artisan db:seed
```

### 7. Install Frontend Dependencies

```bash
cd ../frontend
bun install
```

### 8. Start Development Servers

```bash
# Terminal 1 - Backend (port 8000)
cd backend
php artisan serve

# Terminal 2 - Frontend (port 3000)
cd frontend
bun dev
```

### 9. Access the Application

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api

## Default Admin Account

After seeding, you can login with:
- Email: admin@techconnect.com
- Password: password

## Stripe Configuration

1. Create a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard
3. Add them to your `.env` file:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

## Google Maps Configuration

1. Get a Google Maps API key from https://console.cloud.google.com
2. Enable the Maps JavaScript API
3. Add to your `.env`:

```env
NEXT_PUBLIC_GOOGLE_MAPS_KEY=your_api_key
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Missions (Public)
- `GET /api/missions` - List open missions
- `GET /api/missions/{id}` - Get mission details
- `GET /api/categories` - List mission categories

### Company Routes
- `GET /api/company/missions` - List company's missions
- `POST /api/company/missions` - Create mission
- `POST /api/company/missions/{id}/select-provider` - Select provider
- `POST /api/company/missions/{id}/pay` - Pay for mission
- `POST /api/company/missions/{id}/complete` - Complete mission

### Provider Routes
- `GET /api/provider/missions/available` - Browse available missions
- `POST /api/provider/missions/{id}/apply` - Apply to mission
- `GET /api/provider/earnings` - View earnings

### Admin Routes
- `GET /api/admin/dashboard` - Admin dashboard
- `GET /api/admin/users` - Manage users
- `GET /api/admin/missions` - Manage missions
- `GET /api/admin/analytics` - View analytics

## Payment Flow

1. Company creates a mission with price (e.g., €100)
2. Platform adds 20% fee (€20)
3. Total: €120
4. Provider applies to mission
5. Company selects provider
6. Company pays €120 (held in escrow)
7. Mission is completed
8. Company confirms completion
9. Provider receives €100 (minus fees)

## Development

### Running Tests

```bash
cd backend
php artisan test
```

### Running Backend Commands

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

## Project Structure

```
techconnect/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/          # Controllers, Middleware
│   │   ├── Models/        # Eloquent models
│   │   └── Providers/    # Service providers
│   ├── config/            # Configuration files
│   ├── database/          # Migrations & Seeders
│   └── routes/           # Route definitions
├── frontend/              # Next.js React app
│   ├── src/
│   │   ├── app/          # Pages
│   │   ├── components/   # React components
│   │   ├── context/      # State management
│   │   ├── services/     # API services
│   │   └── types/        # TypeScript types
│   └── public/           # Static assets
└── src/                   # Root Next.js app (template)
```

## License

MIT License
