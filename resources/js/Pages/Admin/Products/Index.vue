<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import Modal from '../../../Components/Modal.vue';
import { Plus, Edit, Trash2, Search, Image as ImageIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

/* Data State */
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
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
    category_id: '',
    brand_id: '',
    per_page: 10,
});

/* Modal State */
const showModal = ref(false);
const modalMode = ref('create');
const editingId = ref(null);
const form = ref({
    name: '',
    description: '',
    sku: '',
    price: 0,
    sale_price: '',
    cost_price: '',
    categories: [],
    brand_id: '',
    stock_quantity: 0,
    is_active: true,
    images: [],
    attribute_values: [],
});
const errors = ref({});
const newImageUrl = ref('');

/* Fetch Products */
const fetchProducts = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            page,
            per_page: filters.value.per_page,
            search: filters.value.search,
            category_id: filters.value.category_id || undefined,
            brand_id: filters.value.brand_id || undefined,
        };
        const response = await axios.get('/api/admin/products', { params });
        products.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            links: response.data.links,
        };
    } catch (error) {
        console.error('Failed to fetch products:', error);
        Swal.fire('Error', 'Failed to fetch products.', 'error');
    } finally {
        isLoading.value = false;
    }
};

const fetchDropdownData = async () => {
    try {
        const [catRes, brandRes, attrRes] = await Promise.all([
            axios.get('/api/admin/categories', { params: { per_page: 100 } }),
            axios.get('/api/admin/brands', { params: { per_page: 100 } }),
            axios.get('/api/admin/attributes', { params: { per_page: 100 } }),
        ]);
        categories.value = catRes.data.data;
        brands.value = brandRes.data.data;
        attributes.value = attrRes.data.data;
    } catch (error) {
        console.error('Failed to fetch dropdown data:', error);
    }
};

watch(filters, () => fetchProducts(1), { deep: true });

onMounted(() => {
    fetchProducts();
    fetchDropdownData();
});

/* Modal Actions */
const openCreateModal = () => {
    modalMode.value = 'create';
    editingId.value = null;
    form.value = {
        name: '',
        description: '',
        sku: '',
        price: 0,
        sale_price: '',
        cost_price: '',
        categories: [],
        brand_id: '',
        stock_quantity: 0,
        is_active: true,
        images: [],
        attribute_values: [],
    };
    newImageUrl.value = '';
    errors.value = {};
    showModal.value = true;
};

const openEditModal = async (product) => {
    modalMode.value = 'edit';
    editingId.value = product.id;
    
    // Fetch full product details
    try {
        const response = await axios.get(`/api/admin/products/${product.id}`);
        const p = response.data.product;
        form.value = {
            name: p.name,
            description: p.description || '',
            sku: p.sku || '',
            price: p.price,
            sale_price: p.sale_price || '',
            cost_price: p.cost_price || '',
            categories: p.categories?.map(c => c.id) || [],
            brand_id: p.brand_id || '',
            stock_quantity: p.stock_quantity || 0,
            is_active: p.is_active,
            images: p.images?.map(img => ({ image_url: img.image_url, is_primary: img.is_primary })) || [],
            attribute_values: p.attribute_values?.map(av => av.id) || [],
        };
    } catch (error) {
        Swal.fire('Error', 'Failed to load product details.', 'error');
        return;
    }
    
    newImageUrl.value = '';
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const addImage = () => {
    if (newImageUrl.value.trim()) {
        form.value.images.push({
            image_url: newImageUrl.value.trim(),
            is_primary: form.value.images.length === 0,
        });
        newImageUrl.value = '';
    }
};

const removeImage = (index) => {
    form.value.images.splice(index, 1);
};

const setPrimaryImage = (index) => {
    form.value.images.forEach((img, i) => {
        img.is_primary = i === index;
    });
};

const toggleAttributeValue = (valueId) => {
    const idx = form.value.attribute_values.indexOf(valueId);
    if (idx > -1) {
        form.value.attribute_values.splice(idx, 1);
    } else {
        form.value.attribute_values.push(valueId);
    }
};

const toggleCategory = (catId) => {
    const idx = form.value.categories.indexOf(catId);
    if (idx > -1) {
        form.value.categories.splice(idx, 1);
    } else {
        form.value.categories.push(catId);
    }
};

const submitForm = async () => {
    errors.value = {};
    try {
        const payload = {
            ...form.value,
            sale_price: form.value.sale_price || null,
            cost_price: form.value.cost_price || null,
            brand_id: form.value.brand_id || null,
        };
        
        if (modalMode.value === 'create') {
            await axios.post('/api/admin/products', payload);
            Swal.fire('Success', 'Product created successfully.', 'success');
        } else {
            await axios.put(`/api/admin/products/${editingId.value}`, payload);
            Swal.fire('Success', 'Product updated successfully.', 'success');
        }
        closeModal();
        fetchProducts(pagination.value.current_page);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
        }
    }
};

const deleteProduct = async (product) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Delete product "${product.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/products/${product.id}`);
            Swal.fire('Deleted!', 'Product has been deleted.', 'success');
            fetchProducts(pagination.value.current_page);
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed to delete product.', 'error');
        }
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PKR' }).format(price);
};

