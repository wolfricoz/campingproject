<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    console.log('Booking submitted:', form.value);
}


</script>

<template>
    <Head title="Welcome" />
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">
        <!-- Hero banner -->
<!--        <div class="relative h-96 w-full">-->
<!--            <img src="/images/frontpage_header.jpg" alt="Camping"-->
<!--                 class="absolute inset-0 h-full w-full object-cover object-center" />-->
<!--            <div class="absolute inset-0 bg-black/30"></div>-->
<!--            <div class="relative z-10 flex h-full flex-col items-center justify-center text-center px-4">-->
<!--                <h1 class="text-4xl font-bold text-white sm:text-5xl">Boek je vakantie!</h1>-->
<!--                <p class="mt-2 text-lg text-white/90">Vind jouw perfecte plek in de natuur</p>-->
<!--            </div>-->
<!--        </div>-->

        <!-- Booking form -->
        <div class="mx-auto max-w-3xl px-4 py-10">
            <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                <h2 class="text-xl font-semibold text-gray-800">Reservering aanvragen</h2>
                <p class="mt-1 text-sm text-gray-500">Vul je gegevens in en kies een locatie.</p>

                <div class="mt-6 space-y-6">
                    <!-- Location + dates -->
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
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Vertrek</label>
                            <input type="date" v-model="form.end_date"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <!-- Customer contact -->
                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="mb-3 text-sm font-semibold text-gray-800">Jouw gegevens</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Naam</label>
                                <input type="text" v-model="form.customer.name" placeholder="Volledige naam"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email" v-model="form.customer.email" placeholder="naam@voorbeeld.nl"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Telefoonnummer</label>
                                <input type="tel" v-model="form.customer.phone_number" placeholder="06 12345678"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Customer address -->
                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="mb-3 text-sm font-semibold text-gray-800">Adres</h3>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
                            <input type="text" v-model="form.customer.street_name" placeholder="Straat"
                                   class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3" />
                            <input type="text" v-model="form.customer.street_number" placeholder="Nr."
                                   class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-1" />
                            <input type="text" v-model="form.customer.postal_code" placeholder="Postcode"
                                   class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-2" />
                            <input type="text" v-model="form.customer.city" placeholder="Plaats"
                                   class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3" />
                            <input type="text" v-model="form.customer.country" placeholder="Land"
                                   class="col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:col-span-3" />
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="border-t border-gray-100 pt-6">
                        <button type="button" @click="submit"
                                class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                            Reservering aanvragen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
