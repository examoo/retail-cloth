<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import InputLabel from '../../Components/InputLabel.vue';
import TextInput from '../../Components/TextInput.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { Lock, Mail, AlertCircle, User } from 'lucide-vue-next';

const router = useRouter();

const form = ref({
    email: '',
    password: '',
    remember: false,
});

const errors = ref({});
const isLoading = ref(false);
const generalError = ref('');

const isFormValid = computed(() => {
    return form.value.email && form.value.password;
});

const submit = async () => {
    if (!isFormValid.value) return;
    
    errors.value = {};
    generalError.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/customer/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
            },
            credentials: 'include',
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                errors.value = data.errors || {};
                if (data.message) {
                    generalError.value = data.message;
                }
            } else {
                generalError.value = data.message || 'An error occurred. Please try again.';
            }
            return;
        }

        // Redirect to home on success
        router.push({ name: 'home' });
    } catch (error) {
        generalError.value = 'Network error. Please check your connection.';
    } finally {
        isLoading.value = false;
    }
};

const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    return '';
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 p-4">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-50">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'52\' height=\'26\' viewBox=\'0 0 52 26\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%2310b981\' fill-opacity=\'0.08\'%3E%3Cpath d=\'M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4v2c-3.314 0-6-2.686-6-6 0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6zm25.464-1.95l8.486 8.486-1.414 1.414-8.486-8.486 1.414-1.414z\' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="px-8 pt-8 pb-6 text-center bg-gradient-to-r from-emerald-500 to-teal-500">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                        <User class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Welcome Back</h1>
                    <p class="text-emerald-100 text-sm">Sign in to your account</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-8 py-8 space-y-6">
                    <!-- General Error -->
                    <div v-if="generalError" 
                         class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                        <AlertCircle class="w-5 h-5 flex-shrink-0" />
                        <span>{{ generalError }}</span>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-gray-700 mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Mail class="w-5 h-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500 transition-all duration-200"
                                placeholder="you@example.com"
                                required
                            />
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-gray-700 mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Lock class="w-5 h-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500 transition-all duration-200"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password[0] }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                v-model="form.remember"
                                class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <PrimaryButton 
                        type="submit" 
                        :disabled="!isFormValid || isLoading"
                        class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isLoading ? 'Signing in...' : 'Sign In' }}
                    </PrimaryButton>

                    <!-- Register Link -->
                    <p class="text-center text-gray-600 text-sm">
                        Don't have an account? 
                        <router-link :to="{ name: 'customer.register' }" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            Sign up
                        </router-link>
                    </p>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center mt-6 text-gray-500 text-sm">
                RetailCloth &copy; 2026
            </p>
        </div>
    </div>
</template>
