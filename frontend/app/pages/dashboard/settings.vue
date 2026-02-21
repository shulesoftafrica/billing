<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
      <p class="text-gray-600 mt-1">Manage your account and company preferences.</p>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
          :class="activeTab === tab.id 
            ? 'border-primary-500 text-primary-600' 
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'profile'" class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>
        <div class="space-y-4">
          <!-- Avatar -->
          <div class="flex items-center space-x-4">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-2xl font-bold">
              JD
            </div>
            <div>
              <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Change Photo
              </button>
              <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF. Max size 2MB.</p>
            </div>
          </div>

          <!-- Name -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              <input
                v-model="profileForm.firstName"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
              <input
                v-model="profileForm.lastName"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
              v-model="profileForm.email"
              type="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input
              v-model="profileForm.phone"
              type="tel"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <!-- Save Button -->
          <div class="flex justify-end">
            <button
              @click="saveProfile"
              class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>

      <!-- Password Change -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
            <input
              v-model="passwordForm.current"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input
              v-model="passwordForm.new"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
            <input
              v-model="passwordForm.confirm"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div class="flex justify-end">
            <button
              @click="changePassword"
              class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all"
            >
              Update Password
            </button>
          </div>
        </div>
      </div>

      <!-- Two-Factor Authentication -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h2>
            <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
          </div>
          <button
            @click="twoFactorEnabled = !twoFactorEnabled"
            class="px-4 py-2 border rounded-lg text-sm font-medium transition-colors"
            :class="twoFactorEnabled 
              ? 'bg-red-50 border-red-300 text-red-700 hover:bg-red-100' 
              : 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100'"
          >
            {{ twoFactorEnabled ? 'Disable' : 'Enable' }} 2FA
          </button>
        </div>
      </div>
    </div>

    <!-- Company Tab -->
    <div v-if="activeTab === 'company'" class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Company Information</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
            <input
              v-model="companyForm.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
              <select
                v-model="companyForm.industry"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Select industry...</option>
                <option value="technology">Technology</option>
                <option value="ecommerce">E-commerce</option>
                <option value="saas">SaaS</option>
                <option value="finance">Finance</option>
                <option value="healthcare">Healthcare</option>
                <option value="education">Education</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Size</label>
              <select
                v-model="companyForm.size"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Select size...</option>
                <option value="1-10">1-10 employees</option>
                <option value="11-50">11-50 employees</option>
                <option value="51-200">51-200 employees</option>
                <option value="201-500">201-500 employees</option>
                <option value="500+">500+ employees</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
            <input
              v-model="companyForm.website"
              type="url"
              placeholder="https://"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
            <input
              v-model="companyForm.supportEmail"
              type="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID / VAT Number</label>
            <input
              v-model="companyForm.taxId"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div class="flex justify-end">
            <button
              @click="saveCompany"
              class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Address</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
            <input
              v-model="companyForm.address.line1"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
            <input
              v-model="companyForm.address.line2"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
              <input
                v-model="companyForm.address.city"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State / Province</label>
              <input
                v-model="companyForm.address.state"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ZIP / Postal Code</label>
              <input
                v-model="companyForm.address.zip"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
            <select
              v-model="companyForm.address.country"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="US">United States</option>
              <option value="CA">Canada</option>
              <option value="GB">United Kingdom</option>
              <option value="DE">Germany</option>
              <option value="FR">France</option>
              <option value="AU">Australia</option>
            </select>
          </div>
          <div class="flex justify-end">
            <button
              @click="saveCompany"
              class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications Tab -->
    <div v-if="activeTab === 'notifications'" class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Notifications</h2>
        <div class="space-y-4">
          <div v-for="notification in emailNotifications" :key="notification.id" class="flex items-start justify-between py-3 border-b border-gray-100 last:border-0">
            <div class="flex-1">
              <h3 class="text-sm font-medium text-gray-900">{{ notification.title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ notification.description }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer ml-4">
              <input
                v-model="notification.enabled"
                type="checkbox"
                class="sr-only peer"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Webhook Notifications</h2>
        <div class="space-y-4">
          <div v-for="webhook in webhookNotifications" :key="webhook.id" class="flex items-start justify-between py-3 border-b border-gray-100 last:border-0">
            <div class="flex-1">
              <h3 class="text-sm font-medium text-gray-900">{{ webhook.title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ webhook.description }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer ml-4">
              <input
                v-model="webhook.enabled"
                type="checkbox"
                class="sr-only peer"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Frequency</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Digest Frequency</label>
            <select
              v-model="notificationFrequency"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="realtime">Real-time (as they happen)</option>
              <option value="hourly">Hourly digest</option>
              <option value="daily">Daily digest</option>
              <option value="weekly">Weekly digest</option>
            </select>
          </div>
          <div class="flex justify-end">
            <button
              @click="saveNotifications"
              class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all"
            >
              Save Preferences
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
  title: 'Settings - Billing Platform'
})

const activeTab = ref('profile')

const tabs = [
  { id: 'profile', name: 'Profile' },
  { id: 'company', name: 'Company' },
  { id: 'notifications', name: 'Notifications' }
]

const twoFactorEnabled = ref(false)

const profileForm = reactive({
  firstName: 'John',
  lastName: 'Doe',
  email: 'john@acmecorp.com',
  phone: '+1 (555) 123-4567'
})

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

const companyForm = reactive({
  name: 'Acme Corporation',
  industry: 'technology',
  size: '51-200',
  website: 'https://acmecorp.com',
  supportEmail: 'support@acmecorp.com',
  taxId: 'US123456789',
  address: {
    line1: '123 Business St',
    line2: 'Suite 100',
    city: 'San Francisco',
    state: 'CA',
    zip: '94102',
    country: 'US'
  }
})

const emailNotifications = ref([
  {
    id: 1,
    title: 'Payment Received',
    description: 'Get notified when a payment is successfully processed',
    enabled: true
  },
  {
    id: 2,
    title: 'Payment Failed',
    description: 'Get notified when a payment attempt fails',
    enabled: true
  },
  {
    id: 3,
    title: 'New Customer',
    description: 'Get notified when a new customer signs up',
    enabled: false
  },
  {
    id: 4,
    title: 'Subscription Changes',
    description: 'Get notified about subscription updates and cancellations',
    enabled: true
  },
  {
    id: 5,
    title: 'Weekly Summary',
    description: 'Receive a weekly summary of your account activity',
    enabled: true
  }
])

const webhookNotifications = ref([
  {
    id: 1,
    title: 'Payment Events',
    description: 'Send webhook notifications for all payment events',
    enabled: true
  },
  {
    id: 2,
    title: 'Customer Events',
    description: 'Send webhook notifications for customer-related events',
    enabled: true
  },
  {
    id: 3,
    title: 'Subscription Events',
    description: 'Send webhook notifications for subscription changes',
    enabled: true
  },
  {
    id: 4,
    title: 'Invoice Events',
    description: 'Send webhook notifications for invoice events',
    enabled: false
  }
])

const notificationFrequency = ref('realtime')

const saveProfile = () => {
  alert('Profile saved successfully!')
}

const changePassword = () => {
  if (passwordForm.new !== passwordForm.confirm) {
    alert('Passwords do not match!')
    return
  }
  alert('Password changed successfully!')
  passwordForm.current = ''
  passwordForm.new = ''
  passwordForm.confirm = ''
}

const saveCompany = () => {
  alert('Company information saved successfully!')
}

const saveNotifications = () => {
  alert('Notification preferences saved successfully!')
}
</script>
