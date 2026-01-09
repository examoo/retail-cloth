<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import { ArrowLeft, Plus, Trash2, Package, FileText, Tag, Layers } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const isEditMode = computed(() => !!route.params.id);
const productId = computed(() => route.params.id);
const isLoading = ref(false);
const isSaving = ref(false);
const errors = ref({});

// Master data
const categories = ref([]);
const brands = ref([]);
const sizes = ref([]);
const colors = ref([]);
const fabrics = ref([]);
const fits = ref([]);
const taxClasses = ref([]);

// Form data
const form = ref({
    // Basic Info
    name: '',
    slug: '',
    short_description: '',
    description: '',
    product_type: 'stitched',
    fabric_type: '',
    care_instructions: '',
    season: '',
    status: 'draft',
    is_featured: false,
    is_bestseller: false,
    is_active: true,
    
    // SEO
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    
    // Categories & Brand
    categories: [],
    brand_id: null,
    
    // Pricing (base)
    price: 0,
    sale_price: null,
    cost_price: null,
    
    // Variants
    variants: []
});

// Create default variant
const createDefaultVariant = () => ({
    id: null,
    sku: '',
    barcode: '',
    size_id: null,
    color_id: null,
    fabric_id: null,
    fit_id: null,
    cost_price: null,
    retail_price: 0,
    sale_price: null,
    tax_class_id: null,
    stock_quantity: 0,
    low_stock_threshold: 5,
    is_online: true,
    is_pos: true,
    is_active: true,
    images: [], // Array of image URLs
});

