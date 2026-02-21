<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Webhooks</h1>
        <p class="text-gray-600 mt-1">Configure and manage webhook endpoints for real-time event notifications.</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all shadow-sm"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Endpoint
      </button>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-sm font-medium text-blue-900 mb-1">About Webhooks</h3>
          <p class="text-sm text-blue-800">
            Webhooks allow you to receive real-time notifications about events in your account. 
            Configure endpoints to receive payloads for specific events like payments, subscriptions, and more.
          </p>
        </div>
      </div>
    </div>

    <!-- Webhooks Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Endpoint URL
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Events
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Last Delivery
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="webhook in webhooks"
              :key="webhook.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="text-sm font-mono text-gray-900">{{ webhook.url }}</div>
                <div class="text-xs text-gray-500 mt-1">Created {{ webhook.created }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="event in webhook.events.slice(0, 2)"
                    :key="event"
                    class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded"
                  >
                    {{ event }}
                  </span>
                  <span
                    v-if="webhook.events.length > 2"
                    class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded"
                  >
                    +{{ webhook.events.length - 2 }} more
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="webhook.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ webhook.enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="webhook.lastDelivery" class="text-sm text-gray-900">
                  {{ webhook.lastDelivery.time }}
                </div>
                <div v-if="webhook.lastDelivery" class="flex items-center text-xs"
                  :class="webhook.lastDelivery.success ? 'text-green-600' : 'text-red-600'">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="webhook.lastDelivery.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  {{ webhook.lastDelivery.success ? 'Success' : 'Failed' }} ({{ webhook.lastDelivery.code }})
                </div>
                <div v-else class="text-sm text-gray-500">Never</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="testWebhook(webhook)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Test
                  </button>
                  <button
                    @click="viewLogs(webhook)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Logs
                  </button>
                  <button
                    @click="toggleWebhook(webhook)"
                    class="text-amber-600 hover:text-amber-900"
                  >
                    {{ webhook.enabled ? 'Disable' : 'Enable' }}
                  </button>
                  <button
                    @click="deleteWebhook(webhook)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="webhooks.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No webhooks configured</h3>
        <p class="text-gray-600 mb-4">Add your first webhook endpoint to start receiving event notifications.</p>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Endpoint
        </button>
      </div>
    </div>

    <!-- Event Reference -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Available Events</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="category in eventCategories" :key="category.name">
          <h3 class="text-sm font-medium text-gray-900 mb-2">{{ category.name }}</h3>
          <ul class="space-y-1">
            <li v-for="event in category.events" :key="event" class="text-sm text-gray-600 font-mono">
              {{ event }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Create/Edit Webhook Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Add Webhook Endpoint</h3>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="createWebhook" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Endpoint URL <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newWebhook.url"
              type="url"
              placeholder="https://yourserver.com/webhook"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              required
            />
            <p class="text-xs text-gray-500 mt-1">The URL to send webhook payloads to</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Events to Send <span class="text-red-500">*</span>
            </label>
            <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
              <label
                v-for="category in eventCategories"
                :key="category.name"
                class="block"
              >
                <div class="font-medium text-sm text-gray-900 mb-1">{{ category.name }}</div>
                <div class="space-y-1 ml-4">
                  <label
                    v-for="event in category.events"
                    :key="event"
                    class="flex items-center"
                  >
                    <input
                      v-model="newWebhook.events"
                      type="checkbox"
                      :value="event"
                      class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 font-mono">{{ event }}</span>
                  </label>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Webhook Secret (Optional)
            </label>
            <input
              v-model="newWebhook.secret"
              type="text"
              placeholder="whsec_..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono text-sm"
            />
            <p class="text-xs text-gray-500 mt-1">Used to verify webhook signatures. Leave empty to auto-generate.</p>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
            >
              Add Endpoint
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})

useHead({
  title: 'Webhooks - Billing Platform'
})

const showCreateModal = ref(false)

const webhooks = ref([
  {
    id: 1,
    url: 'https://api.example.com/webhooks/billing',
    events: ['payment.succeeded', 'payment.failed', 'invoice.paid'],
    enabled: true,
    created: 'Jan 15, 2026',
    lastDelivery: {
      time: '2 minutes ago',
      success: true,
      code: 200
    }
  },
  {
    id: 2,
    url: 'https://app.yoursite.com/webhook-receiver',
    events: ['customer.created', 'customer.updated', 'subscription.created', 'subscription.canceled'],
    enabled: true,
    created: 'Dec 20, 2025',
    lastDelivery: {
      time: '1 hour ago',
      success: false,
      code: 500
    }
  },
  {
    id: 3,
    url: 'https://staging.example.com/webhooks',
    events: ['*'],
    enabled: false,
    created: 'Nov 5, 2025',
    lastDelivery: null
  }
])

const eventCategories = [
  {
    name: 'Payments',
    events: ['payment.succeeded', 'payment.failed', 'payment.refunded']
  },
  {
    name: 'Invoices',
    events: ['invoice.created', 'invoice.paid', 'invoice.failed', 'invoice.voided']
  },
  {
    name: 'Customers',
    events: ['customer.created', 'customer.updated', 'customer.deleted']
  },
  {
    name: 'Subscriptions',
    events: ['subscription.created', 'subscription.updated', 'subscription.canceled', 'subscription.trial_ending']
  }
]

const newWebhook = reactive({
  url: '',
  events: [],
  secret: ''
})

const createWebhook = () => {
  webhooks.value.push({
    id: Date.now(),
    url: newWebhook.url,
    events: [...newWebhook.events],
    enabled: true,
    created: 'Just now',
    lastDelivery: null
  })
  
  showCreateModal.value = false
  newWebhook.url = ''
  newWebhook.events = []
  newWebhook.secret = ''
}

const testWebhook = (webhook) => {
  alert(`Sending test payload to ${webhook.url}...`)
}

const viewLogs = (webhook) => {
  alert(`Viewing delivery logs for ${webhook.url}`)
}

const toggleWebhook = (webhook) => {
  webhook.enabled = !webhook.enabled
}

const deleteWebhook = (webhook) => {
  if (confirm(`Are you sure you want to delete the webhook endpoint ${webhook.url}?`)) {
    const index = webhooks.value.findIndex(w => w.id === webhook.id)
    if (index > -1) {
      webhooks.value.splice(index, 1)
    }
  }
}
</script>
