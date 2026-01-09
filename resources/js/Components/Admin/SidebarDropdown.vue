<script setup>
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    icon: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const isOpen = ref(false);

const toggle = () => {
    if (!props.collapsed) {
        isOpen.value = !isOpen.value;
    }
};
</script>

<template>
    <div>
        <button
            @click="toggle"
            class="w-full text-gray-300 hover:bg-slate-800 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-150"
            :class="{ 'justify-center': collapsed }"
        >
            <svg
                class="flex-shrink-0 h-6 w-6"
                :class="collapsed ? '' : 'mr-3'"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
            </svg>
            <span v-if="!collapsed" class="flex-1 text-left">{{ label }}</span>
            <ChevronDown 
                v-if="!collapsed" 
                class="ml-2 h-4 w-4 transition-transform duration-200" 
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
        >
            <div v-if="isOpen && !collapsed" class="pl-8 mt-1 space-y-1 overflow-hidden">
                <slot />
            </div>
        </transition>
    </div>
</template>
