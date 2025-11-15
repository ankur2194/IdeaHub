# IdeaHub - Open Source Innovation Management Platform

<div align="center">

![IdeaHub Logo](https://img.shields.io/badge/IdeaHub-Innovation_Platform-blue)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19.x-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue.svg)](https://www.typescriptlang.org)

**Transform ideas into innovation through collaborative brainstorming and structured workflows**

[Features](#-key-features) · [Quick Start](#-quick-start) · [Documentation](#-documentation) · [Contributing](#-contributing)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Quick Start](#-quick-start)
- [Development](#-development)
- [Project Structure](#-project-structure)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Overview

IdeaHub is a modern, open-source platform designed to capture, discuss, and implement ideas within organizations. Built with Laravel 12 and React 19, it provides a comprehensive solution for innovation management, from initial brainstorming to final implementation.

### Why IdeaHub?

- **Employee Engagement** - Encourage participation and recognize contributors
- **Structured Workflow** - Multi-level approval process with customizable paths
- **Collaboration** - Real-time discussions and threaded comments
- **Analytics** - Track ROI and measure innovation impact
- **Scalable** - From startups to enterprises

---

## ✨ Key Features

### 🔐 **Authentication & User Management**
- ✅ Multi-tier role system (Admin, Department Head, Team Lead, User)
- ✅ JWT-based API authentication with Laravel Sanctum
- ✅ Protected routes and auto-logout on session expiry
- ✅ User profiles with department and job title

### 💡 **Idea Management**
- ✅ Rich idea submission with title, description, categories, tags
- ✅ Draft auto-saving before submission
- ✅ Status tracking (Draft → Submitted → Under Review → Approved → Implemented)
- ✅ Anonymous submission options
- ✅ Like/upvote system
- ✅ View counting
- ✅ Edit/delete permissions (status-based)

### 💬 **Collaboration & Discussion**
- ✅ Threaded comment system
- ✅ Edit and delete own comments
- ✅ Comment likes
- ✅ Real-time comment counts
- ✅ Author attribution or anonymous posting

### ✅ **Approval Workflows**
- ✅ Multi-level approval tracking with automatic routing
- ✅ Configurable workflows by category and budget
- ✅ Role-based approver assignment
- ✅ Approval comments and feedback
- ✅ Visual workflow status with progress tracking
- ✅ Email notifications for approval requests

### 📊 **Organization & Categorization**
- ✅ Customizable categories with colors and icons
- ✅ Flexible tagging system
- ✅ Advanced filtering (status, category, tags, author, date range)
- ✅ Multiple sort options (date, likes, comments, views, title)
- ✅ Pagination support
- ✅ Full-text search across ideas

### 🎮 **Gamification System** ✨ NEW
- ✅ Experience points (XP) with level progression (1-50+)
- ✅ 18 unique badges across 6 categories (Ideas, Approvals, Comments, Likes, Milestones, Special)
- ✅ 4 rarity tiers (Common, Rare, Epic, Legendary)
- ✅ 7 rank titles (Newcomer → Innovation Master)
- ✅ Automatic badge awarding and level-up notifications
- ✅ User profile with stats and badge gallery
- ✅ Leaderboard by level and points
- ✅ XP breakdown and progress tracking

### 📧 **Notifications & Email**
- ✅ In-app notification system
- ✅ Email notifications (idea submitted, approved, rejected)
- ✅ Comment and reply notifications
- ✅ Approval request notifications
- ✅ Badge earned and level-up notifications
- ✅ Beautiful HTML email templates

### 📈 **Analytics & Reporting**
- ✅ Overview dashboard with key metrics
- ✅ Ideas trend analysis over time
- ✅ Category and status distribution charts
- ✅ Top contributors leaderboard
- ✅ Department statistics
- ✅ Recent activity feed
- ✅ User-specific analytics

### 📎 **File Management**
- ✅ Multiple file attachments per idea
- ✅ Support for documents, images, PDFs (10MB limit)
- ✅ Drag-and-drop file upload
- ✅ File type and size validation
- ✅ Download attachments
- ✅ File icons with preview

### 🎨 **Modern UI/UX**
- ✅ Responsive design (mobile-first)
- ✅ Clean, intuitive interface with TailwindCSS 4
- ✅ Loading and error states
- ✅ Empty state handling
- ✅ Status badges and visual indicators
- ✅ Animated progress bars and transitions
- ✅ Toast notifications for user actions

---

## 🛠 Tech Stack

### Backend
- **Framework:** Laravel 12.x
- **Language:** PHP 8.2+
- **Database:** SQLite (dev) / MySQL 8.0+ / PostgreSQL 14+ (production)
- **Authentication:** Laravel Sanctum (JWT)
- **API:** RESTful with resource controllers
- **ORM:** Eloquent
- **Testing:** PHPUnit 11.5

### Frontend
- **Framework:** React 19.2.0
- **Language:** TypeScript 5.9
- **State Management:** Redux Toolkit 2.10
- **Routing:** React Router 7.9
- **Styling:** TailwindCSS 4.1
- **Build Tool:** Vite 7.2
- **HTTP Client:** Axios 1.13
- **Icons:** Heroicons 2.2
- **Forms:** React Hook Form 7.66 + Zod 4.1

### Development Tools
- **Code Quality:** Laravel Pint, ESLint
- **Version Control:** Git
- **Package Managers:** Composer, NPM

---

## 🚀 Quick Start

### Prerequisites

- **PHP** >= 8.2
- **Node.js** >= 18.x
- **Composer** >= 2.x
- **Database:** MySQL 8.0+ / PostgreSQL 14+ / SQLite (default)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/ideahub.git
cd ideahub

# 2. Install backend dependencies
composer install

# 3. Install frontend dependencies
cd frontend
npm install
cd ..

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure database (edit .env)
# For development, SQLite is pre-configured
# For production, update DB_* variables

# 6. Run migrations and seed database
php artisan migrate --seed

# 7. Start development servers
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
cd frontend
npm run dev
```

### Access the Application

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:8000
- **Demo Credentials:**
  - Email: `admin@ideahub.test`
  - Password: `password`

---

## 💻 Development

### Running Both Servers Concurrently

```bash
# Using the convenience script (runs both backend and frontend)
composer dev
```

This will start:
- Laravel development server (port 8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server (port 5173)

### Backend Development

```bash
# Run Laravel server
php artisan serve

# Run migrations
php artisan migrate

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Run tests
php artisan test

# Format code
composer format

# Interactive REPL
php artisan tinker

# View logs
php artisan pail
```

### Frontend Development

```bash
cd frontend

# Development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run linter
npm run lint
```

### Database Seeding

The database seeder creates test data:
- 5 users with different roles (admin, manager, team lead, 2 regular users)
- 8 categories (Product Innovation, Process Improvement, etc.)
- Multiple tags for idea classification

**Login credentials:**
- Admin: `admin@ideahub.test` / `password`
- Manager: `manager@ideahub.test` / `password`
- User: `alice@ideahub.test` / `password`

---

## 📁 Project Structure

```
IdeaHub/
├── app/                    # Laravel backend
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/       # API Controllers
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic (future)
├── frontend/              # React SPA
│   ├── src/
│   │   ├── components/    # React components
│   │   ├── pages/        # Route pages
│   │   ├── services/     # API services
│   │   ├── store/        # Redux store
│   │   ├── types/        # TypeScript types
│   │   └── utils/        # Helper functions
│   └── package.json
├── database/
│   ├── migrations/       # Database schema
│   ├── seeders/         # Test data
│   └── factories/       # Model factories
├── routes/
│   └── api.php          # API routes
├── tests/               # PHPUnit tests
├── composer.json        # PHP dependencies
├── package.json         # Root scripts
└── README.md           # This file
```

See [CLAUDE.md](CLAUDE.md) for detailed architecture and development guidelines.

---

## 📡 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Key Endpoints

#### Authentication
```http
POST   /api/register          # Register new user
POST   /api/login            # Login and get token
POST   /api/logout           # Logout
GET    /api/user             # Get authenticated user
```

#### Ideas
```http
GET    /api/ideas                    # List ideas (paginated)
POST   /api/ideas                    # Create idea
GET    /api/ideas/{id}              # Get idea details
PUT    /api/ideas/{id}              # Update idea
DELETE /api/ideas/{id}              # Delete idea
POST   /api/ideas/{id}/submit       # Submit for review
POST   /api/ideas/{id}/like         # Like/unlike idea
```

#### Comments
```http
GET    /api/ideas/{id}/comments     # Get idea comments
POST   /api/comments                # Create comment
PUT    /api/comments/{id}           # Update comment
DELETE /api/comments/{id}           # Delete comment
POST   /api/comments/{id}/like      # Like comment
```

#### Categories & Tags
```http
GET    /api/categories              # List categories
GET    /api/tags                    # List tags
```

See full API documentation in [docs/api.md](docs/api.md) (coming soon).

---

## 🧪 Testing

### Backend Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/IdeaTest.php

# Run with coverage
php artisan test --coverage
```

### Frontend Tests

```bash
cd frontend

# Run tests (to be implemented)
npm run test

# Run with coverage
npm run test:coverage
```

---

## 🚢 Deployment

### Production Build

```bash
# Backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend
cd frontend
npm run build
# Output in frontend/dist/
```

### Environment Configuration

Update `.env` for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=ideahub
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Deployment Options

- **Traditional Hosting:** Apache/Nginx + PHP-FPM
- **Cloud Platforms:** AWS, DigitalOcean, Heroku
- **Containerized:** Docker (Dockerfile coming soon)
- **Platform-as-a-Service:** Laravel Forge, Ploi

---

## 📖 Documentation

- [CLAUDE.md](CLAUDE.md) - Comprehensive development guide for AI assistants
- [Frontend README](frontend/README.md) - Frontend-specific documentation
- [API Documentation](docs/api.md) - API reference (coming soon)
- [Deployment Guide](docs/deployment.md) - Production deployment (coming soon)

---

## 🎯 Roadmap

### ✅ Phase 1 - MVP (Completed)
- [x] Authentication system
- [x] Idea submission and management
- [x] Comment system
- [x] Basic approval workflow
- [x] Categories and tags
- [x] Responsive frontend

### ✅ Phase 2 - Core Features (Completed)
- [x] Advanced dashboard with analytics (8 endpoints, charts, leaderboard)
- [x] Multi-level approval workflows (configurable, automatic routing)
- [x] Email notifications (idea submitted, approved, rejected, comments)
- [x] File attachments (multiple files, 10MB limit, download)
- [x] Advanced search (tags, date range, author, multi-filter)

### 🚧 Phase 3 - Enhancement (In Progress)
- [x] **Gamification system** (18 badges, XP, levels, leaderboard) ✨ NEW
- [ ] Real-time features (WebSockets with Laravel Echo)
- [ ] Advanced analytics (export reports, custom metrics)
- [ ] Mobile applications (React Native or PWA)
- [ ] API v2 (GraphQL or enhanced REST)

### 🔮 Phase 4 - Enterprise (Future)
- [ ] Third-party integrations (Slack, Teams, Jira)
- [ ] Multi-tenancy support
- [ ] White-labeling capabilities
- [ ] Enterprise SSO (SAML, OAuth)
- [ ] Advanced reporting and exports

---

## 🤝 Contributing

We welcome contributions! Here's how to get started:

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make your changes**
4. **Run tests**
   ```bash
   php artisan test
   cd frontend && npm run lint
   ```
5. **Commit your changes**
   ```bash
   git commit -m "Add amazing feature"
   ```
6. **Push to your fork**
   ```bash
   git push origin feature/amazing-feature
   ```
7. **Open a Pull Request**

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use Laravel Pint for code formatting
- Write tests for new features
- Follow React/TypeScript best practices
- Use conventional commits
- Update documentation

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## 🐛 Known Issues

- Database seeders create sample data - clear before production use
- Frontend `.env` file needed for API connection
- SQLite has limitations - use MySQL/PostgreSQL for production

---

## 💡 Tips & Troubleshooting

### Common Issues

**"No application encryption key"**
```bash
php artisan key:generate
```

**"Access denied for user"**
- Check database credentials in `.env`
- Ensure database exists: `CREATE DATABASE ideahub;`

**"Port 8000 already in use"**
```bash
php artisan serve --port=8080
```

**"Frontend can't connect to API"**
- Ensure backend is running on port 8000
- Check `VITE_API_URL` in `frontend/.env`
- Verify CORS settings in `config/cors.php`

### Performance Tips

- Enable caching in production
- Use queue workers for async tasks
- Optimize database queries with eager loading
- Enable Redis for cache and sessions
- Use CDN for static assets

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Laravel** - The PHP Framework for Web Artisans
- **React** - A JavaScript library for building user interfaces
- **Tailwind CSS** - A utility-first CSS framework
- **Heroicons** - Beautiful hand-crafted SVG icons
- All contributors and the open-source community

---

## 📞 Support

- **Documentation:** [CLAUDE.md](CLAUDE.md)
- **Issues:** [GitHub Issues](https://github.com/yourusername/ideahub/issues)
- **Discussions:** [GitHub Discussions](https://github.com/yourusername/ideahub/discussions)

---

## ⭐ Star History

If you find IdeaHub useful, please consider giving it a star on GitHub!

---

<div align="center">

**[⬆ Back to Top](#ideahub---open-source-innovation-management-platform)**

Made with ❤️ by the IdeaHub Community

</div>
