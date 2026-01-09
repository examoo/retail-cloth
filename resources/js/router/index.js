import { createRouter, createWebHistory } from 'vue-router';
import NProgress from 'nprogress';
import '../../css/nprogress.css';
import Home from '../Pages/Website/Home.vue';
import Dashboard from '../Pages/Admin/Dashboard.vue';
import AdminLogin from '../Pages/Auth/AdminLogin.vue';
import CustomerLogin from '../Pages/Auth/CustomerLogin.vue';
import CustomerRegister from '../Pages/Auth/CustomerRegister.vue';
import ForgotPassword from '../Pages/Auth/ForgotPassword.vue';
import ResetPassword from '../Pages/Auth/ResetPassword.vue';
import UserIndex from '../Pages/Admin/Users/Index.vue';
import CategoryIndex from '../Pages/Admin/Categories/Index.vue';
import BrandIndex from '../Pages/Admin/Brands/Index.vue';
import AttributeIndex from '../Pages/Admin/Attributes/Index.vue';
import ProductIndex from '../Pages/Admin/Products/Index.vue';
import ProductForm from '../Pages/Admin/Products/Form.vue';
import SizesPage from '../Pages/Admin/Settings/Sizes.vue';
import ColorsPage from '../Pages/Admin/Settings/Colors.vue';
import FabricsPage from '../Pages/Admin/Settings/Fabrics.vue';
import FitsPage from '../Pages/Admin/Settings/Fits.vue';
import TaxClassesPage from '../Pages/Admin/Settings/TaxClasses.vue';
import StoresPage from '../Pages/Admin/Settings/Stores.vue';

// Auth state management
let isAdminAuthenticated = null;
let currentUser = null;

const checkAdminAuth = async () => {
    try {
        const response = await fetch('/api/admin/me', {
            credentials: 'include',
            headers: { Accept: 'application/json' },
        });
        if (response.ok) {
            const data = await response.json();
            isAdminAuthenticated = true;
            currentUser = { ...data.user, roles: data.roles || [], permissions: data.permissions || [] };
            return true;
        }
        isAdminAuthenticated = false;
        currentUser = null;
        return false;
    } catch {
        isAdminAuthenticated = false;
        currentUser = null;
        return false;
    }
};

const hasRole = (requiredRoles) => {
    if (!currentUser || !currentUser.roles) return false;
    const rolesToCheck = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];
    return rolesToCheck.some(role => currentUser.roles.includes(role));
};

const hasPermission = (permission) => {
    if (!currentUser || !currentUser.permissions) return false;
    return currentUser.permissions.includes(permission);
};

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/login', name: 'customer.login', component: CustomerLogin },
    { path: '/register', name: 'customer.register', component: CustomerRegister },
    { path: '/app/login', name: 'admin.login', component: AdminLogin, meta: { guestOnly: true } },
    { path: '/app/forgot-password', name: 'admin.forgot-password', component: ForgotPassword, meta: { guestOnly: true } },
    { path: '/app/reset-password', name: 'admin.reset-password', component: ResetPassword, meta: { guestOnly: true } },
    { path: '/app', name: 'dashboard', component: Dashboard, meta: { requiresAdminAuth: true } },
    { path: '/app/users', name: 'users.index', component: UserIndex, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin'] } },

    // Product Management
    { path: '/app/products', name: 'products.index', component: ProductIndex, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/products/create', name: 'products.create', component: ProductForm, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/products/:id/edit', name: 'products.edit', component: ProductForm, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/categories', name: 'categories.index', component: CategoryIndex, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/brands', name: 'brands.index', component: BrandIndex, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/attributes', name: 'attributes.index', component: AttributeIndex, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },

    // Settings
    { path: '/app/settings/sizes', name: 'settings.sizes', component: SizesPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/settings/colors', name: 'settings.colors', component: ColorsPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/settings/fabrics', name: 'settings.fabrics', component: FabricsPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/settings/fits', name: 'settings.fits', component: FitsPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/settings/tax-classes', name: 'settings.tax-classes', component: TaxClassesPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
    { path: '/app/settings/stores', name: 'settings.stores', component: StoresPage, meta: { requiresAdminAuth: true, requiredRoles: ['super_admin', 'admin'] } },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach(async (to, from, next) => {
    NProgress.start();
    if (to.meta.requiresAdminAuth) {
        const isAuthenticated = await checkAdminAuth();
        if (!isAuthenticated) return next({ name: 'admin.login', query: { redirect: to.fullPath } });
        if (to.meta.requiredRoles && !hasRole(to.meta.requiredRoles)) return next({ name: 'dashboard' });
    }
    if (to.meta.guestOnly && to.path.includes('/app')) {
        const isAuthenticated = await checkAdminAuth();
        if (isAuthenticated) return next({ name: 'dashboard' });
    }
    next();
});

router.afterEach(() => { NProgress.done(); });

export const getCurrentUser = () => currentUser;
export const getUserRole = () => currentUser?.role;
export const userHasRole = hasRole;
export const userHasPermission = hasPermission;
export default router;
