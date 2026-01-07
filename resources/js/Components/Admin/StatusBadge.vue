<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    }
});

const classes = computed(() => {
    switch (props.status.toLowerCase()) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'in stitching': return 'bg-blue-100 text-blue-800';
        case 'ready': return 'bg-purple-100 text-purple-800';
        case 'delivered': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
});
</script>

<template>
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="classes">
        {{ status }}
    </span>
</template>
