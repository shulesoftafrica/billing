<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
        <p class="text-gray-600 mt-1">View system events, API calls, and audit trail.</p>
      </div>
      <button
        @click="exportLogs"
        class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Export Logs
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search logs..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
          <select
            v-model="filterLevel"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Levels</option>
            <option value="info">Info</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filterCategory"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Categories</option>
            <option value="api">API</option>
            <option value="payment">Payment</option>
            <option value="webhook">Webhook</option>
            <option value="auth">Authentication</option>
            <option value="system">System</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Time Range</label>
          <select
            v-model="timeRange"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="1h">Last Hour</option>
            <option value="24h">Last 24 Hours</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Events</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ logStats.total }}</p>
          </div>
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Info</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ logStats.info }}</p>
          </div>
          <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Warnings</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ logStats.warning }}</p>
          </div>
          <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Errors</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ logStats.error }}</p>
          </div>
          <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Timestamp
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Level
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Message
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-for="log in filteredLogs" :key="log.id">
              <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="toggleLogDetails(log.id)">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.timestamp }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getLevelClass(log.level)"
                  >
                    {{ log.level }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                    {{ log.category }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ log.message }}</div>
                  <div v-if="log.user" class="text-sm text-gray-500">User: {{ log.user }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click.stop="toggleLogDetails(log.id)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    {{ expandedLogs.has(log.id) ? 'Hide' : 'Details' }}
                  </button>
                </td>
              </tr>
              
              <!-- Expanded Details Row -->
              <tr v-if="expandedLogs.has(log.id)" class="bg-gray-50">
                <td colspan="5" class="px-6 py-4">
                  <div class="space-y-2">
                    <div class="flex items-start">
                      <span class="text-sm font-medium text-gray-700 w-24">Request ID:</span>
                      <span class="text-sm text-gray-900 font-mono">{{ log.requestId }}</span>
                    </div>
                    <div class="flex items-start">
                      <span class="text-sm font-medium text-gray-700 w-24">IP Address:</span>
                      <span class="text-sm text-gray-900">{{ log.ipAddress }}</span>
                    </div>
                    <div v-if="log.metadata" class="flex items-start">
                      <span class="text-sm font-medium text-gray-700 w-24">Metadata:</span>
                      <pre class="text-xs text-gray-900 bg-white p-3 rounded border border-gray-200 flex-1 overflow-x-auto">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
                    </div>
                    <div v-if="log.stackTrace" class="flex items-start">
                      <span class="text-sm font-medium text-gray-700 w-24">Stack Trace:</span>
                      <pre class="text-xs text-red-900 bg-red-50 p-3 rounded border border-red-200 flex-1 overflow-x-auto">{{ log.stackTrace }}</pre>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredLogs.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No logs found</h3>
        <p class="text-gray-600">Try adjusting your filters or search query.</p>
      </div>

      <!-- Pagination -->
      <div v-if="filteredLogs.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredLogs.length }}</span> of <span class="font-medium">{{ filteredLogs.length }}</span> results
        </div>
        <div class="flex space-x-2">
          <button
            disabled
            class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-400 cursor-not-allowed"
          >
            Previous
          </button>
          <button
            class="px-3 py-1 bg-primary-600 text-white rounded-lg text-sm"
          >
            1
          </button>
          <button
            disabled
            class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-400 cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})

useHead({
  title: 'Logs - Billing Platform'
})

const searchQuery = ref('')
const filterLevel = ref('')
const filterCategory = ref('')
const timeRange = ref('24h')
const expandedLogs = ref(new Set())

const logStats = ref({
  total: 1247,
  info: 1089,
  warning: 134,
  error: 24
})

const logs = ref([
  {
    id: 1,
    timestamp: 'Feb 17, 2026 10:23:45',
    level: 'info',
    category: 'api',
    message: 'API request completed successfully',
    user: 'john@acmecorp.com',
    requestId: 'req_2026021710234501',
    ipAddress: '192.168.1.100',
    metadata: {
      endpoint: '/api/v1/payments',
      method: 'POST',
      statusCode: 200,
      responseTime: '145ms'
    }
  },
  {
    id: 2,
    timestamp: 'Feb 17, 2026 10:15:22',
    level: 'warning',
    category: 'payment',
    message: 'Payment retry attempt for failed transaction',
    user: 'jane@techstart.com',
    requestId: 'req_2026021710152201',
    ipAddress: '192.168.1.105',
    metadata: {
      transactionId: 'TXN-2026021702',
      amount: 1499.00,
      attempt: 2
    }
  },
  {
    id: 3,
    timestamp: 'Feb 17, 2026 09:45:10',
    level: 'error',
    category: 'webhook',
    message: 'Webhook delivery failed after 3 attempts',
    user: null,
    requestId: 'req_2026021709451001',
    ipAddress: '10.0.0.50',
    metadata: {
      webhookId: 2,
      endpoint: 'https://app.yoursite.com/webhook-receiver',
      errorCode: 500
    },
    stackTrace: 'Error: Connection timeout\n  at WebhookService.deliver (webhook.js:45)\n  at async retry (retry.js:12)'
  },
  {
    id: 4,
    timestamp: 'Feb 17, 2026 09:30:55',
    level: 'info',
    category: 'auth',
    message: 'User logged in successfully',
    user: 'bob@designstudio.com',
    requestId: 'req_2026021709305501',
    ipAddress: '192.168.1.120',
    metadata: {
      method: '2FA',
      device: 'Chrome on Windows'
    }
  },
  {
    id: 5,
    timestamp: 'Feb 17, 2026 08:15:33',
    level: 'info',
    category: 'system',
    message: 'Scheduled backup completed successfully',
    user: null,
    requestId: 'req_2026021708153301',
    ipAddress: 'system',
    metadata: {
      backupSize: '2.4 GB',
      duration: '45 seconds',
      destination: 's3://backups/2026-02-17.tar.gz'
    }
  }
])

const filteredLogs = computed(() => {
  let result = logs.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(log => 
      log.message.toLowerCase().includes(query) ||
      log.category.toLowerCase().includes(query) ||
      (log.user && log.user.toLowerCase().includes(query))
    )
  }

  if (filterLevel.value) {
    result = result.filter(log => log.level === filterLevel.value)
  }

  if (filterCategory.value) {
    result = result.filter(log => log.category === filterCategory.value)
  }

  return result
})

const getLevelClass = (level) => {
  const classes = {
    info: 'bg-green-100 text-green-800',
    warning: 'bg-amber-100 text-amber-800',
    error: 'bg-red-100 text-red-800'
  }
  return classes[level] || 'bg-gray-100 text-gray-800'
}

const toggleLogDetails = (logId) => {
  if (expandedLogs.value.has(logId)) {
    expandedLogs.value.delete(logId)
  } else {
    expandedLogs.value.add(logId)
  }
}

const exportLogs = () => {
  alert('Exporting logs as CSV...')
}
</script>
