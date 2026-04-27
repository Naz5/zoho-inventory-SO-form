import { z } from 'zod';

export const customerSchema = z.object({
  contact_name: z.string().min(1, 'Customer name is required'),
  email: z.string().email('Invalid email').optional().or(z.literal('')),
  phone: z.string().optional(),
  company_name: z.string().optional(),
});

export const lineItemSchema = z.object({
  item_id: z.string().min(1, 'Item is required'),
  quantity: z.number().min(1, 'Quantity must be at least 1'),
  rate: z.number().min(0).optional(),
  name: z.string().optional(),
  sku: z.string().optional(),
  stock_on_hand: z.number().optional(),
  create_po: z.boolean().default(false),
});

export const salesOrderSchema = z.object({
  customer_id: z.string().optional(),
  customer_name: z.string().optional(),
  customer_email: z.string().email('Invalid email').optional().or(z.literal('')),
  customer_phone: z.string().optional(),
  date: z.string().min(1, 'Date is required'),
  reference_number: z.string().optional(),
  line_items: z.array(lineItemSchema).min(1, 'At least one item is required'),
}).refine(data => data.customer_id || data.customer_name, {
  message: "Customer is required",
  path: ["customer_id"]
});

export type CustomerForm = z.infer<typeof customerSchema>;
export type LineItemForm = z.infer<typeof lineItemSchema>;
export type SalesOrderForm = z.infer<typeof salesOrderSchema>;

