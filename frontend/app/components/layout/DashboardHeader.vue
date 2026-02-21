<template>
  <div class="sticky top-0 z-10 bg-white border-b border-gray-200">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
      <!-- Left: Mobile menu button -->
      <div class="flex items-center">
        <button
          @click="$emit('toggle-sidebar')"
          class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Breadcrumb for desktop -->
        <div class="hidden lg:flex items-center space-x-2 text-sm">
          <NuxtLink to="/dashboard" class="text-gray-500 hover:text-gray-700">
            Dashboard
          </NuxtLink>
          <span v-if="currentPage" class="text-gray-400">/</span>
          <span v-if="currentPage" class="text-gray-900 font-medium">{{ currentPage }}</span>
        </div>
      </div>

      <!-- Right: Search, Notifications, User Menu -->
      <div class="flex items-center space-x-4">
        <!-- Search (optional - desktop only) -->
        <div class="hidden md:block relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
          <svg
            class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Notifications -->
        <div class="relative">
          <button
            @click="notificationsOpen = !notificationsOpen"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg relative"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="unreadCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="notificationsOpen"
            v-click-outside="() => notificationsOpen = false"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-20"
          >
            <div class="px-4 py-2 border-b border-gray-200">
              <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <div
                v-for="notification in notifications"
                :key="notification.id"
                class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0"
              >
                <div class="flex items-start">
                  <div
                    class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                    :class="notification.read ? 'bg-gray-300' : 'bg-primary-500'"
                  ></div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm text-gray-900">{{ notification.message }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ notification.time }}</p>
                  </div>
                </div>
              </div>
              <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
                No notifications
              </div>
            </div>
            <div class="px-4 py-2 border-t border-gray-200">
              <button class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                View all notifications
              </button>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="relative">
          <button
            @click="userMenuOpen = !userMenuOpen"
            class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg"
          >
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-success-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
              JD
            </div>
            <div class="hidden md:block text-left">
              <p class="text-sm font-medium text-gray-900">John Doe</p>
              <p class="text-xs text-gray-500">john@company.com</p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- User Dropdown -->
          <div
            v-if="userMenuOpen"
            v-click-outside="() => userMenuOpen = false"
            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-20"
          >
            <div class="px-4 py-2 border-b border-gray-200">
              <p class="text-sm font-medium text-gray-900">John Doe</p>
              <p class="text-xs text-gray-500">john@company.com</p>
            </div>
            <NuxtLink
              v-for="item in userMenuItems"
              :key="item.name"
              :to="item.href"
              class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="userMenuOpen = false"
            >
              <span v-html="item.icon" class="w-5 h-5 mr-3 text-gray-400"></span>
              {{ item.name }}
            </NuxtLink>
            <div class="border-t border-gray-200 mt-2 pt-2">
              <button
                @click="handleLogout"
                class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign out
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['toggle-sidebar'])

const route = useRoute()
const searchQuery = ref('')
const notificationsOpen = ref(false)
const userMenuOpen = ref(false)

const currentPage = computed(() => {
  const path = route.path
  if (path === '/dashboard') return null
  const segments = path.split('/').filter(Boolean)
  return segments[segments.length - 1]
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
})

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

const notifications = ref([
  {
    id: 1,
    message: 'New payment received: $299.00',
    time: '5 minutes ago',
    read: false
  },
  {
    id: 2,
    message: 'Invoice #INV-001 has been paid',
    time: '1 hour ago',
    read: false
  },
  {
    id: 3,
    message: 'New customer registered: Acme Corp',
    time: '3 hours ago',
    read: true
  }
])

const userMenuItems = [
  {
    name: 'Your Profile',
    href: '/dashboard/settings',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>'
  },
  {
    name: 'Company Settings',
    href: '/dashboard/settings/company',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>'
  },
  {
    name: 'Documentation',
    href: '/docs',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>'
  }
]

const handleLogout = () => {
  // In production, this would call logout API
  navigateTo('/auth/login')
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>
