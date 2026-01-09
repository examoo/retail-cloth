<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import { ChevronLeft } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const errors = ref({});
const isSubmitting = ref(false);
const isLoading = ref(true);

const roles = [
    { value: 'super_admin', label: 'Super Admin' },
    { value: 'admin', label: 'Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'tailor', label: 'Tailor' },
];

const fetchUser = async () => {
    try {
        const response = await fetch(`/api/admin/users/${route.params.id}`);
        if (!response.ok) throw new Error('Failed to fetch user');
        
        const data = await response.json();
        const user = data.user;
        
        form.value = {
            name: user.name,
            email: user.email,
            role: user.roles[0]?.name || '', // Assuming single role for now
            password: '',
            password_confirmation: '',
        };
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'User not found or access denied.',
        }).then(() => router.push('/app/users'));
    } finally {
        isLoading.value = false;
    }
};

const submit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const payload = { ...form.value };
        if (!payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }

        const response = await fetch(`/api/admin/users/${route.params.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'User updated successfully.',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                router.push('/app/users');
            });
        } else {
            if (response.status === 422) {
                errors.value = data.errors;
            } else {
                throw new Error(data.message || 'Failed to update user');
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

onMounted(() => {
    fetchUser();
});
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
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Edit User</h2>
                    <p class="mt-1 text-sm text-gray-500">Update user details.</p>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-12 bg-white rounded-lg shadow-sm">
                <p class="text-gray-500">Loading user details...</p>
            </div>

            <!-- Form -->
            <div v-else class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <InputLabel value="Full Name" />
                        <TextInput
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
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

                    <!-- Password (Optional) -->
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Change Password (Optional)</h3>
                        <div class="space-y-4">
                            <div>
                                <InputLabel value="New Password" />
                                <TextInput
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    placeholder="Leave blank to keep current password"
                                />
                                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                            </div>

                            <div>
                                <InputLabel value="Confirm New Password" />
                                <TextInput
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
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
                            <span v-else>Update User</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
