# IdeaHub Frontend

Modern React 19 + TypeScript frontend for the IdeaHub innovation management platform.

## 🚀 Tech Stack

- **React 19.2.0** - Modern UI library with concurrent features
- **TypeScript 5.9** - Type-safe development
- **Redux Toolkit 2.10** - Predictable state management
- **React Router 7.9** - Client-side routing
- **TailwindCSS 4.1** - Utility-first styling
- **Vite 7.2** - Lightning-fast build tool
- **Axios 1.13** - HTTP client
- **TanStack Query 5.90** - Server state management
- **React Hook Form 7.66** - Form handling
- **Zod 4.1** - Schema validation
- **Heroicons 2.2** - Beautiful SVG icons

## 📦 Installation

```bash
# Install dependencies
npm install

# Create environment file
cp .env.example .env

# Start development server
npm run dev
```

The application will be available at `http://localhost:5173`

## 🛠️ Development Scripts

```bash
# Development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run ESLint
npm run lint
```

## 🌍 Environment Variables

Create a `.env` file in the frontend directory:

```env
# Backend API URL
VITE_API_URL=http://localhost:8000

# Application info
VITE_APP_NAME=IdeaHub
VITE_APP_VERSION=1.0.0
```

## 📁 Project Structure

```
frontend/
├── src/
│   ├── components/          # Reusable components
│   │   ├── auth/           # Authentication components
│   │   ├── comments/       # Comment components
│   │   ├── common/         # Common UI components
│   │   ├── ideas/          # Idea-related components
│   │   └── layout/         # Layout components
│   ├── pages/              # Page components (routes)
│   │   ├── Dashboard.tsx   # Main dashboard
│   │   ├── Ideas.tsx       # Ideas listing with filters
│   │   ├── MyIdeas.tsx     # User's own ideas
│   │   ├── IdeaDetail.tsx  # Single idea view
│   │   ├── CreateIdea.tsx  # Create/edit idea form
│   │   ├── Login.tsx       # Login page
│   │   └── Register.tsx    # Registration page
│   ├── services/           # API service layer
│   │   ├── api.ts          # Axios instance & interceptors
│   │   ├── authService.ts  # Auth API calls
│   │   ├── ideaService.ts  # Ideas API calls
│   │   ├── commentService.ts
│   │   ├── categoryService.ts
│   │   └── tagService.ts
│   ├── store/              # Redux store
│   │   ├── index.ts        # Store configuration
│   │   ├── hooks.ts        # Typed Redux hooks
│   │   ├── authSlice.ts    # Auth state
│   │   ├── ideasSlice.ts   # Ideas state
│   │   ├── categoriesSlice.ts
│   │   └── tagsSlice.ts
│   ├── types/              # TypeScript type definitions
│   │   └── index.ts        # All type definitions
│   ├── utils/              # Utility functions
│   │   ├── formatters.ts   # Date, text formatters
│   │   └── statusHelpers.ts # Status-related helpers
│   ├── App.tsx             # Root component with routing
│   ├── main.tsx            # Application entry point
│   └── index.css           # Global styles
├── public/                 # Static assets
├── .env                    # Environment variables
├── .env.example            # Environment template
├── index.html              # HTML entry point
├── package.json            # Dependencies
├── tailwind.config.js      # Tailwind configuration
├── tsconfig.json           # TypeScript configuration
└── vite.config.ts          # Vite configuration
```

## 🔐 Authentication

The application uses JWT-based authentication:

1. **Login/Register** - User authenticates and receives a token
2. **Token Storage** - Token stored in `localStorage`
3. **API Requests** - Token automatically added to all requests via Axios interceptor
4. **Protected Routes** - Unauthenticated users redirected to login
5. **Auto Logout** - 401 responses clear token and redirect to login

### Demo Credentials

```
Email: admin@ideahub.test
Password: password
```

## 📱 Features

### ✅ Implemented

- **Authentication**
  - User registration with profile info
  - Login with email/password
  - Protected routes
  - Persistent sessions

- **Dashboard**
  - Stats overview
  - Recent ideas feed
  - Category browser
  - Quick actions

- **Ideas Management**
  - Browse all ideas with pagination
  - Filter by status, category, search
  - Sort by date, likes, comments, views
  - Create new ideas (with drafts)
  - View idea details
  - Like ideas
  - Submit ideas for review
  - Edit/delete own ideas (status-based permissions)

