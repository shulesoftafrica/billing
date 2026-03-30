# New Documentation System - Architecture Guide

## 🎯 Overview

The API documentation has been completely restructured from a single markdown-based file to a **modular, component-based Blade system**. This makes it much easier to update individual endpoints without dealing with massive markdown files.

---

## 📁 File Structure

```
resources/views/docs/
├── index.blade.php              # Main entry point
├── components/                  # Reusable UI components
│   ├── endpoint.blade.php       # API endpoint card component
│   ├── code-block.blade.php     # Syntax-highlighted code display
│   ├── parameter-table.blade.php # Parameter tables
│   └── code-tabs.blade.php      # Multi-language code examples
├── sections/                    # Documentation sections
│   ├── authentication.blade.php # OAuth 2.0 authentication guide
│   └── products.blade.php       # Product endpoints
└── partials/                    # Layout partials
    ├── sidebar.blade.php        # Navigation sidebar
    ├── topbar.blade.php         # Top navigation bar
    ├── styles.blade.php         # All CSS styles
    └── scripts.blade.php        # All JavaScript functionality
```

---

## 🚀 How to Use

### 1. **Adding a New Endpoint**

To add a new endpoint to an existing section (e.g., Products):

**Edit:** `resources/views/docs/sections/products.blade.php`

```blade
<x-docs.endpoint 
    id="create-product"
    method="POST" 
    url="/api/v1/products"
    title="Create Product"
    description="Create a new product with pricing tiers">
    
    <x-slot name="headers">
        <x-docs.parameter-table :parameters="[
            ['key' => 'Authorization', 'value' => 'Bearer {token}', 'required' => true],
            ['key' => 'Accept', 'value' => 'application/json', 'required' => true]
        ]"/>
    </x-slot>

    <x-slot name="requestBody">
        <x-docs.code-block language="json" :code='"{
    \"organization_id\": 1,
    \"product_name\": \"Cloud Hosting\",
    \"product_code\": \"cloud-basic\",
    \"price_plans\": [
        {
            \"billing_cycle\": \"monthly\",
            \"price\": 9.99,
            \"currency\": \"USD\"
        }
    ]
}"'/>
    </x-slot>

    <x-slot name="responses">
        <x-docs.code-block language="json" label="200 Success" :code='"{
    \"success\": true,
    \"message\": \"Product created successfully\"
}"'/>
    </x-slot>
</x-docs.endpoint>
```

**Then add the navigation link in:** `resources/views/docs/partials/sidebar.blade.php`

```blade
<a href="#create-product" class="nav-link">
    <span class="method-badge method-post">POST</span>
    Create Product
</a>
```

---

### 2. **Creating a New Section** (e.g., Invoices)

**Create:** `resources/views/docs/sections/invoices.blade.php`

```blade
<section id="invoices" class="api-section">
    <h2>📄 Invoices</h2>
    <p>Manage customer invoices and billing records.</p>

    {{-- List All Invoices --}}
    <x-docs.endpoint 
        id="list-invoices"
        method="GET" 
        url="/api/v1/invoices"
        title="List All Invoices"
        description="Retrieve a paginated list of all invoices">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {token}', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="responses">
            <x-docs.code-block language="json" :code='"{
    \"data\": [...],
    \"meta\": {...}
}"'/>
        </x-slot>
    </x-docs.endpoint>

    {{-- Add more invoice endpoints here --}}
</section>
```

**Include it in:** `resources/views/docs/index.blade.php`

```blade
<div class="content">
    @include('docs.sections.authentication')
    @include('docs.sections.products')
    @include('docs.sections.invoices')  {{-- ADD THIS LINE --}}
</div>
```

**Add sidebar navigation in:** `resources/views/docs/partials/sidebar.blade.php`

```blade
<div class="nav-section">
    <button type="button" class="nav-section-toggle">
        <span>📄 Invoices</span>
        <span>▾</span>
    </button>
    <div class="nav-links">
        <a href="#list-invoices" class="nav-link">
            <span class="method-badge method-get">GET</span>
            List All Invoices
        </a>
        {{-- More invoice endpoints --}}
    </div>
</div>
```

---

### 3. **Multi-Language Code Examples**

Use `x-docs.code-tabs` for showing examples in multiple languages:

```blade
<x-docs.code-tabs :examples="[
    [
        'label' => 'cURL',
        'language' => 'bash',
        'code' => 'curl -X POST https://api.example.com/products...'
    ],
    [
        'label' => 'PHP',
        'language' => 'php',
        'code' => '\$client = new GuzzleHttp\Client();...'
    ],
    [
        'label' => 'JavaScript',
        'language' => 'javascript',
        'code' => 'const response = await fetch(...);'
    ],
    [
        'label' => 'Python',
        'language' => 'python',
        'code' => 'import requests\nresponse = requests.post(...)'
    ]
]"/>
```

