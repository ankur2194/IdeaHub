# Development Guide

Complete guide for developing IdeaHub locally.

## 📋 Table of Contents

- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Backend Development](#backend-development)
- [Frontend Development](#frontend-development)
- [Database Management](#database-management)
- [Testing](#testing)
- [Debugging](#debugging)
- [Common Tasks](#common-tasks)
- [Best Practices](#best-practices)

---

## Getting Started

### Initial Setup

```bash
# Clone repository
git clone https://github.com/yourusername/ideahub.git
cd ideahub

# Install dependencies
composer install
cd frontend && npm install && cd ..

# Environment configuration
cp .env.example .env
cp frontend/.env.example frontend/.env
php artisan key:generate

# Database setup
touch database/database.sqlite  # If using SQLite
php artisan migrate --seed
```

### Development Servers

#### Option 1: Run Concurrently (Recommended)
```bash
composer dev
```

This starts:
- Laravel server (http://localhost:8000)
- Vite dev server (http://localhost:5173)
- Queue worker
- Log viewer

#### Option 2: Manual Start
```bash
# Terminal 1: Backend
php artisan serve

# Terminal 2: Frontend
cd frontend && npm run dev
```

---

## Development Workflow

### 1. Create Feature Branch
```bash
git checkout -b feature/your-feature-name
```

### 2. Make Changes
- Backend: Edit files in `app/`, `database/`, `routes/`
- Frontend: Edit files in `frontend/src/`

### 3. Test Changes
```bash
# Backend
php artisan test

# Frontend
cd frontend && npm run lint
```

### 4. Commit & Push
```bash
git add .
git commit -m "feat: add your feature"
git push origin feature/your-feature-name
```

### 5. Create Pull Request
Open PR on GitHub for review

---

## Backend Development

### Project Structure
```
app/
├── Http/
│   └── Controllers/
│       └── Api/           # API controllers
├── Models/                # Eloquent models
├── Services/              # Business logic (future)
└── Providers/             # Service providers
```

### Creating a New Feature

#### 1. Create Migration
```bash
php artisan make:migration create_table_name_table
```

Edit `database/migrations/{timestamp}_create_table_name_table.php`:
```php
public function up()
{
    Schema::create('table_name', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        // ... more columns
        $table->timestamps();
    });
}
```

#### 2. Create Model
```bash
php artisan make:model ModelName
```

Edit `app/Models/ModelName.php`:
```php
class ModelName extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Relationships
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
}
```

#### 3. Create Controller
```bash
php artisan make:controller Api/ModelNameController --api
```

Edit `app/Http/Controllers/Api/ModelNameController.php`:
```php
class ModelNameController extends Controller
{
    public function index()
    {
        $items = ModelName::with('relatedModel')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item = ModelName::create($validated);

        return response()->json([
            'success' => true,
            'data' => $item,
        ], 201);
    }
}
```

#### 4. Add Routes
Edit `routes/api.php`:
```php
Route::apiResource('model-names', ModelNameController::class);
```

#### 5. Run Migration
```bash
php artisan migrate
```

### Useful Artisan Commands

```bash
# Database
php artisan migrate                # Run migrations
php artisan migrate:fresh --seed   # Fresh DB with seed data
php artisan migrate:rollback       # Rollback last migration
php artisan db:seed                # Run seeders

# Development
php artisan route:list             # List all routes
php artisan tinker                 # Interactive REPL
php artisan pail                   # Log viewer
php artisan queue:work             # Process queue jobs

# Code generation
php artisan make:model ModelName -mfs  # Model + migration + factory + seeder
php artisan make:controller Name --api # API controller
php artisan make:request StoreName     # Form request

# Cache management
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Code quality
composer format                    # Format with Pint
php artisan test                   # Run tests
```

---

## Frontend Development

### Project Structure
```
frontend/src/
├── components/
│   ├── auth/          # Auth-specific
│   ├── comments/      # Comment components
│   ├── common/        # Reusable UI
│   ├── ideas/         # Idea components
│   └── layout/        # Layout components
├── pages/             # Route pages
├── services/          # API services
├── store/             # Redux store
├── types/             # TypeScript types
└── utils/             # Helpers
```

### Creating a New Feature

#### 1. Define Types
Edit `frontend/src/types/index.ts`:
```typescript
export interface NewModel {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}
```

#### 2. Create API Service
Create `frontend/src/services/newModelService.ts`:
```typescript
import api from './api';
import type { ApiResponse, NewModel } from '../types';

export const newModelService = {
  getAll: async (): Promise<ApiResponse<NewModel[]>> => {
    const response = await api.get('/new-models');
    return response.data;
  },

  create: async (data: Partial<NewModel>): Promise<ApiResponse<NewModel>> => {
    const response = await api.post('/new-models', data);
    return response.data;
  },
};
```

#### 3. Create Redux Slice
Create `frontend/src/store/newModelSlice.ts`:
```typescript
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { newModelService } from '../services/newModelService';
import type { NewModel } from '../types';

interface NewModelState {
  items: NewModel[];
  loading: boolean;
  error: string | null;
}

export const fetchItems = createAsyncThunk(
  'newModel/fetchItems',
  async (_, { rejectWithValue }) => {
    try {
      const response = await newModelService.getAll();
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

const newModelSlice = createSlice({
  name: 'newModel',
  initialState: {
    items: [],
    loading: false,
    error: null,
  } as NewModelState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchItems.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchItems.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload;
      })
      .addCase(fetchItems.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });
  },
});

export default newModelSlice.reducer;
```

#### 4. Create Component
Create `frontend/src/components/newmodel/NewModelCard.tsx`:
```typescript
import type { NewModel } from '../../types';

interface NewModelCardProps {
  item: NewModel;
}

const NewModelCard: React.FC<NewModelCardProps> = ({ item }) => {
  return (
    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <h3 className="text-lg font-semibold text-gray-900">{item.name}</h3>
      {/* More content */}
    </div>
  );
};

export default NewModelCard;
```

#### 5. Create Page
Create `frontend/src/pages/NewModelList.tsx`:
```typescript
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchItems } from '../store/newModelSlice';
import NewModelCard from '../components/newmodel/NewModelCard';

const NewModelList = () => {
  const dispatch = useAppDispatch();
  const { items, loading } = useAppSelector((state) => state.newModel);

  useEffect(() => {
    dispatch(fetchItems());
  }, [dispatch]);

  if (loading) return <div>Loading...</div>;

  return (
    <div className="grid grid-cols-1 gap-6">
      {items.map((item) => (
        <NewModelCard key={item.id} item={item} />
      ))}
    </div>
  );
};

export default NewModelList;
```

#### 6. Add Route
Edit `frontend/src/App.tsx`:
```typescript
import NewModelList from './pages/NewModelList';

// In Routes:
<Route path="new-models" element={<NewModelList />} />
```

### Useful NPM Commands

```bash
cd frontend

# Development
npm run dev          # Start dev server
npm run build        # Build for production
npm run preview      # Preview production build

# Code quality
npm run lint         # Run ESLint
npm run format       # Format code (if configured)

# Dependencies
npm install          # Install dependencies
npm update           # Update dependencies
npm outdated         # Check for outdated packages
```

---

## Database Management

### SQLite (Development)
```bash
# Already configured by default
php artisan migrate --seed
```

### MySQL (Production)
```bash
# 1. Create database
mysql -u root -p
CREATE DATABASE ideahub;
exit;

# 2. Update .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ideahub
DB_USERNAME=root
DB_PASSWORD=your_password

# 3. Run migrations
php artisan migrate --seed
```

### PostgreSQL (Production)
```bash
# 1. Create database
psql -U postgres
CREATE DATABASE ideahub;
\q

# 2. Update .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ideahub
DB_USERNAME=postgres
DB_PASSWORD=your_password

# 3. Run migrations
php artisan migrate --seed
```

### Useful Database Commands

```bash
# Fresh start
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Interact with database
php artisan tinker
>>> User::count()
>>> Idea::where('status', 'approved')->get()
```

---

## Testing

### Backend Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/IdeaTest.php

# Run specific test method
php artisan test --filter=test_user_can_create_idea

# With coverage
php artisan test --coverage
```

#### Writing Tests

Create `tests/Feature/NewFeatureTest.php`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_feature(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/endpoint');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
```

### Frontend Testing

```bash
cd frontend

# Run tests (when configured)
npm run test

# Watch mode
npm run test:watch

# Coverage
npm run test:coverage
```

---

## Debugging

### Backend Debugging

#### Laravel Debugbar (Optional)
```bash
composer require barryvdh/laravel-debugbar --dev
```

#### Logging
```php
// In code
\Log::info('Debug message', ['data' => $variable]);

// View logs
php artisan pail
# or
tail -f storage/logs/laravel.log
```

#### Tinker (REPL)
```bash
php artisan tinker

# Try things out
>>> User::first()
>>> Idea::count()
>>> DB::table('ideas')->where('status', 'approved')->get()
```

### Frontend Debugging

#### Browser DevTools
- **React DevTools** - Component inspector
- **Redux DevTools** - State inspector
- **Network Tab** - API calls
- **Console** - Errors and logs

#### Console Logging
```typescript
console.log('Debug:', variable);
console.table(arrayOfObjects);
console.error('Error:', error);
```

#### Vite Error Overlay
Vite shows errors directly in the browser during development.

---

## Common Tasks

### Adding a New API Endpoint

1. **Create route** in `routes/api.php`
2. **Create controller method**
3. **Add validation**
4. **Test with curl or Postman**

```bash
curl -X POST http://localhost:8000/api/ideas \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","description":"Test desc"}'
```

### Updating Database Schema

```bash
# Create migration
php artisan make:migration add_column_to_table

# Edit migration file, then run
php artisan migrate
```

### Clearing All Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Resetting Development Environment

```bash
# Backend
php artisan migrate:fresh --seed
php artisan cache:clear

# Frontend
cd frontend
rm -rf node_modules package-lock.json
npm install
```

---

## Best Practices

### Backend

1. **Use Form Requests** for validation
2. **Eager load** relationships to avoid N+1 queries
3. **Use database transactions** for multi-step operations
4. **Return consistent** API responses
5. **Write tests** for new features
6. **Use queues** for long-running tasks
7. **Follow PSR-12** coding standards

### Frontend

1. **Type everything** with TypeScript
2. **Use Redux** for global state
3. **Use React Hook Form** for forms
4. **Keep components small** and focused
5. **Use Tailwind** utility classes
6. **Handle loading/error states**
7. **Follow React hooks** best practices

### Git Workflow

1. **Create feature branches** from main
2. **Use conventional commits**:
   - `feat: add new feature`
   - `fix: bug fix`
   - `docs: documentation`
   - `refactor: code refactoring`
3. **Keep commits atomic** and focused
4. **Write descriptive** commit messages
5. **Test before committing**
6. **Squash commits** in PR if needed

---

## Environment Variables

### Backend (.env)
```env
APP_NAME=IdeaHub
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ideahub
# DB_USERNAME=root
# DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173
```

### Frontend (frontend/.env)
```env
VITE_API_URL=http://localhost:8000
VITE_APP_NAME=IdeaHub
VITE_APP_VERSION=1.0.0
```

---

## Troubleshooting

### "Class not found"
```bash
composer dump-autoload
```

### "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### "CORS error in frontend"
- Check `config/cors.php`
- Ensure backend is running
- Verify `VITE_API_URL` in frontend/.env

### "Database locked" (SQLite)
- Close all DB connections
- Restart Laravel server

### "Port already in use"
```bash
# Find process
lsof -i :8000
# Kill process
kill -9 PID
```

---

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Redux Toolkit](https://redux-toolkit.js.org)

---

Happy coding! 🚀
