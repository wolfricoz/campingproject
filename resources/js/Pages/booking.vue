<script setup>
import {Head, Link} from '@inertiajs/vue3';
import {ref, watch} from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";

const props = defineProps({
    customer: {
        type: Object,
    },
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
    id: 0,
    location_id: null,
    customer_id: null,
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
        create_account: true
    },
});

watch(
    () => props.customer,
    (customer) => {
        if (!customer) return;
        form.value.customer_id = customer.id ?? null;
        form.value.customer = {
            id: customer.id ?? 0,
            name: customer.name ?? '',
            email: customer.email ?? '',
            phone_number: customer.phone_number ?? '',
            street_name: customer.street_name ?? '',
            street_number: customer.street_number ?? '',
            postal_code: customer.postal_code ?? '',
            city: customer.city ?? '',
            country: customer.country ?? '',
            create_account: !customer.user_id, // already has account → don't re-create
        };
    },
    { immediate: true }
);


async function submit() {
    try {
        const customerRes = await axios.post(route('api.customers.store'), form.value);

        const customerId = customerRes?.data?.updated_data?.id;
        if (!customerId) {
            console.error('No customer id returned', customerRes);
            return; // stop — don't create an arrangement without a customer
        }

        form.value.customer_id = customerId;
        form.value.customer.id = customerId;

        const arrangementRes = await axios.post(route('api.arrangements.store'), form.value);
        form.value.id = arrangementRes.data.updated_data.id;
        console.log(arrangementRes);
        window.location.href = route('payment', arrangementRes.data.updated_data.guid);
    } catch (error) {
        console.log(error);
    }
}


</script>

<template>
    <Head title="Welcome"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-[url(/images/header.jpg)]  px-4 py-10 bg-cover bg-center">
            <div class="max-w-3xl mx-auto ">

                <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                    <h2 class="text-xl font-semibold text-gray-800">Reservering aanvragen</h2>
                    <p class="mt-1 text-sm text-gray-500">Vul je gegevens in en kies een locatie.</p>

                    <div class="mt-6 space-y-6">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-3">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Locatie</label>
                                <select v-model="form.location_id"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option :value="null">— Selecteer een locatie —</option>
                                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Aankomst</label>
                                <input type="date" v-model="form.start_date"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Vertrek</label>
                                <input type="date" v-model="form.end_date"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            </div>
                        </div>


                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">Jouw gegevens</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Naam</label>
                                    <input type="text" v-model="form.customer.name" placeholder="Volledige naam"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">E-mail</label>
                                    <input type="email" v-model="form.customer.email" placeholder="naam@voorbeeld.nl"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Telefoonnummer</label>
                                    <input type="tel" v-model="form.customer.phone_number" placeholder="06 12345678"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">Adres</h3>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
                                <input type="text" v-model="form.customer.street_name" placeholder="Straat"
                                       class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3"/>
                                <input type="text" v-model="form.customer.street_number" placeholder="Nr."
                                       class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-1"/>
                                <input type="text" v-model="form.customer.postal_code" placeholder="Postcode"
                                       class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-2"/>
                                <input type="text" v-model="form.customer.city" placeholder="Plaats"
                                       class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3"/>
                                <input type="text" v-model="form.customer.country" placeholder="Land"
                                       class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3"/>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <button type="button" @click="submit"
                                    class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                                Reservering aanvragen
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>
