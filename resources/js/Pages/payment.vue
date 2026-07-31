<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ref, watch} from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    paid: {
        type: Boolean,
    },
    guid: {
        type: String
    }
});

const form = useForm(
    'POST',
    route('payment.complete'),
    {
        guid: props.guid,
    }
);

function submit() {
    form.post(route('payment.complete'));

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

                    <div v-if="!paid" class="border-t border-gray-100 pt-6">
                        <Link type="button" @click="submit"
                              class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                            Betaal
                        </Link>
                    </div>
                    <div v-else class="border-t border-gray-100 pt-6">
                        <div class="p-10 w-full bg-orange-100 rounded-xl border-2 border-dashed border-orange-400 text-center">
                            De booking is al betaald!<br>
                            <Link :href="route('dashboard')" class="href-class">Ga terug naar Dashboard</Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </GuestLayout>
</template>
