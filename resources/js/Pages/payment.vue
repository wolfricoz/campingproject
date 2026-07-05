<script setup>
import {Head, Link} from '@inertiajs/vue3';
import {ref, watch} from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineProps({
    locations: {
        type: Object,
    },
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});


const form = ref({
    location_id: null,
    start_date: '',
    end_date: '',
    customer: {
        id: 0,
        name: '',
        email: '',
        phone_number: '',
        street_name: '',
        street_number: '',
        postal_code: '',
        city: '',
        country: '',
    },
});

function submit() {
    // Wire this up yourself — form.value holds everything
}


</script>

<template>
    <Head title="Welcome"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-[url(/images/header.jpg)]  px-4 py-10 bg-cover bg-center h-full">
            <div class="max-w-3xl mx-auto ">

                <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                    <h1 class="text-2xl font-semibold text-gray-800">
                        Payment Placeholder
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Deze pagina is om de betaling te simuleren, in productie zal hier een payment provider voor worden gebruikt.
                    </p>

                        <div class="border-t border-gray-100 pt-6">
                            <button type="button" @click="submit"
                                    class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                                Betaal
                            </button>
                        </div>
                    </div>
                </div>

            </div>
    </GuestLayout>
</template>
