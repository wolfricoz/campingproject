<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";

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
    <Head title="Welcome" />
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister"  >
        <div class="relative h-96 w-full">
            <img src="/images/header.jpg" alt="Camping vista"
                 class="absolute inset-0 w-full h-full object-cover object-center" />
            <div class="relative z-10 flex justify-left h-full p-10">
                <div
                    class="flex flex-col justify-between w-64 rounded-xl bg-emerald-500/80 backdrop-blur-sm px-6 py-4 text-center shadow-lg ring-1 ring-white/20 transition hover:bg-emerald-600 hover:shadow-xl cursor-pointer">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Boek je vakantie!</h3>
                        <p class="mt-1 text-sm text-white/80">Vind jouw perfecte plek</p>
                    </div>
                    <Link class="general-button" :href="route('booking')">
                        Reserveer nu!
                    </Link>
                </div>

            </div>
        </div>
    </GuestLayout>

</template>
