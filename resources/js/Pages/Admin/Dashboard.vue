<script setup>
import AdminLayout from '../../Layouts/AdminLayout.vue';
import StatCard from '../../Components/Admin/StatCard.vue';
import QuickAction from '../../Components/Admin/QuickAction.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import RevenueChart from '../../Components/Admin/RevenueChart.vue';
import CategoryChart from '../../Components/Admin/CategoryChart.vue';
import TopProductsChart from '../../Components/Admin/TopProductsChart.vue';
import StatusBadge from '../../Components/Admin/StatusBadge.vue';
import RegisterWidget from '../../Components/Admin/RegisterWidget.vue';
import InventoryWidget from '../../Components/Admin/InventoryWidget.vue';

import { 
    DollarSign, 
    ShoppingCart, 
    CreditCard, 
    TrendingUp, 
    Plus, 
    Box, 
    FileText, 
    AlertTriangle,
    UserPlus,
} from 'lucide-vue-next';

// --- MOCK DATA ---

// Summary Data
const salesSummary = { value: 'Rs. 45,231', icon: DollarSign, color: 'green', title: 'Total Sales Today' };
const ordersSummary = { value: '12 / 4', icon: ShoppingCart, color: 'blue', title: 'Orders (Pending/Ready)' };
const expensesSummary = { value: 'Rs. 5,000', icon: CreditCard, color: 'orange', title: 'Start of Day Expense' };
const profitSummary = { value: 'Rs. 18,320', icon: TrendingUp, color: 'purple', title: 'Net Profit' };

// Pending Orders
const pendingOrders = [
    { id: '#ORD-001', customer: 'Ali Khan', items: 3, total: 'Rs. 4,500', status: 'In Stitching', date: '2 hrs ago' },
    { id: '#ORD-002', customer: 'Sarah Ahmed', items: 1, total: 'Rs. 1,200', status: 'Pending', date: '4 hrs ago' },
    { id: '#ORD-005', customer: 'John Doe', items: 5, total: 'Rs. 12,000', status: 'Ready', date: '1 day ago' },
];

// Low Stock
const lowStock = [
    { name: 'White Cotton Shirt (L)', stock: 2, limit: 5 },
    { name: 'Black Denim (32)', stock: 1, limit: 5 },
    { name: 'Silk Scarf', stock: 0, limit: 3 },
];

// New Customers
const newCustomers = [
    { name: 'Bilal Ahmed', time: '10:30 AM' },
    { name: 'Maria K.', time: '11:45 AM' },
    { name: 'Guest User', time: '01:15 PM' },
];

</script>

<template>
    <AdminLayout>
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    Dashboard Overview
                </h1>
                <p class="mt-1 text-sm text-gray-500">Real-time business insights.</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                 <PrimaryButton>
                    <Plus class="w-4 h-4 mr-2" />
                    New Order
                </PrimaryButton>
            </div>
        </div>

        <!-- Row 1: Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatCard :title="salesSummary.title" :value="salesSummary.value" :color="salesSummary.color">
                <template #icon><component :is="salesSummary.icon" class="w-6 h-6" /></template>
            </StatCard>
            <StatCard :title="ordersSummary.title" :value="ordersSummary.value" :color="ordersSummary.color">
                <template #icon><component :is="ordersSummary.icon" class="w-6 h-6" /></template>
            </StatCard>
             <StatCard :title="expensesSummary.title" :value="expensesSummary.value" :color="expensesSummary.color">
                <template #icon><component :is="expensesSummary.icon" class="w-6 h-6" /></template>
            </StatCard>
             <StatCard :title="profitSummary.title" :value="profitSummary.value" :color="profitSummary.color">
                <template #icon><component :is="profitSummary.icon" class="w-6 h-6" /></template>
            </StatCard>
        </div>

        <!-- Row 2: Operations & Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            <!-- Quick Actions (Col-5) -->
            <div class="lg:col-span-5 flex flex-col">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Operations</h2>
                <div class="grid grid-cols-2 gap-4 flex-1">
                    <QuickAction label="New Order" color="green">
                        <template #icon><Plus class="w-6 h-6" /></template>
                    </QuickAction>
                    <QuickAction label="Add Product" color="blue">
                         <template #icon><Box class="w-6 h-6" /></template>
                    </QuickAction>
                    <QuickAction label="Add Expense" color="orange">
                         <template #icon><CreditCard class="w-6 h-6" /></template>
                    </QuickAction>
                    <QuickAction label="Reports" color="purple">
                         <template #icon><FileText class="w-6 h-6" /></template>
                    </QuickAction>
                </div>
            </div>

            <!-- Cash Register (Col-3) -->
            <div class="lg:col-span-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Register Status</h2>
                <RegisterWidget :isOpen="true" openingBalance="5,000" currentBalance="12,450" />
            </div>

            <!-- Inventory Snapshot (Col-4) -->
            <div class="lg:col-span-4">
                 <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory Movement</h2>
                 <InventoryWidget :openingStick="1200" :purchased="50" :sold="32" :closingStock="1218" />
            </div>
        </div>

        <!-- Row 3: Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Sales Trend (Line) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Sales Analytics</h2>
                    <!-- Select -->
                </div>
                <RevenueChart />
            </div>

             <!-- Category Splits (Doughnut) -->
            <div class="bg-white p-6 rounded-xl shadow-sm">
                 <h2 class="text-lg font-semibold text-gray-900 mb-6">Sales by Category</h2>
                 <CategoryChart />
            </div>
        </div>
        
         <!-- Row 3.5: More Charts -->
         <div class="grid grid-cols-1 gap-8 mb-8">
              <div class="bg-white p-6 rounded-xl shadow-sm">
                 <h2 class="text-lg font-semibold text-gray-900 mb-6">Top Selling Variants</h2>
                 <div class="h-64">
                    <TopProductsChart />
                 </div>
            </div>
         </div>

        <!-- Row 4: Operational Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Pending Orders -->
            <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Pending Orders</h3>
                    <a href="#" class="text-indigo-600 text-sm font-medium hover:text-indigo-800">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="order in pendingOrders" :key="order.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.customer }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ order.total }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <StatusBadge :status="order.status" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Alerts & New Customers -->
            <div class="space-y-8">
                <!-- Low Stock -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                     <div class="px-6 py-4 border-b border-gray-100 bg-red-50">
                        <h3 class="text-lg font-semibold text-red-800 flex items-center">
                            <AlertTriangle class="w-5 h-5 mr-2" /> Low Stock Details
                        </h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="item in lowStock" :key="item.name" class="px-6 py-4 flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-900">{{ item.name }}</span>
                            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">{{ item.stock }} left</span>
                        </li>
                    </ul>
                </div>

                <!-- New Customers -->
                 <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                     <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <UserPlus class="w-5 h-5 mr-2 text-indigo-500" /> New Customers Today
                        </h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="cust in newCustomers" :key="cust.name" class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ cust.name }}</p>
                                <p class="text-xs text-gray-500">Joined at {{ cust.time }}</p>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold">
                                {{ cust.name.charAt(0) }}
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
