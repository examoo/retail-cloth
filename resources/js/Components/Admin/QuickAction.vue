<script setup>
defineProps({
    label: String,
    color: {
        type: String, // 'green', 'blue', 'orange', 'purple'
        default: 'blue',
    },
});
</script>

<template>
    <button
        class="flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100 w-full"
    >
        <div 
            class="p-3 rounded-lg mb-3"
            :class="[
                 color === 'green' ? 'bg-emerald-500 text-white' : 
                 color === 'orange' ? 'bg-orange-500 text-white' :
                 color === 'blue' ? 'bg-blue-500 text-white' :
                 color === 'purple' ? 'bg-purple-500 text-white' : 'bg-gray-500 text-white'
            ]"
        >
            <slot name="icon" />
        </div>
        <span class="text-sm font-medium text-gray-700">{{ label }}</span>
    </button>
</template>
