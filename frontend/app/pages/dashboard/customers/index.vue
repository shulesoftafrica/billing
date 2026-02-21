<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Customers</h1>
        <p class="text-gray-600 mt-1">Manage your customer database and payment information.</p>
      </div>
      <NuxtLink
        to="/dashboard/customers/create"
        class="flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Customer
      </NuxtLink>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name or email..."
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
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <select
            v-model="sortBy"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="created_desc">Newest First</option>
            <option value="created_asc">Oldest First</option>
            <option value="name_asc">Name (A-Z)</option>
            <option value="name_desc">Name (Z-A)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Subscriptions
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total Spent
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Created
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="customer in filteredCustomers"
              :key="customer.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-success-400 flex items-center justify-center text-white font-semibold">
                    {{ getInitials(customer.name) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                    <div class="text-sm text-gray-500">{{ customer.company }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ customer.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="customer.status === 'active' 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-gray-100 text-gray-800'"
                >
                  {{ customer.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ customer.subscriptions }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ formatNumber(customer.totalSpent) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ customer.created }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <NuxtLink
                    :to="`/dashboard/customers/${customer.id}`"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    View
                  </NuxtLink>
                  <NuxtLink
                    :to="`/dashboard/customers/${customer.id}/edit`"
                    class="text-gray-600 hover:text-gray-900"
                  >
                    Edit
                  </NuxtLink>
                  <button
                    @click="openDeleteModal(customer)"
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
      <div v-if="filteredCustomers.length === 0" class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
        <p class="text-gray-600 mb-4">{{ searchQuery ? 'Try adjusting your search or filters.' : 'Get started by adding your first customer.' }}</p>
        <NuxtLink
          v-if="!searchQuery"
          to="/dashboard/customers/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Customer
        </NuxtLink>
      </div>

      <!-- Pagination -->
      <div v-if="filteredCustomers.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredCustomers.length }}</span> of <span class="font-medium">{{ filteredCustomers.length }}</span> results
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
              <h3 class="text-lg font-semibold text-gray-900">Delete Customer</h3>
              <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
            </div>
          </div>

          <p class="text-sm text-gray-700 mb-4">
            Are you sure you want to delete <strong>{{ customerToDelete?.name }}</strong>? 
            All of their subscriptions, invoices, and payment history will be permanently removed.
          </p>

          <div class="flex space-x-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteCustomer"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete Customer
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
  title: 'Customers - Billing Platform'
})

const searchQuery = ref('')
const filterStatus = ref('')
const sortBy = ref('created_desc')
const showDeleteModal = ref(false)
const customerToDelete = ref(null)

const customers = ref([
  {
    id: 1,
    name: 'John Doe',
    email: 'john@acmecorp.com',
    company: 'Acme Corporation',
    status: 'active',
    subscriptions: 2,
    totalSpent: 5980.00,
    created: 'Jan 15, 2026'
  },
  {
    id: 2,
    name: 'Jane Smith',
    email: 'jane@techstart.com',
    company: 'TechStart Inc',
    status: 'active',
    subscriptions: 1,
    totalSpent: 2999.00,
    created: 'Jan 12, 2026'
  },
  {
    id: 3,
    name: 'Bob Johnson',
    email: 'bob@designstudio.com',
    company: 'Design Studio',
    status: 'active',
    subscriptions: 3,
    totalSpent: 8470.00,
    created: 'Jan 8, 2026'
  },
  {
    id: 4,
    name: 'Alice Williams',
    email: 'alice@globalsys.com',
    company: 'Global Systems',
    status: 'inactive',
    subscriptions: 0,
    totalSpent: 199.00,
    created: 'Dec 28, 2025'
  },
  {
    id: 5,
    name: 'Charlie Brown',
    email: 'charlie@startup.io',
    company: 'Startup Co',
    status: 'active',
    subscriptions: 1,
    totalSpent: 1299.00,
    created: 'Dec 20, 2025'
  }
])

const filteredCustomers = computed(() => {
  let result = customers.value

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(c => 
      c.name.toLowerCase().includes(query) || 
      c.email.toLowerCase().includes(query) ||
      c.company.toLowerCase().includes(query)
    )
  }

  // Filter by status
  if (filterStatus.value) {
    result = result.filter(c => c.status === filterStatus.value)
  }

  // Sort
  result = [...result].sort((a, b) => {
    switch (sortBy.value) {
      case 'created_desc':
        return new Date(b.created) - new Date(a.created)
      case 'created_asc':
        return new Date(a.created) - new Date(b.created)
      case 'name_asc':
        return a.name.localeCompare(b.name)
      case 'name_desc':
        return b.name.localeCompare(a.name)
      default:
        return 0
    }
  })

  return result
})

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const openDeleteModal = (customer) => {
  customerToDelete.value = customer
  showDeleteModal.value = true
}

const deleteCustomer = () => {
  if (customerToDelete.value) {
    const index = customers.value.findIndex(c => c.id === customerToDelete.value.id)
    if (index !== -1) {
      customers.value.splice(index, 1)
    }
  }
  showDeleteModal.value = false
  customerToDelete.value = null
}
</script>
