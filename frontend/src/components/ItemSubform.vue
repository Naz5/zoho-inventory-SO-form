<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Trash2, Plus, Info } from 'lucide-vue-next';
import type { LineItemForm } from '../utils/schemas';



const props = defineProps<{
  items: any[];
  modelValue: LineItemForm[];
}>();

const emit = defineEmits(['update:modelValue']);

const lineItems = ref<LineItemForm[]>(props.modelValue.length > 0 ? JSON.parse(JSON.stringify(props.modelValue)) : [
  { item_id: '', quantity: 1, rate: 0, name: '', sku: '', stock_on_hand: 0, create_po: false }
]);

watch(lineItems, (val) => {
  emit('update:modelValue', val);
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(lineItems.value)) {
    lineItems.value = JSON.parse(JSON.stringify(newVal));
  }
}, { deep: true });

const addItem = () => {
  lineItems.value.push({ 
    item_id: '', 
    quantity: 1, 
    rate: 0, 
    name: '', 
    sku: '', 
    stock_on_hand: 0, 
    create_po: false 
  });
};

const removeItem = (index: number) => {
  if (lineItems.value.length > 1) {
    lineItems.value.splice(index, 1);
  }
};

const onItemSelect = (index: number, itemId: string) => {
  const selectedItem = props.items.find(i => i.item_id === itemId);
  if (selectedItem) {
    const currentItem = lineItems.value[index];
    currentItem.item_id = selectedItem.item_id;
    currentItem.name = selectedItem.name;
    currentItem.sku = selectedItem.sku;
    currentItem.rate = selectedItem.rate;
    currentItem.stock_on_hand = selectedItem.stock_on_hand;
    // Auto-check PO if stock is low initially
    currentItem.create_po = (selectedItem.stock_on_hand || 0) < currentItem.quantity;
  }
};

const handleQuantityChange = (index: number) => {
  const item = lineItems.value[index];
  if (item.item_id && (item.stock_on_hand || 0) < item.quantity) {
    item.create_po = true;
  }
};


const getLineTotal = (item: LineItemForm) => {
  const qty = Number(item.quantity) || 0;
  const rate = Number(item.rate) || 0;
  return (qty * rate).toFixed(2);
};

const computedGrandTotal = computed(() => {
  const total = lineItems.value.reduce((acc, item) => {
    const qty = Number(item.quantity) || 0;
    const rate = Number(item.rate) || 0;
    return acc + (qty * rate);
  }, 0);
  return total.toFixed(2);
});

// Calculate which items will trigger a Purchase Order and for what quantity
const poItems = computed(() => {
  return lineItems.value
    .filter(item => item.create_po && item.item_id && (item.stock_on_hand || 0) < item.quantity)
    .map(item => ({
      name: item.name || 'Unknown Item',
      needed: item.quantity - (item.stock_on_hand || 0),
      stock: item.stock_on_hand || 0,
      order: item.quantity
    }));
});
</script>

<template>
  <div class="mt-8 border rounded-lg overflow-hidden bg-white shadow-sm">
    <table class="w-full text-left border-collapse">
      <thead class="bg-gray-50 border-b">
        <tr>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider w-1/2">Item Details</th>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider text-right">Quantity</th>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider text-right">Rate</th>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider text-center w-24">Create PO?</th>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider text-right">Amount</th>
          <th class="px-4 py-3 text-[11px] font-bold text-zoho-muted uppercase tracking-wider w-10"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="(item, index) in lineItems" :key="index" class="hover:bg-blue-50/20 transition-colors group">
          <td class="px-4 py-3">
            <select 
              v-model="item.item_id" 
              @change="onItemSelect(index, item.item_id)"
              class="zoho-input"
            >
              <option value="" disabled>Select an item</option>
              <option v-for="i in items" :key="i.item_id" :value="i.item_id">
                {{ i.name }}
              </option>
            </select>
            <div v-if="item.sku" class="mt-1 flex items-center gap-2">
              <span class="text-[10px] font-mono text-zoho-muted bg-gray-100 px-1 rounded">{{ item.sku }}</span>
              <span v-if="item.stock_on_hand !== undefined" 
                class="text-[10px]"
                :class="item.stock_on_hand < item.quantity ? 'text-red-500 font-bold' : 'text-green-600'"
              >
                Stock: {{ item.stock_on_hand }}
              </span>
            </div>
          </td>
          <td class="px-4 py-3">
            <input 
              type="number" 
              v-model.number="item.quantity" 
              @input="handleQuantityChange(index)"
              min="1" 
              class="zoho-input text-right"
              placeholder="0"
            />
          </td>
          <td class="px-4 py-3">
            <input 
              type="number" 
              v-model.number="item.rate" 
              step="0.01" 
              class="zoho-input text-right"
              placeholder="0.00"
            />
          </td>
          <td class="px-4 py-3 text-center">
            <div class="flex justify-center">
              <input 
                type="checkbox" 
                v-model="item.create_po" 
                class="w-4 h-4 text-zoho-blue rounded border-gray-300 focus:ring-zoho-blue cursor-pointer"
              />
            </div>
          </td>
          <td class="px-4 py-3 text-sm font-semibold text-zoho-text text-right tabular-nums">
            {{ getLineTotal(item) }}
          </td>
          <td class="px-4 py-3 text-right">
            <button 
              type="button" 
              @click="removeItem(index)" 
              class="text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100"
              title="Remove Row"
            >
              <Trash2 size="16" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div class="p-4 bg-gray-50/50 border-t flex flex-col md:flex-row justify-between items-start gap-6">
      <div class="flex-1 w-full">
        <button 
          type="button" 
          @click="addItem" 
          class="flex items-center gap-1 text-zoho-blue font-bold text-xs hover:text-blue-700 transition-colors mb-4"
        >
          <Plus size="14" /> ADD ANOTHER LINE
        </button>

        <!-- Purchase Order Summary Display -->
        <div v-if="poItems.length > 0" class="p-4 bg-orange-50 border border-orange-100 rounded-lg animate-in fade-in slide-in-from-left-2">
          <div class="flex items-center gap-2 text-orange-800 font-bold text-xs uppercase mb-3">
            <Info size="14" /> Purchase Order Preview
          </div>
          <ul class="space-y-2">
            <li v-for="po in poItems" :key="po.name" class="flex items-center justify-between text-sm border-b border-orange-100 pb-2 last:border-0 last:pb-0">
              <span class="text-orange-900 font-medium">{{ po.name }}</span>
              <div class="flex items-center gap-3">
                <span class="text-[10px] text-orange-700 font-medium bg-white px-2 py-0.5 rounded border border-orange-200">
                  Stock: {{ po.stock }} / Order: {{ po.order }}
                </span>
                <span class="text-orange-900 font-bold">Needed: {{ po.needed }}</span>
              </div>
            </li>
          </ul>
          <p class="mt-3 text-[10px] text-orange-600 italic">
            * Separate Purchase Orders will be created for each vendor.
          </p>
        </div>
      </div>
      
      <div class="w-full md:w-64 space-y-3 shrink-0">
        <div class="flex justify-between text-sm">
          <span class="text-zoho-muted">Sub Total</span>
          <span class="font-medium tabular-nums">{{ computedGrandTotal }}</span>
        </div>
        <div class="flex justify-between items-center pt-3 border-t-2 border-gray-200">
          <span class="text-base font-bold text-zoho-text">Total (USD)</span>
          <span class="text-xl font-black text-zoho-blue tabular-nums">{{ computedGrandTotal }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

