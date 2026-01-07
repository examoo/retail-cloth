<script setup>
import { ref } from 'vue';
import PrimaryButton from './Components/PrimaryButton.vue';
import TextInput from './Components/TextInput.vue';
import InputLabel from './Components/InputLabel.vue';

const count = ref(0);
const name = ref('');
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white p-8 rounded shadow-lg text-center w-96">
            <h1 class="text-3xl font-bold text-blue-600 mb-4">Strict Standards</h1>
            <p class="text-gray-700 mb-6">Laravel 12 + Vue 3 (High Level)</p>

            <div class="mb-4 text-left">
                <InputLabel value="Enter your name" />
                <TextInput v-model="name" class="mt-1 block w-full" placeholder="John Doe" />
            </div>

            <div class="mt-6">
                <!-- Demonstrating PrimaryButton reuse -->
                <PrimaryButton @click="count++"> Count is: {{ count }} </PrimaryButton>
                <!-- Wait, PrimaryButton slot is default, so this is wrong in the slot usage if I copy-pasted wrong, but let's check -->
                <!-- Actually PrimaryButton definition was <slot /> so this is fine except the closing tag -->
            </div>

            <div v-if="name" class="mt-4 text-sm text-gray-500">Hello, {{ name }}!</div>
        </div>
    </div>
</template>
