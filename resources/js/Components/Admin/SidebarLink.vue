<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const props = defineProps({
    to: {
        type: String,
        required: true,
    },
    icon: {
        type: String, // Expecting SVG path data or eventually an icon component
        default: '',
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const route = useRoute();
const active = computed(() => route.path === props.to || route.path.startsWith(`${props.to}/`));
</script>

<template>
    <RouterLink
        :to="to"
        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 ease-in-out"
        :class="[
            active
                ? 'bg-gray-900 text-white'
                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
            collapsed ? 'justify-center' : ''
        ]"
    >
        <span 
            class="flex-shrink-0 h-6 w-6"
            :class="collapsed ? '' : 'mr-3'"
        >
            <!-- Render icon slot or fallback -->
            <slot name="icon">
                <svg v-if="icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
                </svg>
            </slot>
        </span>
        <span v-if="!collapsed">
            <slot />
        </span>
    </RouterLink>
</template>
