<script setup>
import { ref } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import { ChevronLeft } from 'lucide-vue-next';

const router = useRouter();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const errors = ref({});
const isSubmitting = ref(false);

const roles = [
    { value: 'super_admin', label: 'Super Admin' },
    { value: 'admin', label: 'Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'tailor', label: 'Tailor' },
];

const submit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/admin/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'User created successfully.',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                router.push('/app/users');
            });
        } else {
            if (response.status === 422) {
                errors.value = data.errors;
            } else {
                throw new Error(data.message || 'Failed to create user');
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <button 
                    @click="router.back()" 
                    class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                >
                    <ChevronLeft class="w-5 h-5 text-gray-500" />
                </button>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Create User</h2>
                    <p class="mt-1 text-sm text-gray-500">Add a new user to the system.</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <InputLabel value="Full Name" />
                        <TextInput
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel value="Email Address" />
                        <TextInput
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel value="Role" />
                        <select
                            v-model="form.role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="" disabled>Select a role</option>
                            <option v-for="role in roles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <InputLabel value="Password" />
                        <TextInput
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel value="Confirm Password" />
                        <TextInput
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="router.back()"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                        >
                            Cancel
                        </button>
                        <PrimaryButton 
                            :disabled="isSubmitting"
                            class="min-w-[100px] justify-center"
                        >
                            <span v-if="isSubmitting">Saving...</span>
                            <span v-else>Create User</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
