<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Subscriptions</h1>
        <p class="text-gray-600 mt-1">Manage recurring billing and subscriptions.</p>
      </div>
      <NuxtLink
        to="/dashboard/subscriptions/create"
        class="flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all shadow-sm"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Subscription
      </NuxtLink>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Active</span>
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ subscriptionStats.active }}</p>
        <p class="text-xs text-gray-500 mt-1">${{ formatNumber(subscriptionStats.activeMRR) }}/mo MRR</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Trialing</span>
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ subscriptionStats.trialing }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ subscriptionStats.trialExpiringSoon }} ending soon</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Past Due</span>
          <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ subscriptionStats.pastDue }}</p>
        <p class="text-xs text-gray-500 mt-1">Requires attention</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Canceled</span>
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ subscriptionStats.canceled }}</p>
        <p class="text-xs text-gray-500 mt-1">This month</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by customer or plan..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filterStatus"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="trialing">Trialing</option>
            <option value="past_due">Past Due</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <select
            v-model="sortBy"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="amount_desc">Highest Amount</option>
            <option value="amount_asc">Lowest Amount</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Plan
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Next Billing
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Started
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="subscription in filteredSubscriptions"
              :key="subscription.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold">
                    {{ getInitials(subscription.customer) }}
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ subscription.customer }}</div>
                    <div class="text-sm text-gray-500">{{ subscription.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ subscription.plan }}</div>
                <div class="text-sm text-gray-500">{{ subscription.billingCycle }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(subscription.status)"
                >
                  {{ subscription.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${{ formatNumber(subscription.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ subscription.nextBilling || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ subscription.startDate }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <NuxtLink
                    :to="`/dashboard/subscriptions/${subscription.id}`"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    View
                  </NuxtLink>
                  <button
                    v-if="subscription.status === 'active'"
                    @click="pauseSubscription(subscription)"
                    class="text-amber-600 hover:text-amber-900"
                  >
                    Pause
                  </button>
                  <button
                    v-if="subscription.status === 'active' || subscription.status === 'trialing'"
                    @click="openCancelModal(subscription)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Cancel
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredSubscriptions.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No subscriptions found</h3>
        <p class="text-gray-600 mb-4">
          {{ searchQuery ? 'Try adjusting your search or filters.' : 'Create your first subscription to get started.' }}
        </p>
        <NuxtLink
          v-if="!searchQuery"
          to="/dashboard/subscriptions/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Subscription
        </NuxtLink>
      </div>

      <!-- Pagination -->
      <div v-if="filteredSubscriptions.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredSubscriptions.length }}</span> of <span class="font-medium">{{ filteredSubscriptions.length }}</span> results
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

    <!-- Cancel Confirmation Modal -->
    <div
      v-if="showCancelModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-fullmx-4 p-6">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Cancel Subscription</h3>
        </div>
        <p class="text-gray-600 mb-6">
          Are you sure you want to cancel <strong>{{ selectedSubscription?.customer }}'s</strong> subscription to <strong>{{ selectedSubscription?.plan }}</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showCancelModal = false"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Keep Subscription
          </button>
          <button
            @click="cancelSubscription"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Cancel Subscription
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
  title: 'Subscriptions - Billing Platform'
})

const searchQuery = ref('')
const filterStatus = ref('')
const sortBy = ref('newest')
const showCancelModal = ref(false)
const selectedSubscription = ref(null)

const subscriptionStats = ref({
  active: 856,
  activeMRR: 38456,
  trialing: 124,
  trialExpiringSoon: 8,
  pastDue: 23,
  canceled: 15
})

const subscriptions = ref([
  {
    id: 1,
    customer: 'Acme Corporation',
    email: 'john@acmecorp.com',
    plan: 'Professional Plan',
    billingCycle: 'Monthly',
    status: 'active',
    amount: 99.00,
    nextBilling: 'Mar 1, 2026',
    startDate: 'Jan 1, 2026'
  },
  {
    id: 2,
    customer: 'TechStart Inc',
    email: 'jane@techstart.com',
    plan: 'Enterprise Plan',
    billingCycle: 'Annual',
    status: 'trialing',
    amount: 999.00,
    nextBilling: 'Feb 28, 2026',
    startDate: 'Feb 14, 2026'
  },
  {
    id: 3,
    customer: 'Design Studio',
    email: 'bob@designstudio.com',
    plan: 'Basic Plan',
    billingCycle: 'Monthly',
    status: 'past_due',
    amount: 29.00,
    nextBilling: 'Feb 15, 2026',
    startDate: 'Nov 15, 2025'
  },
  {
    id: 4,
    customer: 'Global Systems',
    email: 'alice@globalsys.com',
    plan: 'Professional Plan',
    billingCycle: 'Monthly',
    status: 'active',
    amount: 99.00,
    nextBilling: 'Mar 5, 2026',
    startDate: 'Dec 5, 2025'
  },
  {
    id: 5,
    customer: 'Startup Co',
    email: 'charlie@startup.io',
    plan: 'Basic Plan',
    billingCycle: 'Monthly',
    status: 'canceled',
    amount: 29.00,
    nextBilling: null,
    startDate: 'Sep 1, 2025'
  }
])

const filteredSubscriptions = computed(() => {
  let result = subscriptions.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(s => 
      s.customer.toLowerCase().includes(query) || 
      s.email.toLowerCase().includes(query) ||
      s.plan.toLowerCase().includes(query)
    )
  }

  if (filterStatus.value) {
    result = result.filter(s => s.status === filterStatus.value)
  }

  result = [...result].sort((a, b) => {
    switch (sortBy.value) {
      case 'newest':
        return new Date(b.startDate) - new Date(a.startDate)
      case 'oldest':
        return new Date(a.startDate) - new Date(b.startDate)
      case 'amount_desc':
        return b.amount - a.amount
      case 'amount_asc':
        return a.amount - b.amount
      default:
        return 0
    }
  })

  return result
})

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    trialing: 'bg-blue-100 text-blue-800',
    past_due: 'bg-amber-100 text-amber-800',
    canceled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const pauseSubscription = (subscription) => {
  alert(`Pausing subscription for ${subscription.customer}`)
}

const openCancelModal = (subscription) => {
  selectedSubscription.value = subscription
  showCancelModal.value = true
}

const cancelSubscription = () => {
  if (selectedSubscription.value) {
    selectedSubscription.value.status = 'canceled'
    selectedSubscription.value.nextBilling = null
  }
  showCancelModal.value = false
  selectedSubscription.value = null
}
</script>
