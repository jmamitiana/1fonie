# Active Context: TechConnect - B2B Service Provider Platform

## Current State

**Project Status**: ✅ En développement - Docker supprimé, utilisation de Composer

Le projet TechConnect est une plateforme B2B connectant les entreprises avec des prestataires de services techniques. Docker a été supprimé au profit de Composer et installation locale.

## Recently Completed

- [x] Suppression de docker-compose.yml
- [x] Suppression de backend/Dockerfile
- [x] Suppression de frontend/Dockerfile
- [x] Mise à jour INSTALLATION.md pour utiliser Composer et Bun
- [x] Configuration Laravel 11 avec PHP 8.2
- [x] Configuration frontend Next.js avec Bun

## Current Structure

| File/Directory | Purpose | Status |
|----------------|---------|--------|
| `backend/` | Laravel 11 API | ✅ Prêt |
| `frontend/` | Next.js 14 React app | ✅ Prêt |
| `src/` | Template Next.js starter | ⚠️ Non utilisé |
| `INSTALLATION.md` | Guide d'installation | ✅ Mis à jour |

## Structure du Projet

```
techconnect/
├── backend/                 # Laravel API (Composer)
│   ├── app/
│   │   ├── Http/Controllers/Api/  # API Controllers
│   │   └── Models/                # Eloquent models
│   ├── config/                  # Configuration Laravel
│   └── database/migrations/      # Base de données
├── frontend/                   # Next.js (Bun)
│   ├── src/app/               # Pages Next.js
│   ├── src/context/           # Auth context
│   └── src/services/          # API services
└── INSTALLATION.md            # Guide d'installation
```

## Stack Technique

- **Backend**: Laravel 11, PHP 8.2, MySQL, Composer
- **Frontend**: Next.js 14, React 18, TypeScript, Tailwind CSS 4, Bun
- **Paiement**: Stripe Connect

## Configuration Requise

Pour démarrer le projet:
1. `cd backend && composer install`
2. Configurer `.env` avec les variables de base de données
3. `php artisan migrate`
4. `cd frontend && bun install`
5. `bun dev` (frontend) et `php artisan serve` (backend)

## Pending Improvements

- [ ] Tester l'installation locale
- [ ] Vérifier la connexion API frontend vers backend
- [ ] Ajouter des examples d'utilisation

## Session History

| Date | Changes |
|------|---------|
| Initial | Template créé avec base setup Next.js |
| 2026-03-05 | Suppression Docker, migration vers Composer |
