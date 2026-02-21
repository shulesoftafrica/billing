# Billing API - Frontend Application

> Developer-first billing platform front-end built with Nuxt 3, Vue 3, and Tailwind CSS

## 🚀 What We've Built

A comprehensive front-end application for the Billing API platform based on the [FRONTEND-DESIGN.md](../resources/requirements/FRONTEND-DESIGN.md) specifications.

### ✅ Completed Features

#### 1. **Project Setup & Configuration**
- ✅ Nuxt 4.3.1 with Vue 3.5.28
- ✅ Tailwind CSS with custom design system
- ✅ Complete color palette and typography system
- ✅ Responsive layout configurations

#### 2. **Design System Components**
- ✅ **Button** - Multiple variants (primary, secondary, success, ghost, outline) with loading states
- ✅ **Card** - Flexible card component with header, body, footer slots
- ✅ **Input** - Form input with validation, icons, and password toggle
- ✅ Tailwind utility classes for badges, status dots, code blocks

#### 3. **Layout Components**
- ✅ **Header** - Sticky navigation with authentication states, mode toggle (Test/Live)
- ✅ **Footer** - Multi-column footer with links and social media
- ✅ **Default Layout** - Main layout wrapper with header and footer

#### 4. **Marketing Homepage** (`/`)
Complete homepage with all sections from the design:
- ✅ Hero section with value proposition and code sample
- ✅ Features grid (6 feature cards with hover effects)
- ✅ **Interactive Earnings Calculator**
  - Real-time calculation of earnings
  - Comparison with traditional payment gateways
  - Visual breakdown of advantages
- ✅ "How It Works" timeline (4-step process)
- ✅ Social proof section with testimonials and statistics
- ✅ Final CTA section

### 📁 Project Structure

```
frontend/
├── app/
│   ├── assets/
│   │   └── css/
│   │       └── main.css          # Tailwind + Custom CSS
│   ├── components/
│   │   ├── dashboard/            # Dashboard components (TODO)
│   │   ├── documentation/        # Docs components (TODO)
│   │   ├── forms/                # Form components (TODO)
│   │   ├── layout/
│   │   │   ├── Header.vue        ✅ Complete
│   │   │   └── Footer.vue        ✅ Complete
│   │   ├── marketing/            # Marketing components (TODO)
│   │   └── shared/
│   │       ├── Button.vue        ✅ Complete
│   │       ├── Card.vue          ✅ Complete
│   │       └── Input.vue         ✅ Complete
│   ├── composables/              # Vue composables
│   ├── layouts/
│   │   └── default.vue           ✅ Complete
│   ├── pages/
│   │   └── index.vue             ✅ Homepage Complete
│   ├── utils/                    # Utility functions
│   └── app.vue                   ✅ Root component
├── public/                       # Static assets
├── nuxt.config.ts                ✅ Configured
├── tailwind.config.js            ✅ Custom theme
└── package.json                  ✅ Dependencies installed
```

## 🎨 Design System

