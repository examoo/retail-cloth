<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import SidebarLink from '../Components/Admin/SidebarLink.vue';
import SidebarDropdown from '../Components/Admin/SidebarDropdown.vue';
import { Menu } from 'lucide-vue-next';
import { userHasPermission } from '../router';

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
                    <SidebarLink v-if="userHasPermission('view-dashboard')" to="/app" icon="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" :collapsed="isSidebarCollapsed">
                        Dashboard
                    </SidebarLink>

                    <!-- Product Management Dropdown -->
                    <SidebarDropdown
                        v-if="userHasPermission('view-inventory')"
                        icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        label="Product Management"
                        :collapsed="isSidebarCollapsed"
                    >
                        <SidebarLink to="/app/products" icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" :collapsed="isSidebarCollapsed">
                            Products
                        </SidebarLink>
                        <SidebarLink to="/app/categories" icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" :collapsed="isSidebarCollapsed">
                            Categories
                        </SidebarLink>
                        <SidebarLink to="/app/brands" icon="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" :collapsed="isSidebarCollapsed">
                            Brands
                        </SidebarLink>
                        <SidebarLink to="/app/attributes" icon="M4 6h16M4 12h8m-8 6h16" :collapsed="isSidebarCollapsed">
                            Attributes
                        </SidebarLink>
                    </SidebarDropdown>

                    <SidebarLink 
                        v-if="userHasPermission('view-users')"
                        to="/app/users" 
                        icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" 
                        :collapsed="isSidebarCollapsed"
                    >
                        Users
                    </SidebarLink>
                    <SidebarLink v-if="userHasPermission('view-orders')" to="/app/orders" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" :collapsed="isSidebarCollapsed">
                        Orders
                    </SidebarLink>

                    <!-- Settings Dropdown -->
                    <SidebarDropdown
                        v-if="userHasPermission('view-inventory')"
                        icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        label="Settings"
                        :collapsed="isSidebarCollapsed"
                    >
                        <SidebarLink to="/app/settings/sizes" icon="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" :collapsed="isSidebarCollapsed">
                            Sizes
                        </SidebarLink>
                        <SidebarLink to="/app/settings/colors" icon="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z" :collapsed="isSidebarCollapsed">
                            Colors
                        </SidebarLink>
                        <SidebarLink to="/app/settings/fabrics" icon="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547" :collapsed="isSidebarCollapsed">
                            Fabrics
                        </SidebarLink>
                        <SidebarLink to="/app/settings/fits" icon="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" :collapsed="isSidebarCollapsed">
                            Fits
                        </SidebarLink>
                        <SidebarLink to="/app/settings/tax-classes" icon="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" :collapsed="isSidebarCollapsed">
                            Tax Classes
                        </SidebarLink>
                        <SidebarLink to="/app/settings/stores" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" :collapsed="isSidebarCollapsed">
                            Stores
                        </SidebarLink>
                    </SidebarDropdown>
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
