<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <NuxtLink
        to="/dashboard/subscriptions"
        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
      >
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Subscriptions
      </NuxtLink>
      <h1 class="text-3xl font-bold text-gray-900">
        {{ isEditMode ? 'Edit Subscription' : 'Create Subscription' }}
      </h1>
      <p class="text-gray-600 mt-1">
        {{ isEditMode ? 'Update subscription details and billing information.' : 'Set up a new recurring subscription for a customer.' }}
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Customer Selection -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Customer <span class="text-red-500">*</span>
            </label>
            <select
              v-model="formData.customerId"
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              :class="{ 'border-red-500': errors.customerId }"
              required
            >
              <option value="">Select customer...</option>
              <option value="1">Acme Corporation (john@acmecorp.com)</option>
              <option value="2">TechStart Inc (jane@techstart.com)</option>
              <option value="3">Design Studio (bob@designstudio.com)</option>
              <option value="4">Global Systems (alice@globalsys.com)</option>
              <option value="5">Startup Co (charlie@startup.io)</option>
            </select>
            <p v-if="errors.customerId" class="text-red-500 text-sm mt-1">{{ errors.customerId }}</p>
          </div>
        </div>
      </div>

      <!-- Plan Selection -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Plan Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Plan <span class="text-red-500">*</span>
            </label>
            <select
              v-model="formData.planId"
              @change="updatePlanDetails"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              :class="{ 'border-red-500': errors.planId }"
              required
            >
              <option value="">Select plan...</option>
              <option value="basic">Basic Plan</option>
              <option value="professional">Professional Plan</option>
              <option value="enterprise">Enterprise Plan</option>
            </select>
            <p v-if="errors.planId" class="text-red-500 text-sm mt-1">{{ errors.planId }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Billing Cycle <span class="text-red-500">*</span>
            </label>
            <select
              v-model="formData.billingCycle"
              @change="updatePlanDetails"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              required
            >
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="annually">Annually</option>
            </select>
          </div>
        </div>

        <!-- Plan Preview -->
        <div v-if="planPreview" class="mt-4 p-4 bg-primary-50 border border-primary-200 rounded-lg">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-primary-900">{{ planPreview.name }}</h3>
            <span class="text-2xl font-bold text-primary-600">${{ planPreview.price }} / {{ formData.billingCycle }}</span>
          </div>
          <ul class="space-y-1 text-sm text-primary-800">
            <li v-for="(feature, index) in planPreview.features" :key="index" class="flex items-center">
              <svg class="w-4 h-4 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ feature }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Billing Configuration -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Configuration</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Start Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.startDate"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              First Billing Date
            </label>
            <input
              v-model="formData.firstBillingDate"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <p class="text-xs text-gray-500 mt-1">Leave empty to bill immediately</p>
          </div>

          <div class="md:col-span-2">
            <label class="flex items-center">
              <input
                v-model="formData.enableTrial"
                type="checkbox"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">Enable trial period</span>
            </label>
          </div>

          <div v-if="formData.enableTrial">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Trial Days
            </label>
            <input
              v-model.number="formData.trialDays"
              type="number"
              min="1"
              max="365"
              placeholder="14"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div v-if="formData.enableTrial">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Trial End Date
            </label>
            <input
              v-model="formData.trialEndDate"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
        </div>
      </div>

      <!-- Additional Options -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Options</h2>
        <div class="space-y-4">
          <div>
            <label class="flex items-center">
              <input
                v-model="formData.prorateBilling"
                type="checkbox"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">Prorate billing for partial periods</span>
            </label>
            <p class="text-xs text-gray-500 mt-1 ml-6">Charge proportionally for incomplete billing cycles</p>
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="formData.sendWelcomeEmail"
                type="checkbox"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">Send welcome email to customer</span>
            </label>
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="formData.autoRenew"
                type="checkbox"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">Automatically renew subscription</span>
            </label>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Notes (Internal)
            </label>
            <textarea
              v-model="formData.notes"
              rows="3"
              placeholder="Add any internal notes about this subscription..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-end space-x-3">
        <NuxtLink
          to="/dashboard/subscriptions"
          class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
        >
          Cancel
        </NuxtLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Saving...' : (isEditMode ? 'Update Subscription' : 'Create Subscription') }}
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

const isEditMode = computed(() => {
  return route.params.id && route.params.id !== 'create'
})

useHead({
  title: `${isEditMode.value ? 'Edit' : 'Create'} Subscription - Billing Platform`
})

const loading = ref(false)
const errors = ref({})

const formData = reactive({
  customerId: '',
  planId: '',
  billingCycle: 'monthly',
  startDate: new Date().toISOString().split('T')[0],
  firstBillingDate: '',
  enableTrial: false,
  trialDays: 14,
  trialEndDate: '',
  prorateBilling: true,
  sendWelcomeEmail: true,
  autoRenew: true,
  notes: ''
})

const planPreview = ref(null)

const plans = {
  basic: {
    name: 'Basic Plan',
    monthly: 29,
    quarterly: 79,
    annually: 290,
    features: ['Up to 1,000 transactions', 'Basic reporting', 'Email support', '99.9% uptime SLA']
  },
  professional: {
    name: 'Professional Plan',
    monthly: 99,
    quarterly: 279,
    annually: 990,
    features: ['Up to 10,000 transactions', 'Advanced reporting', 'Priority support', 'Custom webhooks', '99.95% uptime SLA']
  },
  enterprise: {
    name: 'Enterprise Plan',
    monthly: 299,
    quarterly: 849,
    annually: 2990,
    features: ['Unlimited transactions', 'Custom reporting', '24/7 dedicated support', 'Advanced webhooks', '99.99% uptime SLA', 'Custom integrations']
  }
}

const updatePlanDetails = () => {
  if (formData.planId && formData.billingCycle) {
    const plan = plans[formData.planId]
    planPreview.value = {
      name: plan.name,
      price: plan[formData.billingCycle],
      features: plan.features
    }
  } else {
    planPreview.value = null
  }
}

const validateForm = () => {
  errors.value = {}

  if (!formData.customerId) {
    errors.value.customerId = 'Please select a customer'
  }

  if (!formData.planId) {
    errors.value.planId = 'Please select a plan'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  loading.value = true

  setTimeout(() => {
    loading.value = false
    alert(`Subscription ${isEditMode.value ? 'updated' : 'created'} successfully!`)
    navigateTo('/dashboard/subscriptions')
  }, 1500)
}

// Load existing data in edit mode
onMounted(() => {
  if (isEditMode.value) {
    formData.customerId = '1'
    formData.planId = 'professional'
    formData.billingCycle = 'monthly'
    formData.startDate = '2026-01-01'
    formData.enableTrial = false
    formData.prorateBilling = true
    formData.sendWelcomeEmail = false
    formData.autoRenew = true
    formData.notes = 'Enterprise customer - priority support'
    
    updatePlanDetails()
  }
})
</script>
