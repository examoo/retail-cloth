<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import InputLabel from '../../Components/InputLabel.vue';
import TextInput from '../../Components/TextInput.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { Lock, AlertCircle, CheckCircle, Mail } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

const form = ref({
    token: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const isLoading = ref(false);
const successMessage = ref('');
const generalError = ref('');

const isFormValid = computed(() => {
    return (
        form.value.email &&
        form.value.password &&
        form.value.password_confirmation &&
        form.value.password === form.value.password_confirmation
    );
});

onMounted(() => {
    // Get token and email from URL query params
    form.value.token = route.query.token || '';
    form.value.email = route.query.email || '';
});

const submit = async () => {
    if (!isFormValid.value) return;

    errors.value = {};
    generalError.value = '';
    successMessage.value = '';
    isLoading.value = true;

    try {
        const response = await fetch('/api/admin/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
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

        successMessage.value = data.message || 'Password reset successfully!';

        // Redirect to login after 2 seconds
        setTimeout(() => {
            router.push({ name: 'admin.login' });
        }, 2000);
    } catch {
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
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-4"
    >
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div
                class="absolute inset-0"
                style="
                    background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
                "
            ></div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Card -->
            <div
                class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 overflow-hidden"
            >
                <!-- Header -->
                <div class="px-8 pt-8 pb-6 text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 mb-4 shadow-lg"
                    >
                        <Lock class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Reset Password</h1>
                    <p class="text-gray-400 text-sm">Enter your new password below.</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-8 pb-8 space-y-6">
                    <!-- Success Message -->
                    <div
                        v-if="successMessage"
                        class="flex items-center gap-3 p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-300 text-sm"
                    >
                        <CheckCircle class="w-5 h-5 flex-shrink-0" />
                        <span>{{ successMessage }}</span>
                    </div>

                    <!-- General Error -->
                    <div
                        v-if="generalError"
                        class="flex items-center gap-3 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-300 text-sm"
                    >
                        <AlertCircle class="w-5 h-5 flex-shrink-0" />
                        <span>{{ generalError }}</span>
                    </div>

                    <!-- Email Field (readonly) -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-gray-300 mb-2" />
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                            >
                                <Mail class="w-5 h-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500 transition-all duration-200"
                                readonly
                            />
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-400">
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <!-- New Password Field -->
                    <div>
                        <InputLabel for="password" value="New Password" class="text-gray-300 mb-2" />
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                            >
                                <Lock class="w-5 h-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500 transition-all duration-200"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-400">
                            {{ errors.password[0] }}
                        </p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirm Password"
                            class="text-gray-300 mb-2"
                        />
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                            >
                                <Lock class="w-5 h-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500 transition-all duration-200"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <p
                            v-if="
                                form.password &&
                                form.password_confirmation &&
                                form.password !== form.password_confirmation
                            "
                            class="mt-2 text-sm text-red-400"
                        >
                            Passwords do not match
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <PrimaryButton
                        type="submit"
                        :disabled="!isFormValid || isLoading"
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                    >
                        <svg
                            v-if="isLoading"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ isLoading ? 'Resetting...' : 'Reset Password' }}
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </div>
</template>
