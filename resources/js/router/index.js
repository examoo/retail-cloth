import { createRouter, createWebHistory } from 'vue-router';
import Home from '../Pages/Website/Home.vue';
import Dashboard from '../Pages/Admin/Dashboard.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
    },
    {
        path: '/app',
        name: 'dashboard',
        component: Dashboard,
    },
    // Add more admin routes here as children or separate paths
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
