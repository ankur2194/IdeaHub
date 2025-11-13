# IdeaHub - Open Source Innovation Management Platform

<div align="center">
  
![IdeaHub Logo](https://img.shields.io/badge/IdeaHub-Innovation_Platform-blue)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18.x-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue.svg)](https://www.typescriptlang.org)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)
[![GitHub Stars](https://img.shields.io/github/stars/yourusername/ideahub.svg)](https://github.com/yourusername/ideahub/stargazers)
[![GitHub Issues](https://img.shields.io/github/issues/yourusername/ideahub.svg)](https://github.com/yourusername/ideahub/issues)

**Transform ideas into innovation through collaborative brainstorming and structured workflows**

[Demo](https://demo.ideahub.io) · [Documentation](https://docs.ideahub.io) · [Report Bug](https://github.com/yourusername/ideahub/issues) · [Request Feature](https://github.com/yourusername/ideahub/issues)

</div>

## 🌟 Overview

IdeaHub is a modern, open-source platform designed to capture, discuss, and implement ideas within organizations. Built with Laravel and React, it provides a comprehensive solution for innovation management, from initial brainstorming to final implementation.

Whether you're a startup looking to harness employee creativity or an enterprise seeking to streamline innovation processes, IdeaHub provides the tools and workflows to transform ideas into actionable outcomes.

## ✨ Key Features

### 🔐 **Authentication & User Management**
- Multi-tier role system (Admin, Department Head, Team Lead, User)
- Social authentication (Google, Microsoft, LinkedIn)
- Two-factor authentication
- Session management and security logging

### 💡 **Idea Management**
- Rich text editor with markdown support
- File attachments and media uploads
- Categorization and tagging system
- Customizable idea lifecycle workflows
- Anonymous submission options
- Draft auto-saving

### 💬 **Collaboration & Discussion**
- Threaded discussions with @ mentions
- Real-time updates and notifications
- Emoji reactions and comment voting
- Co-authoring capabilities
- Team workspaces

### ✅ **Approval Workflows**
- Multi-level approval chains
- Customizable workflow paths
- Parallel and sequential approvals
- Time-based auto-escalation
- Delegation capabilities

### 📊 **Analytics & Reporting**
- Comprehensive dashboards
- ROI tracking and success metrics
- Custom report builder
- Data export (PDF, Excel, CSV)
- Trend analysis and insights

### 🎮 **Gamification**
- Point system and leaderboards
- Achievement badges
- Team competitions
- Recognition and rewards

### 🔍 **Advanced Search**
- Full-text search capabilities
- Smart filtering and sorting
- Similar idea detection
- Saved searches

### 🔔 **Notifications**
- Multi-channel delivery (Email, In-app, Push)
- Customizable notification preferences
- Slack/Teams integration
- Digest options

## 🚀 Quick Start

### Prerequisites

- PHP >= 8.2
- Node.js >= 18.x
- Composer >= 2.x
- MySQL >= 8.0 or PostgreSQL >= 14
- Redis >= 6.x (optional but recommended)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/ideahub.git
cd ideahub
```

2. **Install backend dependencies**
```bash
composer install
```

3. **Install frontend dependencies**
```bash
cd frontend
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure your database in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ideahub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

7. **Build frontend assets**
```bash
cd frontend
npm run build
```

8. **Start the development servers**

Backend:
```bash
php artisan serve
```

Frontend (in a new terminal):
```bash
cd frontend
npm run dev
```

9. **Access the application**
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000
- Default admin: admin@ideahub.io / password

## 🐳 Docker Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/ideahub.git
cd ideahub

# Start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --seed

# Access at http://localhost:8080
```

## 🛠 Tech Stack

### Backend
- **Framework:** Laravel 11.x
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+ / PostgreSQL 14+
- **Cache:** Redis
- **Queue:** Laravel Horizon
- **Search:** Laravel Scout with Meilisearch
- **API:** RESTful with Laravel Sanctum
- **Real-time:** Laravel Echo with Pusher/Soketi

### Frontend
- **Framework:** React 18.x with TypeScript
- **Build Tool:** Vite
- **Routing:** React Router v6
- **State Management:** Redux Toolkit / Zustand
- **UI Components:** Tailwind CSS + Shadcn/ui
- **Forms:** React Hook Form + Zod
- **API Client:** Axios with TanStack Query

### DevOps & Tools
- **Containerization:** Docker & Docker Compose
- **CI/CD:** GitHub Actions
- **Testing:** PHPUnit, Jest, React Testing Library
- **Code Quality:** ESLint, Prettier, PHP CS Fixer
- **Documentation:** OpenAPI/Swagger

## 📁 Project Structure

```
ideahub/
├── app/                    # Laravel application code
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   ├── Middleware/     # Custom middleware
│   │   └── Requests/       # Form requests
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   └── Repositories/      # Data access layer
├── frontend/              # React application
│   ├── src/
│   │   ├── components/    # React components
│   │   ├── pages/        # Page components
│   │   ├── hooks/        # Custom hooks
│   │   ├── services/     # API services
│   │   ├── store/        # Redux store
│   │   └── utils/        # Utilities
│   └── public/           # Static assets
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/         # Database seeders
├── tests/               # Test suites
├── docker/              # Docker configurations
└── docs/               # Documentation
```

## 🧪 Testing

Run the test suites:

```bash
# Backend tests
php artisan test

# Frontend tests
cd frontend
npm run test

# E2E tests
npm run test:e2e
```

## 📖 Documentation

Comprehensive documentation is available at [https://docs.ideahub.io](https://docs.ideahub.io)

- [Installation Guide](docs/installation.md)
- [Configuration](docs/configuration.md)
- [API Documentation](docs/api.md)
- [User Guide](docs/user-guide.md)
- [Administrator Guide](docs/admin-guide.md)
- [Developer Guide](docs/developer-guide.md)

## 🤝 Contributing

We welcome contributions from the community! Please read our [Contributing Guide](CONTRIBUTING.md) to get started.

### How to Contribute

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Setup

```bash
# Install development dependencies
composer install --dev
cd frontend && npm install --dev

# Run code formatting
composer format
npm run format

# Run linting
composer lint
npm run lint

# Run tests with coverage
composer test:coverage
npm run test:coverage
```

## 🗺 Roadmap

### Phase 1 - MVP (Q1 2025) ✅
- [x] Basic authentication
- [x] Idea submission and listing
- [x] Simple commenting system
- [x] Basic approval workflow

### Phase 2 - Core Features (Q2 2025) 🚧
- [ ] Advanced dashboard
- [ ] Multi-level approvals
- [ ] Search and filtering
- [ ] Email notifications
- [ ] Mobile responsive design

### Phase 3 - Enhancement (Q3 2025)
- [ ] Real-time features
- [ ] Gamification system
- [ ] Advanced analytics
- [ ] API v2
- [ ] Mobile applications

### Phase 4 - Enterprise (Q4 2025)
- [ ] Third-party integrations
- [ ] Advanced reporting
- [ ] Multi-tenancy
- [ ] White-labeling support
- [ ] Enterprise SSO

See the [open issues](https://github.com/yourusername/ideahub/issues) for a full list of proposed features and known issues.

## 🌍 Community & Support

- **Discord:** [Join our Discord server](https://discord.gg/ideahub)
- **Forum:** [Community Forum](https://community.ideahub.io)
- **Twitter:** [@ideahub_io](https://twitter.com/ideahub_io)
- **Email:** support@ideahub.io

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Thanks to all our [contributors](https://github.com/yourusername/ideahub/graphs/contributors)
- Built with amazing open-source projects including Laravel, React, and many more
- Inspired by the need for better innovation management in organizations

## 💰 Sponsors

Support this project by becoming a sponsor. Your logo will show up here with a link to your website.

[Become a sponsor](https://github.com/sponsors/yourusername)

## 🏆 Contributors

Thanks goes to these wonderful people:

<!-- ALL-CONTRIBUTORS-LIST:START -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

## 📊 Stats

![GitHub commit activity](https://img.shields.io/github/commit-activity/m/yourusername/ideahub)
![GitHub last commit](https://img.shields.io/github/last-commit/yourusername/ideahub)
![GitHub code size](https://img.shields.io/github/languages/code-size/yourusername/ideahub)

---

<div align="center">
  
**[Website](https://ideahub.io)** · **[Documentation](https://docs.ideahub.io)** · **[Report Bug](https://github.com/yourusername/ideahub/issues)**

Made with ❤️ by the IdeaHub Community

</div>