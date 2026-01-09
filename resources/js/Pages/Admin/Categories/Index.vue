<script setup>
import { ref, watch, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

/* Data State */
const categories = ref([]);
const parentCategories = ref([]);
const isLoading = ref(true);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    links: [],
});

const filters = ref({
    search: '',
    per_page: 10,
});

/* Modal State */
const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({
    name: '',
    description: '',
    parent_id: '',
    is_active: true,
});
const errors = ref({});

/* Fetch Categories */
const fetchCategories = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            page,
            per_page: filters.value.per_page,
            search: filters.value.search,
        };
        const response = await axios.get('/api/admin/categories', { params });
        categories.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            links: response.data.links,
        };
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        Swal.fire('Error', 'Failed to fetch categories.', 'error');
    } finally {
        isLoading.value = false;
    }
};

const fetchParentCategories = async () => {
    try {
        const response = await axios.get('/api/admin/categories', { params: { per_page: 100 } });
        parentCategories.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch parent categories:', error);
    }
};

watch(filters, () => fetchCategories(1), { deep: true });

onMounted(() => {
    fetchCategories();
    fetchParentCategories();
});

/* Modal Actions */
const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', description: '', parent_id: '', is_active: true };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (category) => {
    modalMode.value = 'edit';
    editingId.value = category.id;
    form.value = {
        name: category.name,
        description: category.description || '',
        parent_id: category.parent_id || '',
        is_active: category.is_active,
    };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/categories', form.value);
            Swal.fire('Success', 'Category created successfully.', 'success');
        } else {
            await axios.put(`/api/admin/categories/${editingId.value}`, form.value);
            Swal.fire('Success', 'Category updated successfully.', 'success');
        }
        closeModal();
        fetchCategories(pagination.value.current_page);
        fetchParentCategories();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
        }
    }
};

const deleteCategory = async (category) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Delete category "${category.name}"? This may also delete child categories.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/categories/${category.id}`);
            Swal.fire('Deleted!', 'Category has been deleted.', 'success');
            fetchCategories(pagination.value.current_page);
            fetchParentCategories();
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed to delete category.', 'error');
        }
    }
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Categories</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage product categories</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Category
                </PrimaryButton>
            </div>

            <!-- Controls -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-lg shadow-sm items-center">
                <div class="flex items-center gap-6 w-full sm:w-auto">
                    <div class="flex items-center text-sm text-gray-600 whitespace-nowrap">
                        <SelectInput v-model="filters.per_page" class="h-[38px] w-16 !py-1 mr-2 text-center">
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
                        <TextInput v-model="filters.search" placeholder="Search categories..." class="w-full pl-10 h-[38px] !py-1" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
                            </tr>
                            <tr v-else-if="categories.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No categories found.</td>
                            </tr>
                            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                    <div v-if="category.description" class="text-sm text-gray-500 truncate max-w-xs">{{ category.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ category.parent?.name || '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(category)" class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded" title="Edit">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCategory(category)" class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded" title="Delete">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.total > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ pagination.from }}</span> to <span class="font-medium">{{ pagination.to }}</span> of <span class="font-medium">{{ pagination.total }}</span> entries
                        </p>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="fetchCategories(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                &laquo;
                            </button>
                            <button v-for="page in pagination.links?.filter(link => !isNaN(link.label)).slice(Math.max(0, pagination.current_page - 3), Math.min(pagination.last_page, pagination.current_page + 2))" :key="page.label" @click="fetchCategories(page.label)" :class="page.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                {{ page.label }}
                            </button>
                            <button @click="fetchCategories(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                &raquo;
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ modalMode === 'create' ? 'Add Category' : 'Edit Category' }}
                </h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <InputLabel for="parent_id" value="Parent Category" />
                        <SelectInput id="parent_id" v-model="form.parent_id" class="mt-1 block w-full">
                            <option value="">None (Top Level)</option>
                            <option v-for="parent in parentCategories.filter(c => c.id !== editingId)" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </SelectInput>
                    </div>
                    <div class="flex items-center">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton type="button" @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton type="submit">{{ modalMode === 'create' ? 'Create' : 'Update' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>
