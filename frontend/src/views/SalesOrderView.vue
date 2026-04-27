<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import { Package, User, Calendar, Mail, Phone, UserPlus, Loader2 } from 'lucide-vue-next';

import { salesOrderSchema, type SalesOrderForm } from '../utils/schemas';
import { getItems, getCustomers, createSalesOrder } from '../api';
import ItemSubform from '../components/ItemSubform.vue';

const items = ref([]);
const customers = ref([]);
const isLoading = ref(true);
const successMessage = ref('');
const errorMessage = ref('');
const showNewCustomerForm = ref(false);

const { values, errors, defineField, handleSubmit, setFieldValue, resetForm } = useForm<SalesOrderForm>({
  validationSchema: toTypedSchema(salesOrderSchema),
  initialValues: {
    customer_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    date: new Date().toISOString().split('T')[0],
    reference_number: '',
    line_items: [{ item_id: '', quantity: 1, rate: 0, name: '', sku: '', stock_on_hand: 0, create_po: false }]
  }
});

const [customer_id] = defineField('customer_id');
const [customer_name] = defineField('customer_name');
const [customer_email] = defineField('customer_email');
const [customer_phone] = defineField('customer_phone');
const [date] = defineField('date');
const [reference_number] = defineField('reference_number');

onMounted(async () => {
  try {
    const [fetchedItems, fetchedCustomers] = await Promise.all([
      getItems(),
      getCustomers()
    ]);
    items.value = fetchedItems;
    customers.value = fetchedCustomers;
  } catch (err) {
    errorMessage.value = 'Failed to load data from server.';
  } finally {
    isLoading.value = false;
  }
});

const onSubmit = handleSubmit(async (data) => {
  isLoading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  
  try {
    const result = await createSalesOrder(data);
    successMessage.value = 'Sales Order processed successfully!';
    resetForm();
    showNewCustomerForm.value = false;
    
    // Refresh customers list to include new one if created
    const updatedCustomers = await getCustomers();
    customers.value = updatedCustomers;
  } catch (err: any) {
    errorMessage.value = err.response?.data?.error || 'Failed to create Sales Order.';
  } finally {
    isLoading.value = false;
  }
});

const toggleCustomerMode = () => {
  showNewCustomerForm.value = !showNewCustomerForm.value;
  if (showNewCustomerForm.value) {
    setFieldValue('customer_id', '');
  } else {
    setFieldValue('customer_name', '');
    setFieldValue('customer_email', '');
    setFieldValue('customer_phone', '');
  }
};
</script>

<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <!-- Initial Loading State -->
    <div v-if="isLoading && !customers.length" class="min-h-[60vh] flex flex-col items-center justify-center space-y-4 animate-in fade-in duration-500">
      <div class="relative">
        <Loader2 class="w-12 h-12 text-zoho-blue animate-spin" />
        <Package class="w-6 h-6 text-zoho-blue absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" />
      </div>
      <div class="text-center">
        <h2 class="text-lg font-bold text-zoho-text">Syncing with Zoho Inventory</h2>
        <p class="text-sm text-zoho-muted">Fetching latest items and customers...</p>
      </div>
    </div>

    <div v-else class="animate-in fade-in slide-in-from-bottom-2 duration-700">
      <div class="flex items-center justify-between mb-8 border-b pb-4">
        <h1 class="text-2xl font-bold flex items-center gap-2 text-zoho-text">
          <Package class="text-zoho-blue" /> New Sales Order
        </h1>
      </div>

      <div v-if="successMessage" class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="onSubmit" class="space-y-8">
        <!-- Top Section: Customer and Order Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 rounded-lg border">
          <div class="space-y-4">
            <div v-if="!showNewCustomerForm">
              <div class="flex items-center justify-between mb-1">
                <label class="zoho-label flex items-center gap-1 mb-0"><User size="14" /> Customer Name *</label>
                <button type="button" @click="toggleCustomerMode" class="text-xs text-zoho-blue font-medium flex items-center gap-1 hover:underline">
                  <UserPlus size="12" /> New Customer
                </button>
              </div>
              <select v-model="customer_id" class="zoho-input" :class="{ 'border-red-500': errors.customer_id }">
                <option value="" disabled>Select Customer</option>
                <option v-for="c in customers" :key="c.contact_id" :value="c.contact_id">
                  {{ c.contact_name }}
                </option>
              </select>
              <p v-if="errors.customer_id" class="text-red-500 text-xs mt-1">{{ errors.customer_id }}</p>
            </div>

            <div v-else class="p-4 bg-blue-50/50 rounded border border-blue-100 space-y-4 animate-in fade-in slide-in-from-top-1">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-zoho-blue uppercase">New Customer Information</h3>
                <button type="button" @click="toggleCustomerMode" class="text-xs text-zoho-muted hover:text-red-500 hover:underline">Choose Existing</button>
              </div>
              
              <div>
                <label class="zoho-label">Contact Name *</label>
                <input v-model="customer_name" type="text" class="zoho-input" placeholder="Full Name" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="zoho-label flex items-center gap-1"><Mail size="12" /> Email</label>
                  <input v-model="customer_email" type="email" class="zoho-input" placeholder="email@example.com" />
                </div>
                <div>
                  <label class="zoho-label flex items-center gap-1"><Phone size="12" /> Phone</label>
                  <input v-model="customer_phone" type="text" class="zoho-input" placeholder="+1..." />
                </div>
              </div>
            </div>
            
            <div>
              <label class="zoho-label flex items-center gap-1"> Reference#</label>
              <input v-model="reference_number" type="text" class="zoho-input" placeholder="SO-00001" />
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="zoho-label flex items-center gap-1"><Calendar size="14" /> Sales Order Date *</label>
              <input v-model="date" type="date" class="zoho-input" :class="{ 'border-red-500': errors.date }" />
              <p v-if="errors.date" class="text-red-500 text-xs mt-1">{{ errors.date }}</p>
            </div>
          </div>
        </div>

        <!-- Line Items Section -->
        <div>
          <h2 class="text-sm font-semibold text-zoho-muted uppercase mb-4 tracking-wider">Item Table</h2>
          <ItemSubform 
            v-model="values.line_items" 
            :items="items" 
            @update:modelValue="(val) => setFieldValue('line_items', val)"
          />
          <p v-if="errors.line_items" class="text-red-500 text-xs mt-2">{{ errors.line_items }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t font-small">
          <button type="submit" :disabled="isLoading" class="zoho-btn-primary px-8 text-base">
            {{ isLoading ? 'Processing...' : 'Save and Send' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>


