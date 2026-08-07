<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import LocaleToggle from '@/Components/LocaleToggle.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="h-screen overflow-hidden bg-gray-100">
            <div class="flex justi">

            <nav
                class="border-b border-gray-100 bg-white rounded-r-xl"
            >
                <!-- Primary Navigation Menu -->
                <div class="h-screen overflow-hidden w-64">
                    <div class="flex flex-col h-full justify-between">

                        <div class="flex-col">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center bg-blue-500 gap-2 rounded-r-xl">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-200"
                                    />
                                </Link>
                                <h1 class="nav-button">Syntec Camping</h1>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="flex-col shrink-0 items-center mt-2 gap-0.5"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    {{ __('Dashboard') }}
                                </NavLink>

                                <NavLink
                                    :href="route('arrangement.index')"
                                    :active="route().current('arrangement.index') && !route().params.status"
                                >
                                    {{ __('Alle reserveringen') }}
                                </NavLink>
                                <NavLink
                                    :href="route('arrangement.index', 'pending')"
                                    :active="route().current('arrangement.index') && route().params.status === 'pending'"
                                >
                                    {{ __('Reserveringen in behandeling') }}
                                </NavLink>
                                <NavLink
                                    :href="route('locations.index')"
                                    :active="route().current('locations.index') && !route().params.status"
                                >
                                    {{ __('Locaties') }}
                                </NavLink>

                            </div>
                        </div>

                        <div class="hidden  sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="left" width="48" drop-up>
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('home')">
                                            {{ __('Naar de website') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            {{ __('Profiel') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ __('Uitloggen') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <LocaleToggle class="ms-3 text-gray-500"/>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            {{ __('Dashboard') }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                {{ __('Profiel') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                {{ __('Uitloggen') }}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>


            <!-- Page Content -->
            <main class="flex-1 overflow-y-scroll max-h-screen">
                <slot />
            </main>
        </div>
        </div>

    </div>
</template>
