<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    color: {
        type: String, // 'green', 'orange', 'blue', 'purple'
        default: 'blue',
    },
});

// colorClasses removed as it was unused and logic is in template

</script>

<template>
    <div 
        class="bg-white overflow-hidden shadow-sm rounded-lg border-t-4"
        :class="`border-${color}-500`" 
        style="/* fallback for safelist issues if needed, strictly using classes usually better but dynamic replacement needs safelist */"
    >
        <!-- Note: We are using dynamic classes. In a real Tailwind setup, ensure these are safelisted or use full class strings in computed -->
        <!-- For better safety, let's use the computed style for the border separately if needed, but here we assume standard colors exist -->
        
        <div class="p-5 flex justify-between items-start">
            <div>
                <dt class="text-sm font-medium text-gray-500 truncate mb-1">{{ title }}</dt>
                <dd class="text-2xl font-bold text-gray-900">{{ value }}</dd>
            </div>
            <div 
                class="rounded-lg p-3"
                :class="[
                    color === 'green' ? 'bg-emerald-100 text-emerald-600' : 
                    color === 'orange' ? 'bg-orange-100 text-orange-600' :
                    color === 'blue' ? 'bg-blue-100 text-blue-600' :
                    color === 'purple' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100'
                ]"
            >
                <slot name="icon" />
            </div>
        </div>
    </div>
</template>
