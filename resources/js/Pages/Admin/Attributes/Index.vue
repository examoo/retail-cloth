<script setup>
import { ref, watch, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

/* Data State */
const attributes = ref([]);
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
    values: [],
});
const newValue = ref('');
const errors = ref({});

/* Fetch Attributes */
const fetchAttributes = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            page,
            per_page: filters.value.per_page,
            search: filters.value.search,
        };
        const response = await axios.get('/api/admin/attributes', { params });
        attributes.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            links: response.data.links,
        };
    } catch (error) {
        console.error('Failed to fetch attributes:', error);
        Swal.fire('Error', 'Failed to fetch attributes.', 'error');
    } finally {
        isLoading.value = false;
    }
};

watch(filters, () => fetchAttributes(1), { deep: true });

onMounted(() => {
    fetchAttributes();
});

/* Modal Actions */
const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', values: [] };
    newValue.value = '';
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (attribute) => {
    modalMode.value = 'edit';
    editingId.value = attribute.id;
    form.value = {
        name: attribute.name,
        values: attribute.values?.map(v => v.value) || [],
    };
    newValue.value = '';
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const addValue = () => {
    if (newValue.value.trim() && !form.value.values.includes(newValue.value.trim())) {
        form.value.values.push(newValue.value.trim());
        newValue.value = '';
    }
};

const removeValue = (index) => {
    form.value.values.splice(index, 1);
};

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/attributes', form.value);
            Swal.fire('Success', 'Attribute created successfully.', 'success');
        } else {
            await axios.put(`/api/admin/attributes/${editingId.value}`, form.value);
            Swal.fire('Success', 'Attribute updated successfully.', 'success');
        }
        closeModal();
        fetchAttributes(pagination.value.current_page);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
        }
    }
};

const deleteAttribute = async (attribute) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Delete attribute "${attribute.name}" and all its values?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/attributes/${attribute.id}`);
            Swal.fire('Deleted!', 'Attribute has been deleted.', 'success');
            fetchAttributes(pagination.value.current_page);
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed to delete attribute.', 'error');
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
                    <h1 class="text-2xl font-semibold text-gray-900">Attributes</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage product attributes like Size, Color, Gender</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Attribute
                </PrimaryButton>
            </div>

            <!-- Controls -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center">
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
                        <TextInput v-model="filters.search" placeholder="Search attributes..." class="w-full pl-10 h-[38px] !py-1" />
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Values</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
                            </tr>
                            <tr v-else-if="attributes.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No attributes found.</td>
                            </tr>
                            <tr v-for="attribute in attributes" :key="attribute.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ attribute.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span 
                                            v-for="value in attribute.values" 
                                            :key="value.id"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                                        >
                                            {{ value.value }}
                                        </span>
                                        <span v-if="!attribute.values?.length" class="text-sm text-gray-400">No values</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(attribute)" class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded" title="Edit">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteAttribute(attribute)" class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded" title="Delete">
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
                            <button @click="fetchAttributes(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                &laquo;
                            </button>
                            <button v-for="page in pagination.links?.filter(link => !isNaN(link.label)).slice(Math.max(0, pagination.current_page - 3), Math.min(pagination.last_page, pagination.current_page + 2))" :key="page.label" @click="fetchAttributes(page.label)" :class="page.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                {{ page.label }}
                            </button>
                            <button @click="fetchAttributes(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
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
                    {{ modalMode === 'create' ? 'Add Attribute' : 'Edit Attribute' }}
                </h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Attribute Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" placeholder="e.g., Size, Color, Gender" required />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>
                    
                    <div>
                        <InputLabel value="Values" />
                        <div class="flex gap-2 mt-1">
                            <TextInput v-model="newValue" type="text" class="flex-1" placeholder="Add a value..." @keydown.enter.prevent="addValue" />
                            <SecondaryButton type="button" @click="addValue">Add</SecondaryButton>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span 
                                v-for="(value, index) in form.values" 
                                :key="index"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800"
                            >
                                {{ value }}
                                <button type="button" @click="removeValue(index)" class="text-indigo-600 hover:text-indigo-900">
                                    <X class="w-3 h-3" />
                                </button>
                            </span>
                            <span v-if="form.values.length === 0" class="text-sm text-gray-400">No values added yet</span>
                        </div>
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
