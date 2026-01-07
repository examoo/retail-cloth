<script setup>
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    openingStick: { type: Number, default: 0 },
    purchased: { type: Number, default: 0 },
    sold: { type: Number, default: 0 },
    closingStock: { type: Number, default: 0 },
});
</script>

<template>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-full">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Inventory Snapshot</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <p class="text-xs text-blue-600 font-medium uppercase">Opening</p>
                <p class="text-xl font-bold text-blue-900 mt-1">{{ openingStick }}</p>
            </div>
            <div class="bg-indigo-50 p-3 rounded-lg">
                <p class="text-xs text-indigo-600 font-medium uppercase">Purchased</p>
                <p class="text-xl font-bold text-indigo-900 mt-1">+{{ purchased }}</p>
            </div>
            <div class="bg-orange-50 p-3 rounded-lg">
                <p class="text-xs text-orange-600 font-medium uppercase">Sold</p>
                <p class="text-xl font-bold text-orange-900 mt-1">-{{ sold }}</p>
            </div>
            <div class="bg-emerald-50 p-3 rounded-lg">
                <p class="text-xs text-emerald-600 font-medium uppercase">Closing</p>
                <p class="text-xl font-bold text-emerald-900 mt-1">{{ closingStock }}</p>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <a href="#" class="text-sm text-indigo-600 font-medium hover:text-indigo-800 flex items-center">
                View Reports <ArrowRight class="w-4 h-4 ml-1" />
            </a>
        </div>
    </div>
</template>
