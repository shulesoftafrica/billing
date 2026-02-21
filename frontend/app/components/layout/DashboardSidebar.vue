<template>
  <div>
    <!-- Mobile sidebar overlay -->
    <div 
      v-if="sidebarOpen" 
      class="fixed inset-0 z-40 lg:hidden"
      @click="$emit('close')"
    >
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
    </div>

    <!-- Sidebar -->
    <div
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
        <NuxtLink to="/dashboard" class="flex items-center">
          <span class="text-xl font-bold bg-gradient-to-r from-primary-600 to-success-600 bg-clip-text text-transparent">
            Billing Platform
          </span>
        </NuxtLink>
        <button
          @click="$emit('close')"
          class="lg:hidden text-gray-400 hover:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <!-- Dashboard Section -->
        <div class="mb-6">
          <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
            Dashboard
          </h3>
          <NuxtLink
            v-for="item in dashboardLinks"
            :key="item.name"
            :to="item.href"
            class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="isActive(item.href) 
              ? 'bg-primary-50 text-primary-600' 
              : 'text-gray-700 hover:bg-gray-50'"
          >
            <span v-html="item.icon" class="w-5 h-5 mr-3"></span>
            {{ item.name }}
          </NuxtLink>
        </div>

        <!-- Management Section -->
        <div class="mb-6">
          <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
            Management
          </h3>
          <NuxtLink
            v-for="item in managementLinks"
            :key="item.name"
            :to="item.href"
            class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="isActive(item.href) 
              ? 'bg-primary-50 text-primary-600' 
              : 'text-gray-700 hover:bg-gray-50'"
          >
            <span v-html="item.icon" class="w-5 h-5 mr-3"></span>
            {{ item.name }}
          </NuxtLink>
        </div>

        <!-- Developer Section -->
        <div class="mb-6">
          <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
            Developer
          </h3>
          <NuxtLink
            v-for="item in developerLinks"
            :key="item.name"
            :to="item.href"
            class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="isActive(item.href) 
              ? 'bg-primary-50 text-primary-600' 
              : 'text-gray-700 hover:bg-gray-50'"
          >
            <span v-html="item.icon" class="w-5 h-5 mr-3"></span>
            {{ item.name }}
          </NuxtLink>
        </div>
      </nav>

      <!-- Mode Indicator (Test/Live) -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg">
          <div class="flex items-center">
            <div 
              class="w-2 h-2 rounded-full mr-2"
              :class="isLiveMode ? 'bg-green-500' : 'bg-blue-500'"
            ></div>
            <span class="text-sm font-medium text-gray-700">
              {{ isLiveMode ? 'Live Mode' : 'Test Mode' }}
            </span>
          </div>
          <button
            @click="toggleMode"
            class="text-xs text-primary-600 hover:text-primary-700 font-medium"
          >
            Switch
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  sidebarOpen: Boolean
})

defineEmits(['close'])

const route = useRoute()
const isLiveMode = ref(false)

const dashboardLinks = [
  {
    name: 'Overview',
    href: '/dashboard',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>'
  },
  {
    name: 'API Keys',
    href: '/dashboard/api-keys',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>'
  }
]

const managementLinks = [
  {
    name: 'Customers',
    href: '/dashboard/customers',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
  },
  {
    name: 'Invoices',
    href: '/dashboard/invoices',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
  },
  {
    name: 'Payments',
    href: '/dashboard/payments',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>'
  },
  {
    name: 'Subscriptions',
    href: '/dashboard/subscriptions',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>'
  }
]

const developerLinks = [
  {
    name: 'Webhooks',
    href: '/dashboard/webhooks',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>'
  },
  {
    name: 'Logs',
    href: '/dashboard/logs',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
  },
  {
    name: 'Settings',
    href: '/dashboard/settings',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
  }
]

const isActive = (href) => {
  if (href === '/dashboard') {
    return route.path === '/dashboard'
  }
  return route.path.startsWith(href)
}

const toggleMode = () => {
  isLiveMode.value = !isLiveMode.value
  // In production, this would call an API to switch modes
}
</script>
