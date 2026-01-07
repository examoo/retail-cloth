<script setup>
import WebsiteLayout from '../../Layouts/WebsiteLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
</script>

<template>
    <WebsiteLayout>
        <div class="text-center py-20">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                Welcome to RetailCloth
            </h1>
            <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
                The best place to find high-quality clothing at affordable prices.
            </p>
            <div class="mt-8 flex justify-center">
                <PrimaryButton>Shop Now</PrimaryButton>
            </div>
        </div>
    </WebsiteLayout>
</template>
