<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import SidebarLink from '../Components/Admin/SidebarLink.vue';
import { Menu } from 'lucide-vue-next';

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};
</script>

<template>
    <div class="h-screen flex overflow-hidden bg-gray-100">
        <!-- Sidebar -->
        <aside 
            class="hidden md:flex flex-col flex-shrink-0 bg-slate-900 border-r border-gray-200 transition-all duration-300 ease-in-out"
            :class="isSidebarCollapsed ? 'w-20' : 'w-64'"
        >
            <div class="h-16 flex items-center justify-center bg-slate-900 shadow-md z-10 overflow-hidden">
                <span v-if="!isSidebarCollapsed" class="text-xl font-bold text-white tracking-wider whitespace-nowrap">RETAIL<span class="text-indigo-500">CLOTH</span></span>
                <span v-else class="text-xl font-bold text-indigo-500">RC</span>
            </div>
            
            <div class="flex-1 flex flex-col overflow-y-auto pt-5 pb-4">
                <nav class="flex-1 px-2 space-y-1 bg-slate-900">
                    <SidebarLink to="/app" icon="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" :collapsed="isSidebarCollapsed">
                        Dashboard
                    </SidebarLink>
                    <SidebarLink to="/app/products" icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" :collapsed="isSidebarCollapsed">
                        Products
                    </SidebarLink>
                    <SidebarLink to="/app/orders" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" :collapsed="isSidebarCollapsed">
                        Orders
                    </SidebarLink>
                </nav>
            </div>

            <div class="flex-shrink-0 flex border-t border-slate-800 p-4 overflow-hidden">
               <RouterLink to="/" class="flex-shrink-0 w-full group block">
                    <div class="flex items-center" :class="isSidebarCollapsed ? 'justify-center' : ''">
                         <div>
                            <div class="inline-block h-9 w-9 rounded-full bg-slate-700 flex items-center justify-center text-white text-xs">
                                RC
                            </div>
                        </div>
                        <div class="ml-3" v-if="!isSidebarCollapsed">
                            <p class="text-sm font-medium text-white group-hover:text-gray-300">
                                Back to Website
                            </p>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
             <!-- Topbar -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10">
                 <div class="flex-1 flex justify-between items-center">
                     <button @click="toggleSidebar" class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 mr-4">
                         <span class="sr-only">Open sidebar</span>
                         <Menu class="h-6 w-6" />
                     </button>
                     <div class="flex-1 flex" /> <!-- Spacer/Search -->
                     <div class="ml-4 flex items-center md:ml-6">
                        <button class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                             <span class="sr-only">View notifications</span>
                             <!-- Bell Icon -->
                             <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>
                        
                        <!-- Profile Dropdown (Simplified) -->
                        <div class="ml-3 relative">
                            <div>
                                <button class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                </button>
                            </div>
                        </div>
                     </div>
                 </div>
            </header>

            <!-- Main Scroll Area -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-full mx-auto px-4 sm:px-6 md:px-8">
                         <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
