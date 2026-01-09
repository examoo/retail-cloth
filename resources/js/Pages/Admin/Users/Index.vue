<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import { Edit, Trash2, Plus, Search } from 'lucide-vue-next';

const router = useRouter();

const users = ref([]);
const pagination = ref({});
const isLoading = ref(false);

const filters = ref({
    search: '',
    role: '',
});

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
                    fetchUsers(pagination.current_page);
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
                <PrimaryButton @click="router.push('/app/users/create')" class="flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Add User
                </PrimaryButton>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        v-model="filters.search"
                        placeholder="Search users..."
                        class="w-full pl-10"
                    />
                </div>
                <div class="w-full sm:w-48">
                    <select
                        v-model="filters.role"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full h-[42px]"
                    >
                        <option v-for="role in roles" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>
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
                                            @click="router.push(`/app/users/${user.id}/edit`)"
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

                <!-- Pagination -->
                <div 
                    v-if="pagination.last_page > 1"
                    class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                >
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="fetchUsers(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Previous
                        </button>
                        <button 
                            @click="fetchUsers(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ pagination.from }}</span>
                                to
                                <span class="font-medium">{{ pagination.to }}</span>
                                of
                                <span class="font-medium">{{ pagination.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <button
                                    @click="fetchUsers(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <span class="sr-only">Previous</span>
                                    <!-- Use a simple chevron here manually to save import space or reuse lucide if needed, sticking to text for simple pagination or simple svg -->
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
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

                                <button
                                    @click="fetchUsers(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
