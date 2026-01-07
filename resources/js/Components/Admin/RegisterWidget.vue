<script setup>
import { Lock, Unlock } from 'lucide-vue-next';

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    openingBalance: {
        type: [String, Number],
        default: 0,
    },
    currentBalance: {
        type: [String, Number],
        default: 0,
    }
});
</script>

<template>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-full flex flex-col relative overflow-hidden">
        <div class="flex justify-between items-start z-10">
            <div>
                 <h3 class="font-bold text-gray-900 text-lg">Cash Register</h3>
                 <div class="mt-2 flex items-center">
                    <span 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="isOpen ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                         {{ isOpen ? 'OPEN' : 'CLOSED' }}
                    </span>
                 </div>
            </div>
             <div class="p-2 rounded-lg bg-gray-50">
                <Unlock v-if="isOpen" class="w-6 h-6 text-green-600" />
                <Lock v-else class="w-6 h-6 text-red-600" />
            </div>
        </div>
        
        <div class="mt-6 space-y-3 z-10">
            <div class="flex justify-between text-sm">
                 <span class="text-gray-500">Opening Balance</span>
                 <span class="font-semibold text-gray-900">Rs. {{ openingBalance }}</span>
            </div>
             <div class="flex justify-between text-sm">
                 <span class="text-gray-500">Current Balance</span>
                 <span class="font-bold text-gray-900 text-base">Rs. {{ currentBalance }}</span>
            </div>
        </div>

        <div class="mt-auto pt-4 z-10">
            <button 
                class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                 :class="isOpen ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500'"
            >
                {{ isOpen ? 'Close Register' : 'Open Register' }}
            </button>
        </div>
        
        <!-- Decorative Background Circle -->
        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-gray-50 rounded-full z-0 opacity-50"></div>
    </div>
</template>
