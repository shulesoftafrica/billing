<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <NuxtLink
        to="/dashboard/customers"
        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
      >
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Customers
      </NuxtLink>
      <h1 class="text-3xl font-bold text-gray-900">{{ isEditMode ? 'Edit Customer' : 'Add New Customer' }}</h1>
      <p class="text-gray-600 mt-1">{{ isEditMode ? 'Update customer information and billing details.' : 'Create a new customer record to start billing.' }}</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Basic Information -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="John Doe"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Email <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="john@company.com"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              :class="{ 'border-red-500': errors.email }"
            />
            <p v-if="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Phone
            </label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="+1 (555) 123-4567"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Company
            </label>
            <input
              v-model="form.company"
              type="text"
              placeholder="Acme Corporation"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Address</h2>
        <div class="grid grid-cols-1 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Address Line 1
            </label>
            <input
              v-model="form.address.line1"
              type="text"
              placeholder="123 Main Street"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Address Line 2
            </label>
            <input
              v-model="form.address.line2"
              type="text"
              placeholder="Apt 4B"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                City
              </label>
              <input
                v-model="form.address.city"
                type="text"
                placeholder="New York"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                State / Province
              </label>
              <input
                v-model="form.address.state"
                type="text"
                placeholder="NY"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                ZIP / Postal Code
              </label>
              <input
                v-model="form.address.zip"
                type="text"
                placeholder="10001"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Country
            </label>
            <select
              v-model="form.address.country"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Select a country</option>
              <option value="US">United States</option>
              <option value="CA">Canada</option>
              <option value="GB">United Kingdom</option>
              <option value="AU">Australia</option>
              <option value="DE">Germany</option>
              <option value="FR">France</option>
              <option value="JP">Japan</option>
              <option value="CN">China</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Currency
            </label>
            <select
              v-model="form.currency"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="USD">USD - US Dollar</option>
              <option value="EUR">EUR - Euro</option>
              <option value="GBP">GBP - British Pound</option>
              <option value="CAD">CAD - Canadian Dollar</option>
              <option value="AUD">AUD - Australian Dollar</option>
              <option value="JPY">JPY - Japanese Yen</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Payment Method
            </label>
            <select
              v-model="form.paymentMethod"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">None (Will add later)</option>
              <option value="card">Credit Card</option>
              <option value="bank">Bank Account</option>
              <option value="paypal">PayPal</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Tax ID
            </label>
            <input
              v-model="form.taxId"
              type="text"
              placeholder="XX-XXXXXXX"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <p class="text-xs text-gray-500 mt-1">VAT, GST, or other tax identification number</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Invoice Prefix
            </label>
            <input
              v-model="form.invoicePrefix"
              type="text"
              placeholder="INV"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <p class="text-xs text-gray-500 mt-1">Custom prefix for invoice numbers</p>
          </div>
        </div>
      </div>

      <!-- Additional Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Settings</h2>
        <div class="space-y-4">
          <div class="flex items-start">
            <input
              v-model="form.sendInvoiceEmails"
              type="checkbox"
              id="sendInvoiceEmails"
              class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <label for="sendInvoiceEmails" class="ml-3">
              <span class="text-sm font-medium text-gray-900">Send Invoice Emails</span>
              <p class="text-sm text-gray-600">Automatically send invoice emails to this customer</p>
            </label>
          </div>

          <div class="flex items-start">
            <input
              v-model="form.sendPaymentEmails"
              type="checkbox"
              id="sendPaymentEmails"
              class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <label for="sendPaymentEmails" class="ml-3">
              <span class="text-sm font-medium text-gray-900">Send Payment Confirmation Emails</span>
              <p class="text-sm text-gray-600">Send receipt emails after successful payments</p>
            </label>
          </div>

          <div class="flex items-start">
            <input
              v-model="form.autoCharge"
              type="checkbox"
              id="autoCharge"
              class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <label for="autoCharge" class="ml-3">
              <span class="text-sm font-medium text-gray-900">Auto-charge on Invoice Creation</span>
              <p class="text-sm text-gray-600">Automatically attempt to charge when invoices are created</p>
            </label>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Notes (Internal)
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Add any internal notes about this customer..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-between">
        <NuxtLink
          to="/dashboard/customers"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
        >
          Cancel
        </NuxtLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Saving...' : (isEditMode ? 'Update Customer' : 'Create Customer') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const isEditMode = computed(() => route.params.id && route.params.id !== 'create')

useHead({
  title: `${isEditMode.value ? 'Edit' : 'Add'} Customer - Billing Platform`
})

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  email: '',
  phone: '',
  company: '',
  address: {
    line1: '',
    line2: '',
    city: '',
    state: '',
    zip: '',
    country: ''
  },
  currency: 'USD',
  paymentMethod: '',
  taxId: '',
  invoicePrefix: 'INV',
  sendInvoiceEmails: true,
  sendPaymentEmails: true,
  autoCharge: false,
  notes: ''
})

// Load customer data in edit mode
onMounted(() => {
  if (isEditMode.value) {
    // In production, fetch customer data from API
    form.name = 'John Doe'
    form.email = 'john@acmecorp.com'
    form.phone = '+1 (555) 123-4567'
    form.company = 'Acme Corporation'
    form.address.line1 = '123 Main Street'
    form.address.city = 'New York'
    form.address.state = 'NY'
    form.address.zip = '10001'
    form.address.country = 'US'
  }
})

const validateForm = () => {
  errors.value = {}
  
  if (!form.name || form.name.length < 2) {
    errors.value.name = 'Name must be at least 2 characters'
  }
  
  if (!form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In production, call API to create/update customer
    navigateTo('/dashboard/customers')
  } catch (error) {
    console.error('Error saving customer:', error)
  } finally {
    loading.value = false
  }
}
</script>
