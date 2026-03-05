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
- Stripe SDK

### Frontend
- React 18
- Next.js 14
- TypeScript
- Tailwind CSS 4
- Zustand (State Management)

### Infrastructure
- Docker & Docker Compose
- Nginx

## Prerequisites

- Docker and Docker Compose
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
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=techconnect
DB_USERNAME=techconnect
DB_PASSWORD=techconnect_pass
```

### 3. Start Docker Containers

```bash
docker-compose up -d
```

This will start:
- MySQL database on port 3306
- Laravel backend API on port 8000
- Next.js frontend on port 3000
- Mailhog on ports 1025/8025
- Nginx reverse proxy on port 80

### 4. Install Backend Dependencies

```bash
docker-compose exec backend composer install
```

### 5. Run Migrations

```bash
docker-compose exec backend php artisan migrate
```

### 6. Seed the Database (Optional)

```bash
docker-compose exec backend php artisan db:seed
```

### 7. Access the Application

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- Mailhog: http://localhost:8025

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
docker-compose exec backend php artisan test
```

### Building for Production

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build
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
└── docker-compose.yml    # Docker configuration
```

## License

MIT License