- **Comments**
  - View comments on ideas
  - Post new comments
  - Edit own comments
  - Delete own comments
  - Like comments

- **My Ideas**
  - View all user's ideas
  - Grouped by status
  - Quick stats

- **UI Components**
  - Responsive navigation
  - Status badges
  - Category chips
  - Tag pills
  - User avatars
  - Loading states
  - Error handling

### 🚧 Future Enhancements

- Approval workflow UI
- Real-time notifications
- File attachments
- Idea analytics
- User profiles
- Settings page
- Dark mode
- Mobile app
- Advanced search
- Idea templates

## 🎨 Styling

The application uses TailwindCSS 4.x with a custom configuration:

- **Colors:** Blue primary, gray neutral
- **Responsive:** Mobile-first design
- **Animations:** Smooth transitions
- **Icons:** Heroicons for consistency

### Custom Styles

Global styles are in `src/index.css`:

```css
@import "tailwindcss";
```

## 🧪 API Integration

All API calls go through the centralized Axios instance in `services/api.ts`:

- **Base URL:** `${VITE_API_URL}/api`
- **Headers:** JSON content type, authentication bearer token
- **Interceptors:** Auto-add auth token, handle 401 errors
- **CORS:** withCredentials enabled for cookie support

### Service Pattern

```typescript
// Example: ideaService.ts
export const ideaService = {
  getIdeas: async (filters?: IdeaFilters) => {
    const response = await api.get('/ideas', { params: filters });
    return response.data;
  },
  // ... more methods
};
```

## 🔄 State Management

Redux Toolkit slices manage different domains:

- **authSlice** - User authentication and profile
- **ideasSlice** - Ideas list, filters, current idea
- **categoriesSlice** - Categories list
- **tagsSlice** - Tags list

### Using Redux

```typescript
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchIdeas, likeIdea } from '../store/ideasSlice';

const MyComponent = () => {
  const dispatch = useAppDispatch();
  const { ideas, loading } = useAppSelector((state) => state.ideas);

  useEffect(() => {
    dispatch(fetchIdeas());
  }, []);

  const handleLike = (id: number) => {
    dispatch(likeIdea(id));
  };

  // ...
};
```

## 🚦 Routing

Routes are defined in `App.tsx`:

- `/` - Redirects to dashboard (protected)
- `/login` - Login page (public)
- `/register` - Registration page (public)
- `/dashboard` - Main dashboard (protected)
- `/ideas` - Browse ideas (protected)
- `/ideas/my` - User's own ideas (protected)
- `/ideas/create` - Create new idea (protected)
- `/ideas/:id` - View idea details (protected)
- `/ideas/:id/edit` - Edit idea (protected)

## 📝 Type Safety

All entities have TypeScript interfaces in `src/types/index.ts`:

```typescript
export interface Idea {
  id: number;
  title: string;
  description: string;
  status: IdeaStatus;
  // ... more fields
}

export type IdeaStatus =
  | 'draft'
  | 'submitted'
  | 'under_review'
  | 'approved'
  | 'rejected'
  | 'implemented'
  | 'archived';
```

## 🔧 Development Tips

### Hot Module Replacement

Vite provides instant HMR. Changes appear immediately without full page reload.

### Redux DevTools

Install the Redux DevTools browser extension to inspect state changes.

### TypeScript Strict Mode

The project uses TypeScript strict mode for maximum type safety.

### Code Formatting

- Use ESLint for linting
- Follow React/TypeScript best practices
- Use functional components with hooks

## 🏗️ Building for Production

```bash
# Build the application
npm run build

# Output is in the dist/ directory
# dist/
# ├── index.html
# ├── assets/
# │   ├── index-[hash].js
# │   └── index-[hash].css
# └── ...
```

### Build Optimization

- Code splitting by route
- Tree shaking for unused code
- Minification and compression
- Asset optimization

## 🐛 Troubleshooting

### Port Already in Use

Change the port in `vite.config.ts` or kill the process using port 5173.

### API Connection Issues

Check that:
1. Backend is running on `http://localhost:8000`
2. `VITE_API_URL` in `.env` is correct
3. CORS is configured on the backend

### Type Errors

Run `npm run build` to check for TypeScript errors.

## 📄 License

MIT License - See LICENSE file for details

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

---

Built with ❤️ using React, TypeScript, and modern web technologies.
