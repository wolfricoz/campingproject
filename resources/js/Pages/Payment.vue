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

// Not everybody can pay with iDeal, so the customer picks how they want to pay
const paymentMethods = [
    {value: 'ideal', label: 'iDeal'},
    {value: 'bank_transfer', label: 'Bankoverschrijving'},
];

const form = useForm(
    'POST',
    route('payment.complete'),
    {
        guid: props.guid,
        payment_method: paymentMethods[0].value,
    }
);

function submit() {
    form.post(route('payment.complete'));

}


</script>

<template>
    <Head :title="__('Betalen')"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-[url(/images/header.jpg)]  px-4 py-10 bg-cover bg-center h-full">
            <div class="max-w-3xl mx-auto ">

                <div class="card-base">
                    <h1 class="text-2xl font-semibold text-gray-800">
                        {{ __('Payment Placeholder') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Deze pagina is om de betaling te simuleren, in productie zal hier een payment provider voor worden gebruikt.') }}
                    </p>

                    <div v-if="!paid" class="border-t border-gray-100 pt-6">
                        <h2 class="text-sm font-semibold text-gray-800">{{ __('Betaalmethode') }}</h2>
                        <div class="mt-3 space-y-2">
                            <label v-for="method in paymentMethods" :key="method.value"
                                   class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 text-sm text-gray-700 transition-colors"
                                   :class="form.payment_method === method.value
                                       ? 'border-emerald-500 bg-emerald-50'
                                       : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="payment_method"
                                       v-model="form.payment_method" :value="method.value"
                                       class="border-gray-300 text-emerald-600 focus:ring-emerald-500"/>
                                {{ __(method.label) }}
                            </label>
                        </div>
                        <p v-if="form.errors.payment_method" class="error-base">{{ form.errors.payment_method }}</p>

                        <Link type="button" @click="submit"
                              class="mt-6 block w-full rounded-lg bg-emerald-600 px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-emerald-700 sm:inline-block sm:w-auto">
                            {{ __('Betaal') }}
                        </Link>
                    </div>
                    <div v-else class="border-t border-gray-100 pt-6">
                        <div class="p-10 w-full bg-orange-100 rounded-xl border-2 border-dashed border-orange-400 text-center">
                            {{ __('De booking is al betaald!') }}<br>
                            <Link :href="route('dashboard')" class="href-class">{{ __('Ga terug naar Dashboard') }}</Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </GuestLayout>
</template>
