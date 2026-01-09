<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Edit, Trash2, Plus, Search, X } from 'lucide-vue-next';

const router = useRouter();

const users = ref([]);
const pagination = ref({});
const isLoading = ref(false);
const isProcessing = ref(false);

const filters = ref({
    search: '',
    role: '',
    per_page: 10,
});

/* Modal State */
const showModal = ref(false);
const modalMode = ref('create'); // 'create' or 'edit'
const editingId = ref(null);

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'staff', // default
});

const errors = ref({});

const roles = [
    { value: '', label: 'All Roles' },
    { value: 'super_admin', label: 'Super Admin' },
    { value: 'admin', label: 'Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'tailor', label: 'Tailor' },
];

const fetchUsers = async (page = 1) => {
    isLoading.value = true;
    try {
        const query = new URLSearchParams({
            page: page,
            ...filters.value,
        });

        const response = await fetch(`/api/admin/users?${query}`);
        const data = await response.json();

        users.value = data.data;
        pagination.value = data;
    } catch (error) {
        console.error('Error fetching users:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to fetch users.',
        });
    } finally {
        isLoading.value = false;
    }
};

const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'staff',
    };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (user) => {
    modalMode.value = 'edit';
    editingId.value = user.id;
    form.value = {
        name: user.name,
        email: user.email,
        password: '', // blank for edit
        password_confirmation: '',
        role: user.roles[0]?.name || 'staff',
    };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'staff',
    };
    errors.value = {};
};

const submitForm = async () => {
    isProcessing.value = true;
    errors.value = {};

    try {
        const url = modalMode.value === 'create' 
            ? '/api/admin/users' 
            : `/api/admin/users/${editingId.value}`;
        
        const method = modalMode.value === 'create' ? 'POST' : 'PUT';

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        };

        const response = await fetch(url, {
            method: method,
            headers: headers,
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });
            closeModal();
            fetchUsers(pagination.value.current_page); // refresh list
        } else {
            if (response.status === 422) {
                errors.value = data.errors;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Something went wrong.',
                });
            }
        }
    } catch (error) {
        console.error('Error submitting form:', error);
         Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred.',
        });
    } finally {
        isProcessing.value = false;
    }
};

const deleteUser = (user) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`/api/admin/users/${user.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    Swal.fire(
                        'Deleted!',
                        'User has been deleted.',
                        'success'
                    );
                    fetchUsers(pagination.value.current_page);
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Failed to delete user');
                }
            } catch (error) {
                Swal.fire(
                    'Error!',
                    error.message,
                    'error'
                );
            }
        }
    });
};

watch(filters, () => {
    fetchUsers(1);
}, { deep: true });

onMounted(() => {
    fetchUsers();
});

const modalTitle = computed(() => modalMode.value === 'create' ? 'Add New User' : 'Edit User');
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Users</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage system users and their roles.</p>
                </div>
                <PrimaryButton @click="openCreateModal" class="flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Add User
                </PrimaryButton>
            </div>

            <!-- Table Controls & Filters -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-lg shadow-sm items-center">
                
                <!-- Left: Per Page & Search -->
                <div class="flex items-center gap-6 w-full sm:w-auto">
                    <div class="flex items-center text-sm text-gray-600 whitespace-nowrap">
                        <SelectInput
                            v-model="filters.per_page"
                            class="h-[38px] w-16 !py-1 mr-2 text-center"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </SelectInput>
                        <span>entries per page</span>
                    </div>

                    <div class="relative sm:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-4 w-4 text-gray-400" />
                        </div>
                        <TextInput
                            v-model="filters.search"
                            placeholder="Search users..."
                            class="w-full pl-10 h-[38px] !py-1"
                        />
                    </div>
                </div>

                <!-- Right: Role Filter -->
                <div class="w-full sm:w-48">
                    <SelectInput
                         v-model="filters.role"
                         class="h-[38px] !py-1"
                    >
                        <option value="">All Roles</option>
                        <option v-for="role in roles.filter(r => r.value)" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </SelectInput>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Joined
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Loading...
                                </td>
                            </tr>
                            <tr v-else-if="users.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        v-for="role in user.roles" 
                                        :key="role.id"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="{
                                            'bg-purple-100 text-purple-800': role.name === 'super_admin',
                                            'bg-blue-100 text-blue-800': role.name === 'admin',
                                            'bg-green-100 text-green-800': role.name === 'staff',
                                            'bg-orange-100 text-orange-800': role.name === 'tailor'
                                        }"
                                    >
                                        {{ role.name.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="openEditModal(user)"
                                            class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded"
                                            title="Edit"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteUser(user)"
                                            class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div 
                    v-if="pagination.total > 0"
                    class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                >
                    <!-- Mobile View -->
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="fetchUsers(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button 
                            @click="fetchUsers(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>

                    <!-- Desktop View -->
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ pagination.from }}</span>
                                to
                                <span class="font-medium">{{ pagination.to }}</span>
                                of
                                <span class="font-medium">{{ pagination.total }}</span>
                                entries
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- First / Previous -->
                                <button
                                    @click="fetchUsers(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <span class="sr-only">Previous</span>
                                    <span aria-hidden="true">&laquo;</span>
                                </button>
                                
                                <button
                                    v-for="page in pagination.links?.filter(link => !isNaN(link.label)).slice(Math.max(0, pagination.current_page - 3), Math.min(pagination.last_page, pagination.current_page + 2))" 
                                    :key="page.label"
                                    @click="fetchUsers(page.label)"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    :class="page.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                >
                                    {{ page.label }}
                                </button>

                                <!-- Next / Last -->
                                <button
                                    @click="fetchUsers(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <span class="sr-only">Next</span>
                                    <span aria-hidden="true">&raquo;</span>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">{{ modalTitle }}</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                    />
                    <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                    />
                    <p v-if="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email[0] }}</p>
                </div>

                <div>
                    <InputLabel for="role" value="Role" />
                    <SelectInput
                        id="role"
                        v-model="form.role"
                        class="mt-1 block w-full"
                    >
                        <option v-for="role in roles.filter(r => r.value)" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </SelectInput>
                    <p v-if="errors.role" class="text-sm text-red-600 mt-1">{{ errors.role[0] }}</p>
                </div>

                <div>
                    <InputLabel for="password" :value="modalMode === 'create' ? 'Password' : 'New Password (Optional)'" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        :required="modalMode === 'create'"
                    />
                    <p v-if="errors.password" class="text-sm text-red-600 mt-1">{{ errors.password[0] }}</p>
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        :required="modalMode === 'create' || form.password.length > 0"
                    />
                </div>

                 <div class="flex items-center justify-end mt-6 gap-3">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': isProcessing }" :disabled="isProcessing">
                        {{ modalMode === 'create' ? 'Create User' : 'Update User' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
