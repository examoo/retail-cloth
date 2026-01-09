<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import { Plus, Edit, Trash2, Search, Eye } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();

/* Data State */
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const isLoading = ref(true);
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });

const filters = ref({
    search: '',
    category_id: '',
    brand_id: '',
    status: '',
    per_page: 25,
});

/* Fetch Products */
const fetchProducts = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            page,
            per_page: filters.value.per_page,
            search: filters.value.search || undefined,
            category_id: filters.value.category_id || undefined,
            brand_id: filters.value.brand_id || undefined,
            status: filters.value.status || undefined,
        };
        const response = await axios.get('/api/admin/products', { params });
        products.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
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
        const [catRes, brandRes] = await Promise.all([
            axios.get('/api/admin/categories', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/brands', { params: { per_page: 100, is_active: true } }),
        ]);
        categories.value = catRes.data.data || [];
        brands.value = brandRes.data.data || [];
    } catch (error) {
        console.error('Failed to fetch dropdown data:', error);
    }
};

watch(filters, () => fetchProducts(1), { deep: true });

onMounted(() => {
    fetchDropdownData();
    fetchProducts();
});

/* Navigation */
const goToCreate = () => router.push({ name: 'products.create' });
const goToEdit = (id) => router.push({ name: 'products.edit', params: { id } });

/* Delete */
const deleteProduct = async (product) => {
    const result = await Swal.fire({
        title: 'Delete Product?',
        text: `Are you sure you want to delete "${product.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    });
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/products/${product.id}`);
            Swal.fire('Deleted!', 'Product has been deleted.', 'success');
            fetchProducts(pagination.value.current_page);
        } catch (error) {
            Swal.fire('Error', 'Failed to delete product.', 'error');
        }
    }
};

/* Pagination */
const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchProducts(page);
    }
};

const statusBadge = (status) => {
    const colors = {
        'draft': 'bg-gray-100 text-gray-700',
        'published': 'bg-green-100 text-green-700',
        'pos_only': 'bg-blue-100 text-blue-700',
        'online_only': 'bg-purple-100 text-purple-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const statusLabel = (status) => {
    const labels = { 'draft': 'Draft', 'published': 'Published', 'pos_only': 'POS Only', 'online_only': 'Online' };
    return labels[status] || status;
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
                    <p class="text-sm text-gray-500">Manage your product catalog</p>
                </div>
                <PrimaryButton @click="goToCreate">
                    <Plus class="w-4 h-4 mr-2" /> Add Product
                </PrimaryButton>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 bg-white p-4 rounded-lg shadow-sm items-center">
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <TextInput v-model="filters.search" placeholder="Search products..." class="pl-10 w-full" />
                </div>
                <SelectInput v-model="filters.category_id" class="w-40">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </SelectInput>
                <SelectInput v-model="filters.brand_id" class="w-36">
                    <option value="">All Brands</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </SelectInput>
                <SelectInput v-model="filters.status" class="w-36">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="pos_only">POS Only</option>
                    <option value="online_only">Online Only</option>
                </SelectInput>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variants</th>
                            <th class="relative px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="isLoading">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Loading...</td>
                        </tr>
                        <tr v-else-if="products.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No products found.</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                        <img v-if="product.images?.[0]?.image_url" :src="product.images[0].image_url" class="w-full h-full object-cover" />
                                        <span v-else class="text-gray-400 text-xs">No img</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-sm text-gray-500">{{ product.categories?.map(c => c.name).join(', ') || 'No category' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ product.product_type || 'stitched' }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">Rs. {{ product.price?.toLocaleString() }}</div>
                                <div v-if="product.sale_price" class="text-sm text-green-600">Sale: Rs. {{ product.sale_price?.toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="statusBadge(product.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ statusLabel(product.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ product.variants_count || product.variants?.length || 0 }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="goToEdit(product.id)" class="text-indigo-600 hover:text-indigo-900 p-1 mr-2" title="Edit">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-900 p-1" title="Delete">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="pagination.total > 0" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} products
                    </p>
                    <div class="flex gap-1">
                        <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                            class="px-3 py-1 text-sm rounded border hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 text-sm rounded border hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