---

## 🎨 Available Components

### 1. `<x-docs.endpoint>`
Full API endpoint card with accordion

**Props:**
- `id`: Unique identifier for anchor links
- `method`: HTTP method (GET, POST, PUT, PATCH, DELETE)
- `url`: Endpoint URL
- `title`: Endpoint title
- `description`: Brief description

**Slots:**
- `headers`: Request headers
- `requestBody`: Request body example
- `responses`: Response examples

---

### 2. `<x-docs.code-block>`
Syntax-highlighted code block with copy button

**Props:**
- `code`: Code string to display
- `language`: Syntax highlighting language (json, php, javascript, python, bash)
- `label`: Optional label/title

---

### 3. `<x-docs.parameter-table>`
Table displaying parameters

**Props:**
- `parameters`: Array of parameters with `key`, `value`, `description`, `required`

---

### 4. `<x-docs.code-tabs>`
Tabbed code examples for multiple languages

**Props:**
- `examples`: Array of examples with `label`, `language`, `code`

---

## 🔧 Features

### ✅ Implemented
- ✅ Dark/Light theme toggle (saved to localStorage)
- ✅ Mobile-responsive sidebar
- ✅ Search functionality (filters sidebar and content)
- ✅ Accordion-style endpoints
- ✅ Syntax highlighting (Prism.js)
- ✅ Copy-to-clipboard buttons
- ✅ Smooth scrolling with offset
- ✅ Active section highlighting in sidebar
- ✅ Tab switching for multi-language examples
- ✅ Authentication guide with OAuth 2.0 flow
- ✅ Product endpoints with SafariChat 4-tier example
- ✅ Get Single Product (by ID or code)

---

## 📝 Current Endpoints

### Authentication
- OAuth 2.0 Client Credentials flow guide
- Step-by-step token generation
- cURL, PHP, and Python examples

### Products
1. **GET /api/v1/products** - List All Products (with filters)
2. **POST /api/v1/products** - Create Product (with SafariChat 4-tier example)
3. **GET /api/v1/products/{product}** - Get Single Product (by ID or code)

---

## 🗑️ Old System (To Be Archived)

The following files are no longer used and can be archived/deleted after confirming the new system works:

- `resources/views/api-documentation.blade.php` (1718 lines - old monolithic file)
- `docs/api-documentation.md` (6628 lines - markdown source)

---

## 🚦 Testing Checklist

Before archiving the old system, verify:

- [ ] All endpoints display correctly
- [ ] Accordion open/close works
- [ ] Copy buttons work
- [ ] Tab switching works
- [ ] Search/filter works
- [ ] Mobile sidebar toggle works
- [ ] Theme toggle works (dark/light)
- [ ] Smooth scroll to anchors works
- [ ] Active section highlighting works
- [ ] All syntax highlighting displays correctly
- [ ] SafariChat 4-tier example displays correctly
- [ ] Get Single Product shows both ID and code methods

---

## 🔄 Migration Status

✅ **COMPLETED:**
- Created component-based architecture
- Created authentication section
- Created products section with 3 endpoints
- Included SafariChat 4-tier product example
- Included Get Single Product (ID + code methods)
- Created all partials (sidebar, topbar, styles, scripts)
- Updated route to point to new `docs.index` view
- Cleared all Laravel caches

⏳ **PENDING:**
- Create remaining sections (invoices, subscriptions, tax-rates, payments, webhooks)
- Test all functionality thoroughly
- Archive old markdown-based system

---

## 💡 Quick Tips

1. **Updating an endpoint?** 
   → Edit the section file in `resources/views/docs/sections/`

2. **Adding a new section?** 
   → Create new file in `sections/`, include it in `index.blade.php`, and add sidebar navigation

3. **Changes not showing?**
   → Run: `php artisan view:clear; php artisan config:clear; php artisan cache:clear`

4. **Want to customize styles?**
   → Edit `resources/views/docs/partials/styles.blade.php`

5. **Need to add JavaScript functionality?**
   → Edit `resources/views/docs/partials/scripts.blade.php`

---

## 🎯 Summary

**Before:** One massive markdown file (6628 lines) parsed by complex PHP logic

**After:** Modular Blade components that are easy to understand and update

**Result:** Maintainable, searchable, beautiful API documentation that doesn't rely on markdown parsing! ✨
