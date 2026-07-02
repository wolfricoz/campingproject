<script setup>
import {Head, Link} from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

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
                    <h1 class="text-xl font-bold text-white tracking-tight">
                        Syntec Camping
                    </h1>
                    <div class="flex flex-row gap-6 ml-auto">
                        <Link href="https://google.com/" class="nav-button">
                            Home
                        </Link>
                        <Link href="#" class="nav-button">
                            Locaties
                        </Link>
                        <Link href="#" class="nav-button">
                            Over Ons
                        </Link>
                        <Link v-if="canLogin" :href="route('login')" class="nav-button">
                            Login
                        </Link>
                        <Link v-else class="nav-button">
                            Dashboard
                        </Link>
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
                <span>Locatie: Florijnstraat 20 4879 AH, Etten-Leur</span>
                <span class="text-gray-600">•</span>
                <span>Email: Ricardo.sas@syntec-it.nl</span>
            </div>
        </footer>
    </div>
</template>
