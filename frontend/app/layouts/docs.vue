<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 transition-colors">
    <!-- Top Bar -->
    <div class="sticky top-0 z-50 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
      <div class="flex items-center justify-between h-16 px-6">
        <!-- Logo + Nav -->
        <div class="flex items-center space-x-8">
          <NuxtLink to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">B</span>
            </div>
            <span class="text-xl font-bold text-gray-900 dark:text-white">Billing API</span>
          </NuxtLink>
          
          <!-- Main Tabs -->
          <nav class="hidden md:flex items-center space-x-1">
            <NuxtLink to="/docs" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors" 
              :class="route.path.startsWith('/docs') ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'">
              API Docs
            </NuxtLink>
            <NuxtLink to="/" class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
              Home
            </NuxtLink>
            <NuxtLink to="/pricing" class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
              Pricing
            </NuxtLink>
          </nav>
        </div>
        
        <!-- Right Actions -->
        <div class="flex items-center space-x-4">
          <!-- Language Selector -->
          <select 
            v-model="selectedLanguage"
            class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="curl">cURL</option>
            <option value="node">Node.js</option>
            <option value="python">Python</option>
            <option value="php">PHP</option>
            <option value="ruby">Ruby</option>
          </select>
          
          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDark"
            class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="Toggle dark mode"
          >
            <svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </button>
          
          <!-- Sign In -->
          <NuxtLink to="/auth/login" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            Sign in
          </NuxtLink>
          
          <!-- Dashboard -->
          <NuxtLink to="/dashboard" class="px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            Dashboard
          </NuxtLink>
        </div>
      </div>
    </div>
    
    <!-- Main Layout -->
    <div class="flex">
      <!-- Sidebar Navigation -->
      <DocumentationSidebar :selected-language="selectedLanguage" />
      
      <!-- Content + Code Panel -->
      <div class="flex-1 flex min-h-[calc(100vh-4rem)]">
        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
          <div class="max-w-4xl mx-auto px-8 py-12">
            <slot />
          </div>
        </main>
        
        <!-- Code Examples Panel -->
        <aside class="hidden xl:block w-[480px] bg-gray-900 dark:bg-black border-l border-gray-800 overflow-auto sticky top-16 h-[calc(100vh-4rem)]">
          <div class="p-8">
            <slot name="code" />
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import { useRoute } from 'vue-router'
import DocumentationSidebar from '~/components/documentation/Sidebar.vue'

const route = useRoute()
const { isDark, toggleDark } = useDarkMode()
const selectedLanguage = ref('node')

// Provide language selection to child components
provide('selectedLanguage', selectedLanguage)
</script>
