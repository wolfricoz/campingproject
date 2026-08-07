<script setup>
import {Head, Link} from '@inertiajs/vue3';
import Dropdown from "@/Components/Dropdown.vue";
import {usePage} from '@inertiajs/vue3';
import {computed} from "vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import LocaleToggle from "@/Components/LocaleToggle.vue";

defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
    },

});


const user = computed(() => usePage().props.auth.user);

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <main class="flex-1 flex flex-col">
            <!--  === Navbar ===  -->
            <div class="flex items-center justify-center px-16 py-4 w-full bg-blue-600 shadow-sm border-b border-gray-200">
                <div id="navbar" class="flex flex-row items-center gap-8 w-2/3">
                    <Link :href="route('home')" class="text-xl font-bold text-white tracking-tight">
                        Syntec Camping
                    </Link>
                    <div class="flex flex-row gap-6 ml-auto">
                        <Link :href="route('home')" class="nav-button">
                            {{ __('Home') }}
                        </Link>
                        <Link :href="route('locations')" class="nav-button">
                            {{ __('Locaties') }}
                        </Link>
                        <Link :href="route('about')" class="nav-button">
                            {{ __('Over Ons') }}
                        </Link>
                        <Link :href="route('contact')" class="nav-button">
                            {{ __('Contact') }}
                        </Link>
                        <Link v-if="canLogin" :href="route('login')" class="nav-button">
                            {{ __('Login') }}
                        </Link>
                        <Dropdown v-else align="middle" width="48">
                            <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="nav-button"
                                            >
                                                {{ user?.name }}


                                            </button>
                                        </span>
                            </template>

                            <template #content>
                                <dropdownLink :href="route('dashboard')">
                                    {{ __('Dashboard') }}
                                </dropdownLink>
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

                        <LocaleToggle class="text-gray-200"/>
                    </div>
                </div>
            </div>

            <!--    === Content Slot ===    -->
            <div class="flex-1">
                <slot></slot>
            </div>
        </main>

        <!--  === Footer ===  -->
        <footer class="flex flex-col items-center justify-center gap-1 px-16 py-6 w-full bg-gray-900 text-gray-300 shadow-sm">
            <span class="text-base font-semibold text-white tracking-tight">
                Syntec Camping
            </span>
            <div class="flex flex-row flex-wrap items-center justify-center gap-3 text-sm text-gray-400">
                <span>KVK: 1234567</span>
                <span class="text-gray-600">•</span>
                <span>VAT: 1234567</span>
                <span class="text-gray-600">•</span>
                <span>{{ __('Locatie') }}: Florijnstraat 20 4879 AH, Etten-Leur</span>
                <span class="text-gray-600">•</span>
                <span>{{ __('E-mail') }}: Ricardo.sas@syntec-it.nl</span>
            </div>
        </footer>
    </div>
</template>
