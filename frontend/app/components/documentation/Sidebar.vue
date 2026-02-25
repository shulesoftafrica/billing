<template>
  <aside class="w-72 flex-shrink-0 border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-y-auto sticky top-16 h-[calc(100vh-4rem)]">
    <div class="p-6">
      <!-- Search -->
      <div class="mb-8">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search documentation..."
            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
            @focus="showSearchResults = true"
          />
          <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      
      <!-- Navigation -->
      <nav class="space-y-8">
        <div v-for="section in navigation" :key="section.title">
          <button
            @click="toggleSection(section.title)"
            class="flex items-center justify-between w-full text-left mb-3 group"
          >
            <h3 class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">
              {{ section.title }}
            </h3>
            <svg 
              class="w-3 h-3 text-gray-500 dark:text-gray-400 transition-transform"
              :class="{ 'rotate-180': expandedSections.has(section.title) }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <ul v-show="expandedSections.has(section.title)" class="space-y-0.5">
            <li v-for="item in section.items" :key="item.href">
              <NuxtLink
                :to="item.href"
                class="block px-3 py-2 text-sm rounded-md transition-colors"
                :class="isActive(item.href) 
                  ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium' 
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
              >
                {{ item.name }}
              </NuxtLink>
              
              <!-- Sub-items -->
              <ul v-if="item.children && (isActive(item.href) || expandedSections.has(item.href))" class="ml-3 mt-0.5 space-y-0.5 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                <li v-for="child in item.children" :key="child.href">
                  <NuxtLink
                    :to="child.href"
                    class="block px-3 py-1.5 text-sm rounded-md transition-colors"
                    :class="isActive(child.href)
                      ? 'text-primary-700 dark:text-primary-400 font-medium'
                      : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'"
                  >
                    {{ child.name }}
                  </NuxtLink>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const searchQuery = ref('')
const showSearchResults = ref(false)
const expandedSections = ref(new Set(['Introduction', 'Core Resources', 'API Reference']))

const toggleSection = (title) => {
  if (expandedSections.value.has(title)) {
    expandedSections.value.delete(title)
  } else {
    expandedSections.value.add(title)
  }
}

const navigation = [
  {
    title: 'Introduction',
    items: [
      { name: 'Getting Started', href: '/docs' },
      { name: 'Authentication', href: '/docs/authentication' },
      { name: 'Making Requests', href: '/docs/making-requests' },
      { name: 'Error Handling', href: '/docs/error-handling' },
      { name: 'Webhooks', href: '/docs/guides/webhooks' },
    ]
  },
  {
    title: 'Core Resources',
    items: [
      { name: 'Customers', href: '/docs/guides/customers' },
      { name: 'Subscriptions', href: '/docs/guides/subscriptions' },
      { name: 'Invoices', href: '/docs/guides/invoices' },
      { name: 'Payments', href: '/docs/guides/payments' },
    ]
  },
  {
    title: 'API Reference',
    items: [
      { 
        name: 'Customers', 
        href: '/docs/api/customers',
        children: [
          { name: 'Create Customer', href: '/docs/api/customers#create' },
          { name: 'List Customers', href: '/docs/api/customers#list' },
          { name: 'Update Customer', href: '/docs/api/customers#update' },
        ]
      },
      { 
        name: 'Subscriptions', 
        href: '/docs/api/subscriptions',
        children: [
          { name: 'Create Subscription', href: '/docs/api/subscriptions#create' },
          { name: 'List Subscriptions', href: '/docs/api/subscriptions#list' },
          { name: 'Cancel Subscription', href: '/docs/api/subscriptions#cancel' },
        ]
      },
      { name: 'Invoices', href: '/docs/api/invoices' },
      { name: 'Payments', href: '/docs/api/payments' },
    ]
  },
  {
    title: 'Resources',
    items: [
      { name: 'Examples', href: '/docs/examples' },
      { name: 'SDKs & Libraries', href: '/docs/sdks' },
      { name: 'Changelog', href: '/docs/changelog' },
      { name: 'Support', href: '/docs/support' },
    ]
  }
]

const isActive = (href) => {
  return route.path === href || route.path.startsWith(href + '/')
}
</script>