const getPrimaryImage = (product) => {
    const primary = product.images?.find(img => img.is_primary);
    return primary?.image_url || product.images?.[0]?.image_url;
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your product catalog</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Product
                </PrimaryButton>
            </div>

            <!-- Controls -->
            <div class="flex flex-col sm:flex-row justify-between gap-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center">
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <div class="flex items-center text-sm text-gray-600 whitespace-nowrap">
                        <SelectInput v-model="filters.per_page" class="h-[38px] w-16 !py-1 mr-2 text-center">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </SelectInput>
                        <span>per page</span>
                    </div>
                    <div class="relative sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-4 w-4 text-gray-400" />
                        </div>
                        <TextInput v-model="filters.search" placeholder="Search products..." class="w-full pl-10 h-[38px] !py-1" />
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <SelectInput v-model="filters.category_id" class="h-[38px] !py-1 w-full sm:w-40">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </SelectInput>
                    <SelectInput v-model="filters.brand_id" class="h-[38px] !py-1 w-full sm:w-40">
                        <option value="">All Brands</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                    </SelectInput>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Brand</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="isLoading">
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
                            </tr>
                            <tr v-else-if="products.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No products found.</td>
                            </tr>
                            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
                                            <img v-if="getPrimaryImage(product)" :src="getPrimaryImage(product)" :alt="product.name" class="h-full w-full object-cover" />
                                            <ImageIcon v-else class="w-5 h-5 text-gray-400" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                            <div class="text-sm text-gray-500">{{ product.sku || '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="product.categories?.length">
                                        {{ product.categories.map(c => c.name).join(', ') }}
                                    </span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ product.brand?.name || '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ formatPrice(product.price) }}</div>
                                    <div v-if="product.sale_price" class="text-sm text-green-600">{{ formatPrice(product.sale_price) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ product.stock_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(product)" class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded" title="Edit">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded" title="Delete">
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
                            <button @click="fetchProducts(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                &laquo;
                            </button>
                            <button v-for="page in pagination.links?.filter(link => !isNaN(link.label)).slice(Math.max(0, pagination.current_page - 3), Math.min(pagination.last_page, pagination.current_page + 2))" :key="page.label" @click="fetchProducts(page.label)" :class="page.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                {{ page.label }}
                            </button>
                            <button @click="fetchProducts(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                &raquo;
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="5xl">
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ modalMode === 'create' ? 'Add Product' : 'Edit Product' }}
                </h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <InputLabel for="name" value="Product Name" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>
                        
                        <div>
                            <InputLabel for="sku" value="SKU" />
                            <TextInput id="sku" v-model="form.sku" type="text" class="mt-1 block w-full" />
                        </div>
                        
                        <div>
                            <InputLabel for="stock_quantity" value="Stock Quantity" />
                            <TextInput id="stock_quantity" v-model="form.stock_quantity" type="number" min="0" class="mt-1 block w-full" />
                        </div>
                        
                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput id="price" v-model="form.price" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                        </div>
                        
                        <div>
                            <InputLabel for="sale_price" value="Sale Price (optional)" />
                            <TextInput id="sale_price" v-model="form.sale_price" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        </div>
                        
                        <div>
                            <InputLabel for="cost_price" value="Cost Price (optional)" />
                            <TextInput id="cost_price" v-model="form.cost_price" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        </div>
                        
                        <div class="md:col-span-2">
                            <InputLabel value="Categories" />
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    type="button"
                                    @click="toggleCategory(cat.id)"
                                    class="px-3 py-1 text-sm rounded-full border transition-colors"
                                    :class="form.categories.includes(cat.id) ? 'bg-indigo-100 border-indigo-500 text-indigo-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                >
                                    {{ cat.name }}
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <InputLabel for="brand_id" value="Brand" />
                            <SelectInput id="brand_id" v-model="form.brand_id" class="mt-1 block w-full">
                                <option value="">Select Brand</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </SelectInput>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>
                    </div>
                    
                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Images -->
                    <div>
                        <InputLabel value="Product Images" />
                        <div class="flex gap-2 mt-1">
                            <TextInput v-model="newImageUrl" type="text" class="flex-1" placeholder="Image URL..." />
                            <SecondaryButton type="button" @click="addImage">Add</SecondaryButton>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <div v-for="(img, index) in form.images" :key="index" class="relative group">
                                <img :src="img.image_url" class="h-16 w-16 object-cover rounded border" :class="img.is_primary ? 'ring-2 ring-indigo-500' : ''" />
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center gap-1">
                                    <button type="button" @click="setPrimaryImage(index)" class="text-white text-xs">★</button>
                                    <button type="button" @click="removeImage(index)" class="text-white text-xs">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attributes -->
                    <div v-if="attributes.length > 0">
                        <InputLabel value="Attributes" />
                        <div class="mt-2 space-y-3">
                            <div v-for="attr in attributes" :key="attr.id" class="border rounded-lg p-3">
                                <div class="text-sm font-medium text-gray-700 mb-2">{{ attr.name }}</div>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="val in attr.values"
                                        :key="val.id"
                                        type="button"
                                        @click="toggleAttributeValue(val.id)"
                                        class="px-3 py-1 text-sm rounded-full border transition-colors"
                                        :class="form.attribute_values.includes(val.id) ? 'bg-indigo-100 border-indigo-500 text-indigo-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                    >
                                        {{ val.value }}
                                    </button>
                                </div>
                            </div>
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