### Colors
- **Primary**: Blue (#2563eb) - Main brand color
- **Success**: Green (#059669) - Earnings, positive actions
- **Warning**: Amber (#d97706) - Attention items
- **Error**: Red (#ef4444) - Errors, failures

### Typography
- **Font**: Inter (sans-serif) for UI, JetBrains Mono for code
- **Scale**: xs (12px) → 6xl (60px)

### Components
All components follow consistent patterns:
- Variant-based styling (primary, secondary, etc.)
- Size options (sm, md, lg)
- Accessibility-first (ARIA labels, keyboard navigation)
- Loading and disabled states

## 🏃 Running the Application

### Prerequisites
- Node.js 18+ and npm
- Git

### Installation & Setup

1. **Navigate to frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install dependencies** (if not already done):
   ```bash
   npm install
   ```

3. **Run development server:**
   ```bash
   npm run dev
   ```

4. **Open your browser:**
   ```
   http://localhost:3000
   ```
   (If port 3000 is in use, Nuxt will automatically use 3001, 3002, etc.)

### Available Scripts

```bash
npm run dev        # Start development server
npm run build      # Build for production
npm run generate   # Generate static site
npm run preview    # Preview production build
```

## 📋 Next Steps (TODO)

Based on the [FRONTEND-DESIGN.md](../resources/requirements/FRONTEND-DESIGN.md), here's what needs to be built next:

### Phase 2: Documentation Hub (Week 4-6)
- [ ] Documentation layout (3-column: nav, content, TOC)
- [ ] Search functionality (Algolia DocSearch integration)
- [ ] API Reference pages with interactive examples
- [ ] Quick Start guide page
- [ ] Code syntax highlighting (Shiki/Prism)
- [ ] "Try in Playground" buttons

### Phase 3: Authentication (Week 7-8)
- [ ] Sign Up page (`/auth/signup`)
- [ ] Login page (`/auth/login`)
- [ ] Email verification page
- [ ] Password reset flow
- [ ] Onboarding wizard (3 steps: KYC, Test, Live)

### Phase 4: Dashboard (Week 9-12)
- [ ] Dashboard layout with sidebar
- [ ] Overview page with KPIs and charts
- [ ] API Keys management page
- [ ] Customers CRUD pages
- [ ] Transactions list and details
- [ ] Invoices management
- [ ] Subscriptions management
- [ ] Settlements tracking
- [ ] Webhooks configuration
- [ ] Settings page

### Phase 5: Advanced Features (Week 13-16)
- [ ] API Playground (split-pane request/response)
- [ ] Real-time transaction feed (WebSocket)
- [ ] Analytics dashboard
- [ ] Earnings widget with charts
- [ ] KYC document upload component
- [ ] Interactive code examples
- [ ] Mode switching (Test ↔ Live)

### Phase 6: Integration (Week 17-20)
- [ ] Connect to Laravel backend API
- [ ] Authentication service (Laravel Sanctum)
- [ ] API service layer with axios
- [ ] State management (Pinia)
- [ ] Error handling and toasts
- [ ] Loading states and skeleton screens

### Phase 7: Polish (Week 21-24)
- [ ] Performance optimization
- [ ] SEO meta tags for all pages
- [ ] Accessibility audit (WCAG 2.1 AA)
- [ ] Mobile responsiveness testing
- [ ] Cross-browser testing
- [ ] End-to-end tests (Playwright)

## 🔧 Configuration

### Environment Variables

Create a `.env` file in the frontend directory:

```bash
# API Configuration
NUXT_PUBLIC_API_BASE=http://localhost:8000/api

# Feature Flags
NUXT_PUBLIC_ENABLE_ANALYTICS=false
```

### Connecting to Laravel Backend

The API base URL is configured in `nuxt.config.ts`:

```javascript
runtimeConfig: {
  public: {
    apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
  },
}
```

## 🎯 Key Features

### ✅ Implemented
- [x] Responsive header with navigation
- [x] Interactive earnings calculator
- [x] Feature showcase grid
- [x] Social proof section
- [x] Design system with Tailwind
- [x] Reusable UI components

### 📝 Planned
- [ ] Documentation hub
- [ ] Authentication flow
- [ ] Dashboard components
- [ ] API playground
- [ ] Real-time updates

## 📚 Resources

- [Nuxt 3 Documentation](https://nuxt.com)
- [Vue 3 Documentation](https://vuejs.org)
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Design Specifications](../resources/requirements/FRONTEND-DESIGN.md)

---

**Built with ❤️ following world-class API documentation standards** (Stripe, Twilio, Plaid)

# yarn
yarn build

# bun
bun run build
```

Locally preview production build:

```bash
# npm
npm run preview

# pnpm
pnpm preview

# yarn
yarn preview

# bun
bun run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.
