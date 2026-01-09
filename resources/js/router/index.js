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

// Auth state management
let isAdminAuthenticated = null;
let currentUser = null;

const checkAdminAuth = async () => {
    try {
        const response = await fetch('/api/admin/me', {
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
        });
        if (response.ok) {
            const data = await response.json();
            isAdminAuthenticated = true;
            // Merge user data with roles and permissions
            currentUser = {
                ...data.user,
                roles: data.roles || [],
                permissions: data.permissions || [],
            };
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

// Check if current user has required role
const hasRole = (requiredRoles) => {
    if (!currentUser || !currentUser.roles) return false;

    // Normalize requiredRoles to array
    const rolesToCheck = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];

    // Check if user has ANY of the required roles
    return rolesToCheck.some(role => currentUser.roles.includes(role));
};

// Check if current user has required permission
const hasPermission = (permission) => {
    if (!currentUser || !currentUser.permissions) return false;
    return currentUser.permissions.includes(permission);
};

const routes = [
    // Public Website Routes
    {
        path: '/',
        name: 'home',
        component: Home,
    },

    // Customer Auth Routes
    {
        path: '/login',
        name: 'customer.login',
        component: CustomerLogin,
    },
    {
        path: '/register',
        name: 'customer.register',
        component: CustomerRegister,
    },

    // Admin Auth Routes
    {
        path: '/app/login',
        name: 'admin.login',
        component: AdminLogin,
        meta: { guestOnly: true },
    },
    {
        path: '/app/forgot-password',
        name: 'admin.forgot-password',
        component: ForgotPassword,
        meta: { guestOnly: true },
    },
    {
        path: '/app/reset-password',
        name: 'admin.reset-password',
        component: ResetPassword,
        meta: { guestOnly: true },
    },

    // Admin Protected Routes
    {
        path: '/app',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAdminAuth: true },
    },

    {
        path: '/app/users',
        name: 'users.index',
        component: UserIndex,
        meta: {
            requiresAdminAuth: true,
            requiredRoles: ['super_admin']
        },
    },

    // Product Management Routes
    {
        path: '/app/categories',
        name: 'categories.index',
        component: CategoryIndex,
        meta: {
            requiresAdminAuth: true,
            requiredRoles: ['super_admin', 'admin']
        },
    },
    {
        path: '/app/brands',
        name: 'brands.index',
        component: BrandIndex,
        meta: {
            requiresAdminAuth: true,
            requiredRoles: ['super_admin', 'admin']
        },
    },
    {
        path: '/app/attributes',
        name: 'attributes.index',
        component: AttributeIndex,
        meta: {
            requiresAdminAuth: true,
            requiredRoles: ['super_admin', 'admin']
        },
    },
    // Add more admin routes here - they will all be protected
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Global Navigation Guard for Admin Routes
router.beforeEach(async (to, from, next) => {
    NProgress.start();

    // Check if route requires admin authentication
    if (to.meta.requiresAdminAuth) {
        const isAuthenticated = await checkAdminAuth();

        if (!isAuthenticated) {
            // Redirect to admin login with return URL
            return next({
                name: 'admin.login',
                query: { redirect: to.fullPath },
            });
        }

        // Check for required roles
        if (to.meta.requiredRoles && !hasRole(to.meta.requiredRoles)) {
            // User doesn't have required role, redirect to dashboard with error
            return next({ name: 'dashboard' });
        }
    }

    // If already logged in admin tries to access guest-only pages, redirect to dashboard
    if (to.meta.guestOnly && to.path.includes('/app')) {
        const isAuthenticated = await checkAdminAuth();

        if (isAuthenticated) {
            return next({ name: 'dashboard' });
        }
    }

    next();
});

router.afterEach(() => {
    NProgress.done();
});

// Export helper for components to access current user
export const getCurrentUser = () => currentUser;
export const getUserRole = () => currentUser?.role;
export const userHasRole = hasRole;
export const userHasPermission = hasPermission;

export default router;



