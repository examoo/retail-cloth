<script setup>
import { ref, watch, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const fits = ref([]);
const isLoading = ref(true);
const filters = ref({ search: '', per_page: 25 });

const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({ name: '', is_active: true });
const errors = ref({});

const fetchFits = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = { page, per_page: filters.value.per_page, search: filters.value.search };
        const response = await axios.get('/api/admin/fits', { params });
        fits.value = response.data.data;
    } catch (error) {
        Swal.fire('Error', 'Failed to fetch fits.', 'error');
    } finally {
        isLoading.value = false;
    }
};

watch(filters, () => fetchFits(1), { deep: true });
onMounted(() => fetchFits());

const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', is_active: true };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (fit) => {
    modalMode.value = 'edit';
    editingId.value = fit.id;
    form.value = { name: fit.name, is_active: fit.is_active };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/fits', form.value);
            Swal.fire('Success', 'Fit created.', 'success');
        } else {
            await axios.put(`/api/admin/fits/${editingId.value}`, form.value);
            Swal.fire('Success', 'Fit updated.', 'success');
        }
        closeModal();
        fetchFits();
    } catch (error) {
        if (error.response?.status === 422) errors.value = error.response.data.errors || {};
        else Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
    }
};

const deleteFit = async (fit) => {
    const result = await Swal.fire({ title: 'Delete Fit?', text: `Delete "${fit.name}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/fits/${fit.id}`);
            Swal.fire('Deleted!', 'Fit deleted.', 'success');
            fetchFits();
        } catch (error) {
            Swal.fire('Error', 'Failed to delete.', 'error');
        }
    }
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Fits</h1>
                    <p class="text-sm text-gray-500">Manage fit styles (Slim, Regular, Loose)</p>
                </div>
                <PrimaryButton @click="openCreateModal"><Plus class="w-4 h-4 mr-2" />Add Fit</PrimaryButton>
            </div>

            <div class="flex gap-4 bg-white p-4 rounded-lg shadow-sm border items-center">
                <div class="relative flex-1 max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <TextInput v-model="filters.search" placeholder="Search..." class="pl-10 w-full" />
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="relative px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="isLoading"><td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                        <tr v-else-if="fits.length === 0"><td colspan="3" class="px-6 py-4 text-center text-gray-500">No fits found.</td></tr>
                        <tr v-for="fit in fits" :key="fit.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ fit.name }}</td>
                            <td class="px-6 py-4">
                                <span :class="fit.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">{{ fit.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(fit)" class="text-indigo-600 hover:text-indigo-900 p-1"><Edit class="w-4 h-4" /></button>
                                <button @click="deleteFit(fit)" class="text-red-600 hover:text-red-900 p-1 ml-2"><Trash2 class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">{{ modalMode === 'create' ? 'Add Fit' : 'Edit Fit' }}</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 w-full" placeholder="e.g., Slim, Regular, Loose" required />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
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
