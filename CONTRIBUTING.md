# Contributing to IdeaHub

Thank you for your interest in contributing to IdeaHub! We welcome contributions from the community and are grateful for your support.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Submitting Changes](#submitting-changes)
- [Reporting Bugs](#reporting-bugs)
- [Requesting Features](#requesting-features)
- [Testing Guidelines](#testing-guidelines)

## Code of Conduct

This project adheres to a Code of Conduct that all contributors are expected to follow. Please be respectful and constructive in all interactions.

### Our Standards

- **Be Respectful:** Treat everyone with respect and courtesy
- **Be Collaborative:** Work together and help each other succeed
- **Be Professional:** Keep discussions focused and productive
- **Be Inclusive:** Welcome contributors of all backgrounds and skill levels

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP** >= 8.2
- **Node.js** >= 18.x
- **Composer** >= 2.x
- **Git** for version control
- A **GitHub account**

### Setting Up Your Development Environment

1. **Fork the repository** on GitHub
2. **Clone your fork locally:**
   ```bash
   git clone https://github.com/YOUR_USERNAME/ideahub.git
   cd ideahub
   ```

3. **Add upstream remote:**
   ```bash
   git remote add upstream https://github.com/ankur2194/IdeaHub.git
   ```

4. **Install dependencies:**
   ```bash
   # Backend dependencies
   composer install

   # Frontend dependencies
   cd frontend
   npm install
   cd ..
   ```

5. **Set up environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   cp frontend/.env.example frontend/.env
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate --seed
   ```

7. **Start development servers:**
   ```bash
   composer dev
   ```

## Development Workflow

### Creating a Feature Branch

Always create a new branch for your work:

```bash
# Sync with upstream
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name
```

### Branch Naming Conventions

- **Features:** `feature/description-of-feature`
- **Bug Fixes:** `fix/description-of-bug`
- **Documentation:** `docs/description-of-change`
- **Refactoring:** `refactor/description-of-change`
- **Tests:** `test/description-of-test`

### Making Changes

1. **Write code** following our coding standards
2. **Test your changes** thoroughly
3. **Update documentation** if needed
4. **Run code formatters** and linters
5. **Commit your changes** with clear messages

## Coding Standards

### Backend (PHP/Laravel)

#### PSR-12 Compliance

All PHP code must follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.

#### Formatting

Use Laravel Pint for automatic code formatting:

```bash
composer format
```

#### Best Practices

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Example extends Model
{
    // Use strict typing
    protected $fillable = ['name', 'email'];

    // Use casts() method (Laravel 12+)
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // Use return type declarations
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

#### Guidelines

- ✅ Use Eloquent ORM, avoid raw SQL queries
- ✅ Use dependency injection
- ✅ Keep controllers thin, use services for business logic
- ✅ Use proper exception handling
- ✅ Validate all input with Form Requests
- ✅ Use database transactions for multi-step operations
- ❌ Don't use deprecated Laravel features
- ❌ Don't bypass validation or security measures

### Frontend (React/TypeScript)

#### ESLint Compliance

All TypeScript/React code must pass ESLint checks:

```bash
cd frontend
npm run lint
```

#### Best Practices

```typescript
// Use functional components with TypeScript
interface ComponentProps {
  title: string;
  onAction?: () => void;
}

export const Component: React.FC<ComponentProps> = ({
  title,
  onAction
}) => {
  // Use hooks at the top
  const [state, setState] = useState(false);

  // Use proper event handlers
  const handleClick = () => {
    setState(!state);
    onAction?.();
  };

  // Use Tailwind CSS classes
  return (
    <div className="rounded-lg bg-white p-4 shadow">
      <h2 className="text-xl font-semibold">{title}</h2>
      <button onClick={handleClick}>Action</button>
    </div>
  );
};
```

#### Guidelines

- ✅ Use TypeScript strict mode
- ✅ Define interfaces for all props
- ✅ Use functional components with hooks
- ✅ Use Tailwind CSS for styling
- ✅ Use React Hook Form for forms
- ✅ Use Redux Toolkit for global state
- ✅ Use TanStack Query for server state
- ❌ Don't use `any` type - use `unknown` instead
- ❌ Don't use inline styles - use Tailwind
- ❌ Don't bypass type checking

### Git Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

#### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

#### Examples

```bash
feat(ideas): add like functionality to ideas
fix(auth): resolve token expiration issue
docs(readme): update installation instructions
test(ideas): add tests for idea creation
refactor(comments): simplify comment component logic
```

## Submitting Changes

### Pull Request Process

1. **Ensure your code passes all checks:**
   ```bash
   # Backend
   composer format
   php artisan test

   # Frontend
   cd frontend
   npm run lint
   npm run build
   ```

2. **Update your branch with latest upstream:**
   ```bash
   git checkout main
   git pull upstream main
   git checkout your-feature-branch
   git rebase main
   ```

3. **Push to your fork:**
   ```bash
   git push origin your-feature-branch
   ```

4. **Create a Pull Request on GitHub:**
   - Go to your fork on GitHub
   - Click "New Pull Request"
   - Select your branch
   - Fill out the PR template

### Pull Request Guidelines

#### Title

Use a clear, descriptive title following conventional commit format:

```
feat: Add user profile page
fix: Resolve authentication redirect issue
```

#### Description

Include:

- **Summary:** What changes does this PR make?
- **Motivation:** Why are these changes needed?
- **Changes:** List of specific changes made
- **Testing:** How were these changes tested?
- **Screenshots:** If UI changes, include before/after screenshots

#### Template

```markdown
## Summary
Brief description of the changes

## Motivation
Why these changes are needed

## Changes
- Change 1
- Change 2
- Change 3

## Testing
- [ ] Tested locally
- [ ] Added unit tests
- [ ] Added feature tests
- [ ] Tested on different browsers/devices (if applicable)

## Screenshots (if applicable)
Before: [screenshot]
After: [screenshot]
```

### Code Review Process

- All PRs require at least one approval
- Address all review comments
- Keep discussions respectful and constructive
- Be open to feedback and suggestions

## Reporting Bugs

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Test on the latest version** to ensure bug still exists
3. **Gather information** about your environment

### Bug Report Template

```markdown
## Bug Description
Clear description of the bug

## Steps to Reproduce
1. Step 1
2. Step 2
3. Step 3

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- OS: [e.g., Ubuntu 22.04]
- PHP Version: [e.g., 8.2.1]
- Laravel Version: [e.g., 12.0]
- Node Version: [e.g., 18.17.0]
- Browser: [e.g., Chrome 120]

## Screenshots
If applicable, add screenshots

## Additional Context
Any other relevant information
```

## Requesting Features

### Feature Request Template

```markdown
## Feature Description
Clear description of the proposed feature

## Use Case
Why is this feature needed? What problem does it solve?

## Proposed Solution
How should this feature work?

## Alternatives Considered
What other solutions have you considered?

## Additional Context
Any other relevant information
```

## Testing Guidelines

### Backend Testing

All new features must include tests:

```bash
# Create test file
php artisan make:test Feature/YourFeatureTest

# Run tests
php artisan test

# Run specific test
php artisan test --filter=YourFeatureTest
```

#### Test Example

```php
namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdeaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_idea(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/ideas', [
                'title' => 'Test Idea',
                'description' => 'Test Description',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('ideas', [
            'title' => 'Test Idea',
        ]);
    }
}
```

### Frontend Testing

When frontend testing is set up (Vitest), include component tests:

```typescript
import { render, screen } from '@testing-library/react';
import { IdeaCard } from './IdeaCard';

describe('IdeaCard', () => {
  it('renders idea title', () => {
    render(<IdeaCard title="Test Idea" description="Test" author="User" />);
    expect(screen.getByText('Test Idea')).toBeInTheDocument();
  });
});
```

## Documentation

### When to Update Documentation

Update documentation when:

- Adding new features
- Changing existing functionality
- Updating configuration requirements
- Adding new dependencies
- Changing API endpoints

### Documentation Files

- **README.md:** Project overview and quick start
- **CLAUDE.md:** Development guide for AI assistants
- **docs/api.md:** API documentation
- **docs/deployment.md:** Deployment guide
- **Code Comments:** For complex logic

## Development Tips

### Debugging

#### Backend

```bash
# View logs in real-time
php artisan pail

# Use Tinker REPL
php artisan tinker

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

#### Frontend

- Use React DevTools browser extension
- Use Redux DevTools for state inspection
- Check browser console for errors

### Common Issues

#### Composer Dependencies

```bash
composer install --ignore-platform-reqs
```

#### Node Modules

```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

#### Database Issues

```bash
php artisan migrate:fresh --seed
```

## Getting Help

- **Documentation:** Check [CLAUDE.md](CLAUDE.md) for detailed guides
- **Issues:** Search existing GitHub issues
- **Discussions:** Use GitHub Discussions for questions

## Recognition

Contributors will be:

- Listed in the project contributors
- Credited in release notes
- Appreciated by the community!

## License

By contributing to IdeaHub, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to IdeaHub! 🎉
