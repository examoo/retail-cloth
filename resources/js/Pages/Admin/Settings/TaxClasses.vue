<script setup>
import { ref, watch, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search, Star } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const taxClasses = ref([]);
const isLoading = ref(true);
const filters = ref({ search: '', per_page: 25 });

const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({ name: '', rate: 0, is_default: false, is_active: true });
const errors = ref({});

const fetchTaxClasses = async () => {
    isLoading.value = true;
    try {
        const params = { per_page: filters.value.per_page, search: filters.value.search };
        const response = await axios.get('/api/admin/tax-classes', { params });
        taxClasses.value = response.data.data;
    } catch (error) {
        Swal.fire('Error', 'Failed to fetch tax classes.', 'error');
    } finally {
        isLoading.value = false;
    }
};

watch(filters, () => fetchTaxClasses(), { deep: true });
onMounted(() => fetchTaxClasses());

const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', rate: 0, is_default: false, is_active: true };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (tc) => {
    modalMode.value = 'edit';
    editingId.value = tc.id;
    form.value = { name: tc.name, rate: tc.rate, is_default: tc.is_default, is_active: tc.is_active };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/tax-classes', form.value);
            Swal.fire('Success', 'Tax class created.', 'success');
        } else {
            await axios.put(`/api/admin/tax-classes/${editingId.value}`, form.value);
            Swal.fire('Success', 'Tax class updated.', 'success');
        }
        closeModal();
        fetchTaxClasses();
    } catch (error) {
        if (error.response?.status === 422) errors.value = error.response.data.errors || {};
        else Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
    }
};

const deleteTaxClass = async (tc) => {
    const result = await Swal.fire({ title: 'Delete Tax Class?', text: `Delete "${tc.name}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/tax-classes/${tc.id}`);
            Swal.fire('Deleted!', 'Tax class deleted.', 'success');
            fetchTaxClasses();
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
                    <h1 class="text-2xl font-semibold text-gray-900">Tax Classes</h1>
                    <p class="text-sm text-gray-500">Manage tax rates</p>
                </div>
                <PrimaryButton @click="openCreateModal"><Plus class="w-4 h-4 mr-2" />Add Tax Class</PrimaryButton>
            </div>

            <div class="flex gap-4 bg-white p-4 rounded-lg shadow-sm items-center">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="relative px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="isLoading"><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                        <tr v-else-if="taxClasses.length === 0"><td colspan="5" class="px-6 py-4 text-center text-gray-500">No tax classes found.</td></tr>
                        <tr v-for="tc in taxClasses" :key="tc.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ tc.name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ tc.rate }}%</td>
                            <td class="px-6 py-4">
                                <Star v-if="tc.is_default" class="w-5 h-5 text-yellow-500 fill-yellow-500" />
                            </td>
                            <td class="px-6 py-4">
                                <span :class="tc.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">{{ tc.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(tc)" class="text-indigo-600 hover:text-indigo-900 p-1"><Edit class="w-4 h-4" /></button>
                                <button @click="deleteTaxClass(tc)" class="text-red-600 hover:text-red-900 p-1 ml-2"><Trash2 class="w-4 h-4" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">{{ modalMode === 'create' ? 'Add Tax Class' : 'Edit Tax Class' }}</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 w-full" placeholder="e.g., Standard, Reduced" required />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <InputLabel for="rate" value="Tax Rate (%)" />
                        <TextInput id="rate" v-model="form.rate" type="number" step="0.01" min="0" max="100" class="mt-1 w-full" required />
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center">
                            <input id="is_default" v-model="form.is_default" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                            <label for="is_default" class="ml-2 text-sm text-gray-900">Default tax class</label>
                        </div>
                        <div class="flex items-center">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                            <label for="is_active" class="ml-2 text-sm text-gray-900">Active</label>
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
