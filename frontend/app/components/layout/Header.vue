<template>
  <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
    <div class="container-custom">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xl">B</span>
          </div>
          <span class="text-xl font-bold text-gray-900">Billing API</span>
        </NuxtLink>
        
        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <NuxtLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="text-gray-600 hover:text-gray-900 font-medium transition-colors"
          >
            {{ item.name }}
          </NuxtLink>
        </nav>
        
        <!-- Right side actions -->
        <div class="flex items-center space-x-4">
          <template v-if="!isAuthenticated">
            <NuxtLink to="/auth/login" class="text-gray-600 hover:text-gray-900 font-medium">
              Sign In
            </NuxtLink>
            <Button to="/auth/signup" variant="primary">
              Get Started Free
            </Button>
          </template>
          
          <template v-else>
            <!-- Mode Toggle -->
            <button
              @click="toggleMode"
              class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
            >
              <span class="text-sm font-medium" :class="isTestMode ? 'text-warning-600' : 'text-success-600'">
                {{ isTestMode ? 'Test' : 'Live' }}
              </span>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
              </svg>
            </button>
            
            <!-- User Menu -->
            <div class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-2 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                  <span class="text-white text-sm font-medium">{{ userInitials }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 border border-gray-200"
              >
                <NuxtLink
                  v-for="item in userMenuItems"
                  :key="item.name"
                  :to="item.href"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  {{ item.name }}
                </NuxtLink>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-error-600 hover:bg-gray-50"
                >
                  Sign Out
                </button>
              </div>
            </div>
          </template>
          
          <!-- Mobile menu button -->
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="md:hidden p-2 rounded-lg hover:bg-gray-50"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Mobile menu -->
      <div v-if="showMobileMenu" class="md:hidden py-4 border-t border-gray-200">
        <nav class="flex flex-col space-y-4">
          <NuxtLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="text-gray-600 hover:text-gray-900 font-medium"
            @click="showMobileMenu = false"
          >
            {{ item.name }}
          </NuxtLink>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import Button from '../shared/Button.vue'

const navigation = [
  { name: 'Documentation', href: '/docs' },
  { name: 'API Reference', href: '/docs/api-reference' },
  { name: 'Pricing', href: '/pricing' },
]

const userMenuItems = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'API Keys', href: '/dashboard/api-keys' },
  { name: 'Settings', href: '/dashboard/settings' },
]

const isAuthenticated = ref(false) // TODO: Connect to auth store
const isTestMode = ref(true)
const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const userInitials = computed(() => {
  // TODO: Get from user data
  return 'JD'
})

const toggleMode = () => {
  isTestMode.value = !isTestMode.value
  // TODO: Update API mode in store
}

const handleLogout = () => {
  showUserMenu.value = false
  // TODO: Implement logout
  console.log('Logout')
}
</script>
