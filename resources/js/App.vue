<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white p-8 rounded shadow-lg text-center">
            <h1 class="text-3xl font-bold text-blue-600 mb-4">Hello World</h1>
            <p class="text-gray-700">Laravel 12 + Vue 3 (Vite)</p>
            <div class="mt-6">
                <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition" @click="count++">
                    Count is: {{ count }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>