// Fetch master data
const fetchMasterData = async () => {
    try {
        const [catRes, brandRes, sizeRes, colorRes, fabricRes, fitRes, taxRes] = await Promise.all([
            axios.get('/api/admin/categories', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/brands', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/sizes', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/colors', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/fabrics', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/fits', { params: { per_page: 100, is_active: true } }),
            axios.get('/api/admin/tax-classes', { params: { per_page: 100, is_active: true } }),
        ]);
        categories.value = catRes.data.data || [];
        brands.value = brandRes.data.data || [];
        sizes.value = sizeRes.data.data || [];
        colors.value = colorRes.data.data || [];
        fabrics.value = fabricRes.data.data || [];
        fits.value = fitRes.data.data || [];
        taxClasses.value = taxRes.data.data || [];
    } catch (error) {
        console.error('Failed to fetch master data:', error);
    }
};

// Fetch product for edit
const fetchProduct = async () => {
    if (!isEditMode.value) return;
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/admin/products/${productId.value}`);
        const product = response.data.product;
        form.value = {
            name: product.name,
            slug: product.slug,
            short_description: product.short_description || '',
            description: product.description || '',
            product_type: product.product_type || 'stitched',
            fabric_type: product.fabric_type || '',
            care_instructions: product.care_instructions || '',
            season: product.season || '',
            status: product.status || 'draft',
            is_featured: product.is_featured || false,
            is_bestseller: product.is_bestseller || false,
            is_active: product.is_active ?? true,
            meta_title: product.meta_title || '',
            meta_description: product.meta_description || '',
            meta_keywords: product.meta_keywords || '',
            categories: product.categories?.map(c => c.id) || [],
            brand_id: product.brand_id,
            price: product.price || 0,
            sale_price: product.sale_price,
            cost_price: product.cost_price,
            variants: product.variants?.length ? product.variants.map(v => ({
                id: v.id,
                sku: v.sku,
                barcode: v.barcode || '',
                size_id: v.size_id,
                color_id: v.color_id,
                fabric_id: v.fabric_id,
                fit_id: v.fit_id,
                cost_price: v.cost_price,
                retail_price: v.retail_price,
                sale_price: v.sale_price,
                tax_class_id: v.tax_class_id,
                stock_quantity: v.stock_quantity,
                low_stock_threshold: v.low_stock_threshold,
                is_online: v.is_online,
                is_pos: v.is_pos,
                is_active: v.is_active,
                images: v.images?.map(img => img.image_url) || [],
            })) : [createDefaultVariant()],
        };
    } catch (error) {
        Swal.fire('Error', 'Failed to load product.', 'error');
        router.push({ name: 'products.index' });
    } finally {
        isLoading.value = false;
    }
};

// Auto-generate slug
watch(() => form.value.name, (newName) => {
    if (!isEditMode.value && newName) {
        form.value.slug = newName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
});

// Add variant
const addVariant = () => {
    form.value.variants.push(createDefaultVariant());
};

// Remove variant
const removeVariant = (index) => {
    if (form.value.variants.length > 1) {
        form.value.variants.splice(index, 1);
    } else {
        Swal.fire('Warning', 'At least one variant is required.', 'warning');
    }
};

// Toggle category selection
const toggleCategory = (categoryId) => {
    const index = form.value.categories.indexOf(categoryId);
    if (index > -1) {
        form.value.categories.splice(index, 1);
    } else {
        form.value.categories.push(categoryId);
    }
};

// Submit form
const submitForm = async () => {
    isSaving.value = true;
    errors.value = {};
    
    try {
        const payload = { ...form.value };
        
        if (isEditMode.value) {
            await axios.put(`/api/admin/products/${productId.value}`, payload);
            Swal.fire('Success', 'Product updated successfully.', 'success');
        } else {
            await axios.post('/api/admin/products', payload);
            Swal.fire('Success', 'Product created successfully.', 'success');
        }
        router.push({ name: 'products.index' });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            Swal.fire('Validation Error', 'Please check the form for errors.', 'error');
        } else {
            Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
        }
    } finally {
        isSaving.value = false;
    }
};

onMounted(() => {
    fetchMasterData();
    if (isEditMode.value) {
        fetchProduct();
    } else {
        form.value.variants = [createDefaultVariant()];
    }
});

const productTypes = [
    { value: 'stitched', label: 'Stitched' },
    { value: 'unstitched', label: 'Unstitched' },
];

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'pos_only', label: 'POS Only' },
    { value: 'online_only', label: 'Online Only' },
];

const seasons = ['Spring', 'Summer', 'Fall', 'Winter', 'All Season'];
</script>

<template>
    <AdminLayout>
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <button @click="router.push({ name: 'products.index' })" class="p-2 rounded-lg hover:bg-gray-100">
                        <ArrowLeft class="w-5 h-5" />
                    </button>
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ isEditMode ? 'Edit Product' : 'Create Product' }}</h1>
                        <p class="text-sm text-gray-500">{{ isEditMode ? 'Update product details' : 'Add a new product to your store' }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <SecondaryButton @click="router.push({ name: 'products.index' })">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitForm" :disabled="isSaving">
                        {{ isSaving ? 'Saving...' : (isEditMode ? 'Update Product' : 'Create Product') }}
                    </PrimaryButton>
                </div>
            </div>

            <div v-if="isLoading" class="text-center py-12">Loading...</div>

            <form v-else @submit.prevent="submitForm" class="space-y-6">
                <!-- Section 1: Basic Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Package class="w-5 h-5 text-indigo-600" />
                        <h2 class="text-lg font-medium">Basic Information</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <InputLabel for="name" value="Product Name *" />
                            <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>
                        <div>
                            <InputLabel for="slug" value="Slug" />
                            <TextInput id="slug" v-model="form.slug" class="mt-1 w-full font-mono text-sm" />
                        </div>
                        <div>
                            <InputLabel for="product_type" value="Product Type" />
                            <SelectInput id="product_type" v-model="form.product_type" class="mt-1 w-full">
                                <option v-for="pt in productTypes" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
                            </SelectInput>
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="short_description" value="Short Description" />
                            <textarea id="short_description" v-model="form.short_description" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="description" value="Full Description" />
                            <textarea id="description" v-model="form.description" rows="4" class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <InputLabel for="fabric_type" value="Fabric Type" />
                            <TextInput id="fabric_type" v-model="form.fabric_type" class="mt-1 w-full" placeholder="e.g., Cotton, Silk" />
                        </div>
                        <div>
                            <InputLabel for="season" value="Season" />
                            <SelectInput id="season" v-model="form.season" class="mt-1 w-full">
                                <option value="">Select Season</option>
                                <option v-for="s in seasons" :key="s" :value="s">{{ s }}</option>
                            </SelectInput>
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="care_instructions" value="Care Instructions" />
                            <textarea id="care_instructions" v-model="form.care_instructions" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" />
                            <SelectInput id="status" v-model="form.status" class="mt-1 w-full">
                                <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </SelectInput>
                        </div>
                        <div class="flex items-center gap-6 pt-6">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600" />
                                <span>Active</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_featured" class="rounded border-gray-300 text-indigo-600" />
                                <span>Featured</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_bestseller" class="rounded border-gray-300 text-indigo-600" />
                                <span>Bestseller</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Categories & Brand -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Tag class="w-5 h-5 text-indigo-600" />
                        <h2 class="text-lg font-medium">Categories & Brand</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Categories (select multiple)" />
                            <div class="mt-2 flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                                <button v-for="cat in categories" :key="cat.id" type="button" @click="toggleCategory(cat.id)"
                                    :class="form.categories.includes(cat.id) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                                    {{ cat.name }}
                                </button>
                                <p v-if="categories.length === 0" class="text-sm text-gray-500">No categories available</p>
                            </div>
                        </div>
                        <div>
                            <InputLabel for="brand_id" value="Brand" />
                            <SelectInput id="brand_id" v-model="form.brand_id" class="mt-1 w-full">
                                <option :value="null">Select Brand</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </SelectInput>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Variants -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Layers class="w-5 h-5 text-indigo-600" />
                            <h2 class="text-lg font-medium">Product Variants</h2>
                        </div>
                        <PrimaryButton type="button" @click="addVariant" class="!py-1.5 !px-3 text-sm">
                            <Plus class="w-4 h-4 mr-1" /> Add Variant
                        </PrimaryButton>
                    </div>

                    <div v-for="(variant, index) in form.variants" :key="index" class="rounded-lg p-4 mb-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-gray-700">Variant {{ index + 1 }}</h3>
                            <button v-if="form.variants.length > 1" type="button" @click="removeVariant(index)" class="text-red-600 hover:text-red-800 p-1">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <InputLabel value="SKU *" />
                                <TextInput v-model="variant.sku" class="mt-1 w-full font-mono text-sm" placeholder="Auto-generate" />
                            </div>
                            <div>
                                <InputLabel value="Barcode" />
                                <TextInput v-model="variant.barcode" class="mt-1 w-full font-mono text-sm" />
                            </div>
                            <div>
                                <InputLabel value="Size" />
                                <SelectInput v-model="variant.size_id" class="mt-1 w-full">
                                    <option :value="null">Select</option>
                                    <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </SelectInput>
                            </div>
                            <div>
                                <InputLabel value="Color" />
                                <SelectInput v-model="variant.color_id" class="mt-1 w-full">
                                    <option :value="null">Select</option>
                                    <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </SelectInput>
                            </div>
                            <div>
                                <InputLabel value="Fabric" />
                                <SelectInput v-model="variant.fabric_id" class="mt-1 w-full">
                                    <option :value="null">Select</option>
                                    <option v-for="f in fabrics" :key="f.id" :value="f.id">{{ f.name }}</option>
                                </SelectInput>
                            </div>
                            <div>
                                <InputLabel value="Fit" />
                                <SelectInput v-model="variant.fit_id" class="mt-1 w-full">
                                    <option :value="null">Select</option>
                                    <option v-for="f in fits" :key="f.id" :value="f.id">{{ f.name }}</option>
                                </SelectInput>
                            </div>
                            <div>
                                <InputLabel value="Tax Class" />
                                <SelectInput v-model="variant.tax_class_id" class="mt-1 w-full">
                                    <option :value="null">None</option>
                                    <option v-for="tc in taxClasses" :key="tc.id" :value="tc.id">{{ tc.name }} ({{ tc.rate }}%)</option>
                                </SelectInput>
                            </div>
                            <div>
                                <InputLabel value="Stock Qty" />
                                <TextInput v-model="variant.stock_quantity" type="number" min="0" class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel value="Cost Price" />
                                <TextInput v-model="variant.cost_price" type="number" step="0.01" min="0" class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel value="Retail Price *" />
                                <TextInput v-model="variant.retail_price" type="number" step="0.01" min="0" class="mt-1 w-full" required />
                            </div>
                            <div>
                                <InputLabel value="Sale Price" />
                                <TextInput v-model="variant.sale_price" type="number" step="0.01" min="0" class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel value="Low Stock Alert" />
                                <TextInput v-model="variant.low_stock_threshold" type="number" min="0" class="mt-1 w-full" />
                            </div>
                        </div>
                        <div class="flex gap-6 mt-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="variant.is_online" class="rounded border-gray-300 text-indigo-600" />
                                <span>Available Online</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="variant.is_pos" class="rounded border-gray-300 text-indigo-600" />
                                <span>Available in POS</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="variant.is_active" class="rounded border-gray-300 text-indigo-600" />
                                <span>Active</span>
                            </label>
                        </div>
                        <!-- Variant Images -->
                        <div class="mt-4 border-t pt-4">
                            <InputLabel value="Variant Images" />
                            <div class="mt-2 flex flex-wrap gap-3">
                                <div v-for="(img, imgIndex) in variant.images" :key="imgIndex" class="relative group">
                                    <img :src="img" class="w-20 h-20 object-cover rounded-lg border" />
                                    <button type="button" @click="variant.images.splice(imgIndex, 1)" 
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                        ×
                                    </button>
                                </div>
                                <div class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center">
                                    <input type="text" :id="`img-input-${index}`" placeholder="URL"
                                        @keydown.enter.prevent="(e) => { if(e.target.value) { variant.images.push(e.target.value); e.target.value = ''; } }"
                                        class="w-full h-full text-xs text-center border-0 focus:ring-0 p-1" />
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Press Enter to add image URL</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: SEO (at last) -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <FileText class="w-5 h-5 text-indigo-600" />
                        <h2 class="text-lg font-medium">SEO Settings</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <InputLabel for="meta_title" value="Meta Title" />
                            <TextInput id="meta_title" v-model="form.meta_title" class="mt-1 w-full" maxlength="70" />
                            <p class="mt-1 text-xs text-gray-500">{{ form.meta_title?.length || 0 }}/70 characters</p>
                        </div>
                        <div>
                            <InputLabel for="meta_description" value="Meta Description" />
                            <textarea id="meta_description" v-model="form.meta_description" rows="2" maxlength="170" class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ form.meta_description?.length || 0 }}/170 characters</p>
                        </div>
                        <div>
                            <InputLabel for="meta_keywords" value="Meta Keywords" />
                            <TextInput id="meta_keywords" v-model="form.meta_keywords" class="mt-1 w-full" placeholder="keyword1, keyword2, keyword3" />
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons (Bottom) -->
                <div class="flex justify-end gap-3 pb-6">
                    <SecondaryButton type="button" @click="router.push({ name: 'products.index' })">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="isSaving">
                        {{ isSaving ? 'Saving...' : (isEditMode ? 'Update Product' : 'Create Product') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
