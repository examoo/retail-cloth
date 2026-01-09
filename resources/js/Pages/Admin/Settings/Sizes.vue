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

const sizes = ref([]);
const isLoading = ref(true);
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });
const filters = ref({ search: '', size_group: '', per_page: 25 });

const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({ name: '', size_group: 'adult', sort_order: 0, is_active: true });
const errors = ref({});

const fetchSizes = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = { page, per_page: filters.value.per_page, search: filters.value.search, size_group: filters.value.size_group || undefined };
        const response = await axios.get('/api/admin/sizes', { params });
        sizes.value = response.data.data;
        pagination.value = { current_page: response.data.current_page, last_page: response.data.last_page, from: response.data.from, to: response.data.to, total: response.data.total };
    } catch (error) {
        Swal.fire('Error', 'Failed to fetch sizes.', 'error');
    } finally {
        isLoading.value = false;
    }
};

watch(filters, () => fetchSizes(1), { deep: true });
onMounted(() => fetchSizes());

const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', size_group: 'adult', sort_order: 0, is_active: true };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (size) => {
    modalMode.value = 'edit';
    editingId.value = size.id;
    form.value = { name: size.name, size_group: size.size_group, sort_order: size.sort_order, is_active: size.is_active };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/sizes', form.value);
            Swal.fire('Success', 'Size created successfully.', 'success');
        } else {
            await axios.put(`/api/admin/sizes/${editingId.value}`, form.value);
            Swal.fire('Success', 'Size updated successfully.', 'success');
        }
        closeModal();
        fetchSizes(pagination.value.current_page);
    } catch (error) {
        if (error.response?.status === 422) errors.value = error.response.data.errors || {};
        else Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
    }
};

const deleteSize = async (size) => {
    const result = await Swal.fire({ title: 'Delete Size?', text: `Delete "${size.name}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/sizes/${size.id}`);
            Swal.fire('Deleted!', 'Size deleted.', 'success');
            fetchSizes(pagination.value.current_page);
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed to delete.', 'error');
        }
    }
};

const sizeGroups = [
    { value: 'baby', label: 'Baby' },
    { value: 'kids', label: 'Kids' },
    { value: 'adult', label: 'Adult' },
    { value: 'custom', label: 'Custom' },
];
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Sizes</h1>
                    <p class="text-sm text-gray-500">Manage product sizes</p>
                </div>
                <PrimaryButton @click="openCreateModal"><Plus class="w-4 h-4 mr-2" />Add Size</PrimaryButton>
            </div>

            <div class="flex gap-4 bg-white p-4 rounded-lg shadow-sm border items-center">
                <div class="relative flex-1 max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <TextInput v-model="filters.search" placeholder="Search..." class="pl-10 w-full" />
                </div>
                <SelectInput v-model="filters.size_group" class="w-32">
                    <option value="">All Groups</option>
                    <option v-for="g in sizeGroups" :key="g.value" :value="g.value">{{ g.label }}</option>
                </SelectInput>
            </div>

            <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="relative px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="isLoading"><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                        <tr v-else-if="sizes.length === 0"><td colspan="5" class="px-6 py-4 text-center text-gray-500">No sizes found.</td></tr>
                        <tr v-for="size in sizes" :key="size.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ size.name }}</td>
                            <td class="px-6 py-4 text-gray-500 capitalize">{{ size.size_group }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ size.sort_order }}</td>
                            <td class="px-6 py-4">
                                <span :class="size.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">{{ size.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(size)" class="text-indigo-600 hover:text-indigo-900 p-1"><Edit class="w-4 h-4" /></button>
                                <button @click="deleteSize(size)" class="text-red-600 hover:text-red-900 p-1 ml-2"><Trash2 class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">{{ modalMode === 'create' ? 'Add Size' : 'Edit Size' }}</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <InputLabel for="size_group" value="Size Group" />
                        <SelectInput id="size_group" v-model="form.size_group" class="mt-1 w-full">
                            <option v-for="g in sizeGroups" :key="g.value" :value="g.value">{{ g.label }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputLabel for="sort_order" value="Sort Order" />
                        <TextInput id="sort_order" v-model="form.sort_order" type="number" min="0" class="mt-1 w-full" />
                    </div>
                    <div class="flex items-center">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                        <label for="is_active" class="ml-2 text-sm text-gray-900">Active</label>
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
