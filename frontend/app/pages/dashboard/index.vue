<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Overview</h1>
      <p class="text-gray-600 mt-1">Welcome back! Here's what's happening with your business.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Revenue Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="flex items-center text-sm font-medium text-green-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            12.5%
          </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(stats.revenue) }}</p>
        <p class="text-sm text-gray-600 mt-1">Total Revenue</p>
        <p class="text-xs text-gray-500 mt-2">vs. last month</p>
      </div>

      <!-- Customers Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <span class="flex items-center text-sm font-medium text-green-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            8.2%
          </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.customers) }}</p>
        <p class="text-sm text-gray-600 mt-1">Total Customers</p>
        <p class="text-xs text-gray-500 mt-2">+{{ stats.newCustomers }} this month</p>
      </div>

      <!-- Active Subscriptions Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <span class="flex items-center text-sm font-medium text-green-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            5.4%
          </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.activeSubscriptions) }}</p>
        <p class="text-sm text-gray-600 mt-1">Active Subscriptions</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.churnRate }}% churn rate</p>
      </div>

      <!-- Pending Invoices Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="flex items-center text-sm font-medium text-red-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
            3.1%
          </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ formatNumber(stats.pendingAmount) }}</p>
        <p class="text-sm text-gray-600 mt-1">Pending Invoices</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.pendingCount }} invoices</p>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Revenue Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-gray-900">Revenue Overview</h2>
          <select v-model="revenueChartPeriod" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="7d">Last 7 days</option>
            <option value="30d">Last 30 days</option>
            <option value="90d">Last 90 days</option>
          </select>
        </div>
        <div class="h-64 flex items-end justify-between space-x-2">
          <div
            v-for="(value, index) in revenueData"
            :key="index"
            class="flex-1 bg-gradient-to-t from-primary-500 to-primary-400 rounded-t-lg hover:from-primary-600 hover:to-primary-500 transition-all cursor-pointer relative group"
            :style="{ height: (value / Math.max(...revenueData) * 100) + '%' }"
          >
            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
              ${{ formatNumber(value) }}
            </div>
          </div>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
          <span>Mon</span>
          <span>Tue</span>
          <span>Wed</span>
          <span>Thu</span>
          <span>Fri</span>
          <span>Sat</span>
          <span>Sun</span>
        </div>
      </div>

      <!-- Subscription Status -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Subscription Status</h2>
        <div class="space-y-4">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Active</span>
              <span class="text-sm font-semibold text-gray-900">{{ subscriptionStatus.active }} ({{ subscriptionStatus.activePercent }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-green-500 h-2 rounded-full" :style="{ width: subscriptionStatus.activePercent + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Trialing</span>
              <span class="text-sm font-semibold text-gray-900">{{ subscriptionStatus.trialing }} ({{ subscriptionStatus.trialingPercent }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" :style="{ width: subscriptionStatus.trialingPercent + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Past Due</span>
              <span class="text-sm font-semibold text-gray-900">{{ subscriptionStatus.pastDue }} ({{ subscriptionStatus.pastDuePercent }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-amber-500 h-2 rounded-full" :style="{ width: subscriptionStatus.pastDuePercent + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Canceled</span>
              <span class="text-sm font-semibold text-gray-900">{{ subscriptionStatus.canceled }} ({{ subscriptionStatus.canceledPercent }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-red-500 h-2 rounded-full" :style="{ width: subscriptionStatus.canceledPercent + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Transactions -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
            <NuxtLink to="/dashboard/payments" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
              View all
            </NuxtLink>
          </div>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="transaction in recentTransactions"
            :key="transaction.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div 
                  class="w-10 h-10 rounded-full flex items-center justify-center"
                  :class="transaction.status === 'succeeded' ? 'bg-green-100' : transaction.status === 'pending' ? 'bg-amber-100' : 'bg-red-100'"
                >
                  <svg 
                    v-if="transaction.status === 'succeeded'" 
                    class="w-5 h-5 text-green-600" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg 
                    v-else-if="transaction.status === 'pending'" 
                    class="w-5 h-5 text-amber-600" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg 
                    v-else
                    class="w-5 h-5 text-red-600" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ transaction.customer }}</p>
                  <p class="text-xs text-gray-500">{{ transaction.description }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">${{ formatNumber(transaction.amount) }}</p>
                <p class="text-xs text-gray-500">{{ transaction.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="space-y-3">
          <NuxtLink
            to="/dashboard/invoices/create"
            class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors group"
          >
            <div class="flex items-center">
              <div class="w-10 h-10 bg-primary-100 group-hover:bg-primary-200 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-900">Create Invoice</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/customers/create"
            class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors group"
          >
            <div class="flex items-center">
              <div class="w-10 h-10 bg-success-100 group-hover:bg-success-200 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-900">Add Customer</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/api-keys"
            class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors group"
          >
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-900">Manage API Keys</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/docs"
            class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors group"
          >
            <div class="flex items-center">
              <div class="w-10 h-10 bg-purple-100 group-hover:bg-purple-200 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-900">View Documentation</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>
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
  title: 'Dashboard - Billing Platform'
})

const revenueChartPeriod = ref('7d')

const stats = ref({
  revenue: 47823.50,
  customers: 1247,
  newCustomers: 84,
  activeSubscriptions: 856,
  churnRate: 2.3,
  pendingAmount: 12450.00,
  pendingCount: 18
})

const revenueData = ref([12400, 15200, 11800, 18900, 16500, 14200, 19800])

const subscriptionStatus = ref({
  active: 856,
  activePercent: 72,
  trialing: 143,
  trialingPercent: 12,
  pastDue: 95,
  pastDuePercent: 8,
  canceled: 95,
  canceledPercent: 8
})

const recentTransactions = ref([
  {
    id: 1,
    customer: 'Acme Corporation',
    description: 'Monthly subscription',
    amount: 299.00,
    status: 'succeeded',
    time: '2 min ago'
  },
  {
    id: 2,
    customer: 'TechStart Inc',
    description: 'Annual plan upgrade',
    amount: 2999.00,
    status: 'succeeded',
    time: '14 min ago'
  },
  {
    id: 3,
    customer: 'Design Studio',
    description: 'Invoice payment',
    amount: 549.00,
    status: 'pending',
    time: '1 hour ago'
  },
  {
    id: 4,
    customer: 'Global Systems',
    description: 'Failed payment retry',
    amount: 199.00,
    status: 'failed',
    time: '2 hours ago'
  }
])

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(num)
}
</script>
