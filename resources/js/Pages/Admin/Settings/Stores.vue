<script setup>
import { ref, watch, onMounted } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search, Star, MapPin } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const stores = ref([]);
const isLoading = ref(true);
const filters = ref({ search: '', per_page: 25 });

const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({ name: '', code: '', address: '', phone: '', email: '', is_default: false, is_active: true });
const errors = ref({});

const fetchStores = async () => {
    isLoading.value = true;
    try {
        const params = { per_page: filters.value.per_page, search: filters.value.search };
        const response = await axios.get('/api/admin/stores', { params });
        stores.value = response.data.data;
    } catch (error) {
        Swal.fire('Error', 'Failed to fetch stores.', 'error');
    } finally {
        isLoading.value = false;
    }
};

watch(filters, () => fetchStores(), { deep: true });
onMounted(() => fetchStores());

const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = { name: '', code: '', address: '', phone: '', email: '', is_default: false, is_active: true };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (store) => {
    modalMode.value = 'edit';
    editingId.value = store.id;
    form.value = { name: store.name, code: store.code, address: store.address || '', phone: store.phone || '', email: store.email || '', is_default: store.is_default, is_active: store.is_active };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
    errors.value = {};
    try {
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/stores', form.value);
            Swal.fire('Success', 'Store created.', 'success');
        } else {
            await axios.put(`/api/admin/stores/${editingId.value}`, form.value);
            Swal.fire('Success', 'Store updated.', 'success');
        }
        closeModal();
        fetchStores();
    } catch (error) {
        if (error.response?.status === 422) errors.value = error.response.data.errors || {};
        else Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
    }
};

const deleteStore = async (store) => {
    const result = await Swal.fire({ title: 'Delete Store?', text: `Delete "${store.name}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' });
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/stores/${store.id}`);
            Swal.fire('Deleted!', 'Store deleted.', 'success');
            fetchStores();
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
                    <h1 class="text-2xl font-semibold text-gray-900">Stores</h1>
                    <p class="text-sm text-gray-500">Manage store locations for POS and inventory</p>
                </div>
                <PrimaryButton @click="openCreateModal"><Plus class="w-4 h-4 mr-2" />Add Store</PrimaryButton>
            </div>

            <div class="flex gap-4 bg-white p-4 rounded-lg shadow-sm border items-center">
                <div class="relative flex-1 max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <TextInput v-model="filters.search" placeholder="Search..." class="pl-10 w-full" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="isLoading" class="col-span-full text-center py-8 text-gray-500">Loading...</div>
                <div v-else-if="stores.length === 0" class="col-span-full text-center py-8 text-gray-500">No stores found.</div>
                <div v-for="store in stores" :key="store.id" class="bg-white rounded-lg border shadow-sm p-5 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900">{{ store.name }}</h3>
                            <Star v-if="store.is_default" class="w-4 h-4 text-yellow-500 fill-yellow-500" title="Default Store" />
                        </div>
                        <span :class="store.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ store.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-600 font-mono">{{ store.code }}</div>
                    <div v-if="store.address" class="mt-2 flex items-start gap-1 text-sm text-gray-500">
                        <MapPin class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <span>{{ store.address }}</span>
                    </div>
                    <div v-if="store.phone || store.email" class="mt-2 text-sm text-gray-500">
                        <span v-if="store.phone">{{ store.phone }}</span>
                        <span v-if="store.phone && store.email"> · </span>
                        <span v-if="store.email">{{ store.email }}</span>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button @click="openEditModal(store)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
                        <button @click="deleteStore(store)" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">{{ modalMode === 'create' ? 'Add Store' : 'Edit Store' }}</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Store Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>
                        <div>
                            <InputLabel for="code" value="Store Code" />
                            <TextInput id="code" v-model="form.code" class="mt-1 w-full font-mono uppercase" placeholder="e.g., MAIN, BR01" required />
                            <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code[0] }}</p>
                        </div>
                    </div>
                    <div>
                        <InputLabel for="address" value="Address" />
                        <textarea id="address" v-model="form.address" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="phone" value="Phone" />
                            <TextInput id="phone" v-model="form.phone" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 w-full" />
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center">
                            <input id="is_default" v-model="form.is_default" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                            <label for="is_default" class="ml-2 text-sm text-gray-900">Default store</label>
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
