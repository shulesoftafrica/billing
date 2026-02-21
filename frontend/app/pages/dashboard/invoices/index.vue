<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
        <p class="text-gray-600 mt-1">Create and manage customer invoices.</p>
      </div>
      <NuxtLink
        to="/dashboard/invoices/create"
        class="flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Invoice
      </NuxtLink>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Total</span>
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ invoiceStats.total }}</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Paid</span>
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(invoiceStats.paid) }}</p>
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
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(invoiceStats.pending) }}</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-600">Overdue</span>
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(invoiceStats.overdue) }}</p>
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
              placeholder="Search by invoice number or customer..."
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
            <option value="draft">Draft</option>
            <option value="open">Open</option>
            <option value="paid">Paid</option>
            <option value="void">Void</option>
            <option value="overdue">Overdue</option>
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

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Invoice
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Due Date
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="invoice in filteredInvoices"
              :key="invoice.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ invoice.number }}</div>
                <div class="text-sm text-gray-500">{{ invoice.date }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ invoice.customer }}</div>
                <div class="text-sm text-gray-500">{{ invoice.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(invoice.status)"
                >
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                ${{ formatNumber(invoice.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ invoice.dueDate }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <NuxtLink
                    :to="`/dashboard/invoices/${invoice.id}`"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    View
                  </NuxtLink>
                  <button
                    v-if="invoice.status === 'open'"
                    @click="sendInvoice(invoice)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Send
                  </button>
                  <button
                    v-if="invoice.status === 'draft'"
                    @click="publishInvoice(invoice)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Publish
                  </button>
                  <button
                    @click="openDeleteModal(invoice)"
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
      <div v-if="filteredInvoices.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No invoices found</h3>
        <p class="text-gray-600 mb-4">{{ searchQuery ? 'Try adjusting your search or filters.' : 'Create your first invoice to get started.' }}</p>
        <NuxtLink
          v-if="!searchQuery"
          to="/dashboard/invoices/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Invoice
        </NuxtLink>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click.self="showDeleteModal = false"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Delete Invoice</h3>
              <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
            </div>
          </div>

          <p class="text-sm text-gray-700 mb-4">
            Are you sure you want to delete invoice <strong>{{ invoiceToDelete?.number }}</strong>? 
          </p>

          <div class="flex space-x-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteInvoice"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete Invoice
            </button>
          </div>
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
  title: 'Invoices - Billing Platform'
})

const searchQuery = ref('')
const filterStatus = ref('')
const sortBy = ref('date_desc')
const showDeleteModal = ref(false)
const invoiceToDelete = ref(null)

const invoiceStats = ref({
  total: 124,
  paid: 45800.00,
  pending: 12450.00,
  overdue: 3200.00
})

const invoices = ref([
  {
    id: 1,
    number: 'INV-001',
    customer: 'Acme Corporation',
    email: 'john@acmecorp.com',
    status: 'paid',
    amount: 2990.00,
    date: 'Feb 15, 2026',
    dueDate: 'Feb 15, 2026'
  },
  {
    id: 2,
    number: 'INV-002',
    customer: 'TechStart Inc',
    email: 'jane@techstart.com',
    status: 'open',
    amount: 1499.00,
    date: 'Feb 14, 2026',
    dueDate: 'Feb 28, 2026'
  },
  {
    id: 3,
    number: 'INV-003',
    customer: 'Design Studio',
    email: 'bob@designstudio.com',
    status: 'overdue',
    amount: 549.00,
    date: 'Jan 25, 2026',
    dueDate: 'Feb 10, 2026'
  },
  {
    id: 4,
    number: 'INV-004',
    customer: 'Global Systems',
    email: 'alice@globalsys.com',
    status: 'draft',
    amount: 785.00,
    date: 'Feb 16, 2026',
    dueDate: 'Mar 1, 2026'
  },
  {
    id: 5,
    number: 'INV-005',
    customer: 'Startup Co',
    email: 'charlie@startup.io',
    status: 'paid',
    amount: 299.00,
    date: 'Feb 12, 2026',
    dueDate: 'Feb 12, 2026'
  }
])

const filteredInvoices = computed(() => {
  let result = invoices.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(i => 
      i.number.toLowerCase().includes(query) || 
      i.customer.toLowerCase().includes(query) ||
      i.email.toLowerCase().includes(query)
    )
  }

  if (filterStatus.value) {
    result = result.filter(i => i.status === filterStatus.value)
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
    draft: 'bg-gray-100 text-gray-800',
    open: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    void: 'bg-gray-100 text-gray-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const sendInvoice = (invoice) => {
  // In production, call API to send invoice
  alert(`Sending invoice ${invoice.number} to ${invoice.customer}`)
}

const publishInvoice = (invoice) => {
  // In production, call API to publish invoice
  invoice.status = 'open'
}

const openDeleteModal = (invoice) => {
  invoiceToDelete.value = invoice
  showDeleteModal.value = true
}

const deleteInvoice = () => {
  if (invoiceToDelete.value) {
    const index = invoices.value.findIndex(i => i.id === invoiceToDelete.value.id)
    if (index !== -1) {
      invoices.value.splice(index, 1)
    }
  }
  showDeleteModal.value = false
  invoiceToDelete.value = null
}
</script>
