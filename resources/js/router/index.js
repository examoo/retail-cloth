import { createRouter, createWebHistory } from 'vue-router';
import Home from '../Pages/Website/Home.vue';
import Dashboard from '../Pages/Admin/Dashboard.vue';
import AdminLogin from '../Pages/Auth/AdminLogin.vue';
import CustomerLogin from '../Pages/Auth/CustomerLogin.vue';
import CustomerRegister from '../Pages/Auth/CustomerRegister.vue';
import ForgotPassword from '../Pages/Auth/ForgotPassword.vue';
import ResetPassword from '../Pages/Auth/ResetPassword.vue';

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
            currentUser = data.user;
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
const hasRole = (roles) => {
    if (!currentUser || !currentUser.role) return false;
    if (typeof roles === 'string') return currentUser.role === roles;
    return roles.includes(currentUser.role);
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
    // Add more admin routes here - they will all be protected
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Global Navigation Guard for Admin Routes
router.beforeEach(async (to, from, next) => {
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

// Export helper for components to access current user
export const getCurrentUser = () => currentUser;
export const getUserRole = () => currentUser?.role;
export const userHasRole = hasRole;

export default router;



