<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Payments</h1>
      <p class="text-gray-600 mt-1">View and manage all payment transactions.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Total Volume</span>
          <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(paymentStats.totalVolume) }}</p>
        <p class="text-xs text-gray-500 mt-1">This month</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Successful</span>
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ paymentStats.successful }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ paymentStats.successRate }}% success rate</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Pending</span>
          <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ paymentStats.pending }}</p>
        <p class="text-xs text-gray-500 mt-1">Awaiting confirmation</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Failed</span>
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ paymentStats.failed }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ paymentStats.failureRate }}% failure rate</p>
      </div>
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
              placeholder="Search by transaction ID or customer..."
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
            <option value="succeeded">Succeeded</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
          <select
            v-model="filterMethod"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Methods</option>
            <option value="card">Card</option>
            <option value="bank">Bank Transfer</option>
            <option value="paypal">PayPal</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <select
            v-model="sortBy"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="date_desc">Newest First</option>
            <option value="date_asc">Oldest First</option>
            <option value="amount_desc">Highest Amount</option>
            <option value="amount_asc">Lowest Amount</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Transaction
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Method
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="payment in filteredPayments"
              :key="payment.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-mono text-gray-900">{{ payment.transactionId }}</div>
                <div class="text-sm text-gray-500">{{ payment.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ payment.customer }}</div>
                <div class="text-sm text-gray-500">{{ payment.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center text-sm text-gray-900">
                  <svg v-if="payment.method === 'card'" class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <svg v-else-if="payment.method === 'bank'" class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                  </svg>
                  {{ payment.methodDetails }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(payment.status)"
                >
                  {{ payment.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                ${{ formatNumber(payment.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ payment.date }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="viewDetails(payment)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    View
                  </button>
                  <button
                    v-if="payment.status === 'succeeded'"
                    @click="refundPayment(payment)"
                    class="text-amber-600 hover:text-amber-900"
                  >
                    Refund
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredPayments.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No payments found</h3>
        <p class="text-gray-600">Try adjusting your search or filters.</p>
      </div>

      <!-- Pagination -->
      <div v-if="filteredPayments.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredPayments.length }}</span> of <span class="font-medium">{{ filteredPayments.length }}</span> results
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
  title: 'Payments - Billing Platform'
})

const searchQuery = ref('')
const filterStatus = ref('')
const filterMethod = ref('')
const sortBy = ref('date_desc')

const paymentStats = ref({
  totalVolume: 125890.50,
  successful: 487,
  successRate: 94.2,
  pending: 12,
  failed: 18,
  failureRate: 3.5
})

const payments = ref([
  {
    id: 1,
    transactionId: 'TXN-2026021701',
    customer: 'Acme Corporation',
    email: 'john@acmecorp.com',
    method: 'card',
    methodDetails: 'Visa •••• 4242',
    status: 'succeeded',
    amount: 2990.00,
    description: 'Invoice #INV-001',
    date: 'Feb 17, 2026 10:23 AM'
  },
  {
    id: 2,
    transactionId: 'TXN-2026021702',
    customer: 'TechStart Inc',
    email: 'jane@techstart.com',
    method: 'bank',
    methodDetails: 'Bank Transfer',
    status: 'pending',
    amount: 1499.00,
    description: 'Invoice #INV-002',
    date: 'Feb 17, 2026 09:15 AM'
  },
  {
    id: 3,
    transactionId: 'TXN-2026021603',
    customer: 'Design Studio',
    email: 'bob@designstudio.com',
    method: 'card',
    methodDetails: 'Mastercard •••• 5454',
    status: 'failed',
    amount: 549.00,
    description: 'Invoice #INV-003',
    date: 'Feb 16, 2026 03:45 PM'
  },
  {
    id: 4,
    transactionId: 'TXN-2026021504',
    customer: 'Global Systems',
    email: 'alice@globalsys.com',
    method: 'card',
    methodDetails: 'Amex •••• 3782',
    status: 'succeeded',
    amount: 3599.00,
    description: 'Monthly subscription',
    date: 'Feb 15, 2026 11:20 AM'
  },
  {
    id: 5,
    transactionId: 'TXN-2026021405',
    customer: 'Startup Co',
    email: 'charlie@startup.io',
    method: 'card',
    methodDetails: 'Visa •••• 1234',
    status: 'refunded',
    amount: 299.00,
    description: 'Invoice #INV-005',
    date: 'Feb 14, 2026 02:30 PM'
  }
])

const filteredPayments = computed(() => {
  let result = payments.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(p => 
      p.transactionId.toLowerCase().includes(query) || 
      p.customer.toLowerCase().includes(query) ||
      p.email.toLowerCase().includes(query)
    )
  }

  if (filterStatus.value) {
    result = result.filter(p => p.status === filterStatus.value)
  }

  if (filterMethod.value) {
    result = result.filter(p => p.method === filterMethod.value)
  }

  result = [...result].sort((a, b) => {
    switch (sortBy.value) {
      case 'date_desc':
        return new Date(b.date) - new Date(a.date)
      case 'date_asc':
        return new Date(a.date) - new Date(b.date)
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

const getStatusClass = (status) => {
  const classes = {
    succeeded: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const viewDetails = (payment) => {
  alert(`Viewing details for ${payment.transactionId}`)
}

const refundPayment = (payment) => {
  if (confirm(`Are you sure you want to refund $${formatNumber(payment.amount)} to ${payment.customer}?`)) {
    payment.status = 'refunded'
  }
}
</script>
