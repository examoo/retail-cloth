<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <RouterLink to="/" class="font-bold text-xl text-indigo-600">RetailCloth</RouterLink>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <RouterLink to="/" class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-500 text-sm font-medium text-gray-900">
                                Home
                            </RouterLink>
                            <RouterLink to="/app" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Admin
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500 text-sm">&copy; 2026 RetailCloth. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
