<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";

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


const form = useForm({
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
    terms_accepted: false
});

watch(
    () => props.customer,
    (customer) => {
        if (!customer) return;
        form.customer_id = customer.id ?? null;
        form.customer = {
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


function splitDate(value) {
    if (!value) return {date: '', time: ''};
    const [date, time = ''] = value.split('T');
    return {date, time: time.slice(0, 5)}; // HH:mm
}

const startDatePart = computed({
    get: () => splitDate(form.start_date).date,
    set: (val) => {
        const {time} = splitDate(form.start_date);
        form.start_date = val ? `${val}T${time || '00:00'}` : '';
    }
});
const startTimePart = computed({
    get: () => splitDate(form.start_date).time,
    set: (val) => {
        const {date} = splitDate(form.start_date);
        form.start_date = date ? `${date}T${val || '00:00'}` : '';
    }
});

const endDatePart = computed({
    get: () => splitDate(form.end_date).date,
    set: (val) => {
        const {time} = splitDate(form.end_date);
        form.end_date = val ? `${val}T${time || '00:00'}` : '';
    }
});
const endTimePart = computed({
    get: () => splitDate(form.end_date).time,
    set: (val) => {
        const {date} = splitDate(form.end_date);
        form.end_date = date ? `${date}T${val || '00:00'}` : '';
    }
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

// De einddatum mag nooit voor de startdatum liggen, dus we schuiven hem mee.
watch(() => form.start_date, () => {
    if (form.end_date && form.end_date < form.start_date) {
        form.end_date = form.start_date;
    }
});

// === Nachten & prijs ===
const days = ref(0);
const price = ref(0);

function fetchDays() {
    if (!form.start_date || !form.end_date) {
        days.value = 0;
        return;
    }
    axios.get(route('api.calculations.days'), {
        params: {start_date: form.start_date, end_date: form.end_date},
    }).then(response => {
        days.value = response.data.days;
    }).catch(error => {
        days.value = 0;
        console.log(error);
    });
}

function fetchPrice() {
    if (!form.location_id || !days.value) {
        price.value = 0;
        return;
    }
    axios.get(route('api.calculations.price'), {
        params: {location_id: form.location_id, days: days.value},
    }).then(response => {
        price.value = response.data.price;
    }).catch(error => {
        price.value = 0;
        console.log(error);
    });
}

watch(() => [form.start_date, form.end_date], fetchDays, {immediate: true});
watch(() => [form.location_id, days.value], fetchPrice);

function submit() {
    form.post(route('booking.store'));

    // try {
    //     const customerRes = await axios.post(route('api.customers.store'), form.value);
    //
    //     const customerId = customerRes?.data?.updated_data?.id;
    //     if (!customerId) {
    //         console.error('No customer id returned', customerRes);
    //         return; // stop — don't create an arrangement without a customer
    //     }
    //
    //     form.value.customer_id = customerId;
    //     form.value.customer.id = customerId;
    //
    //     const arrangementRes = await axios.post(route('api.arrangements.store'), form.value);
    //     form.value.id = arrangementRes.data.updated_data.id;
    //     console.log(arrangementRes);
    //     window.location.href = route('payment', arrangementRes.data.updated_data.guid);
    // } catch (error) {
    //     console.log(error);
    // }
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

                    <!-- Algemene foutmelding wanneer er validatiefouten zijn -->
                    <div v-if="hasErrors"
                         class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-medium">Er ging iets mis. Controleer de gemarkeerde velden:</p>
                        <ul class="mt-1 list-inside list-disc">
                            <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
                        </ul>
                    </div>

                    <div class="mt-6 space-y-6">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-3">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Locatie <span class="text-red-600">*</span></label>
                                <select v-model="form.location_id"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option :value="null">— Selecteer een locatie —</option>
                                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                                <p v-if="form.errors.location_id" class="mt-1 text-xs text-red-600">{{ form.errors.location_id }}</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Aankomst <span class="text-red-600">*</span></label>
                                <div class="flex gap-2">
                                    <input type="date" v-model="startDatePart"
                                           class="flex-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <input type="time" v-model="startTimePart" step="60"
                                           class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                </div>
                                <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Vertrek <span class="text-red-600">*</span></label>
                                <div class="flex gap-2">
                                    <input type="date" v-model="endDatePart" :min="startDatePart"
                                           class="flex-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <input type="time" v-model="endTimePart" step="60"
                                           class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                </div>
                                <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Berekende nachten en prijs -->
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ days }} {{ days === 1 ? 'nacht' : 'nachten' }}</span>
                            <span class="font-semibold text-emerald-700">€ {{ price }}</span>
                        </div>


                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">Jouw gegevens</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Naam <span class="text-red-600">*</span></label>
                                    <input type="text" v-model="form.customer.name" placeholder="Volledige naam"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.name']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.name'] }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">E-mail <span class="text-red-600">*</span></label>
                                    <input type="email" v-model="form.customer.email" placeholder="naam@voorbeeld.nl"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.email']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.email'] }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Telefoonnummer</label>
                                    <input type="tel" v-model="form.customer.phone_number" placeholder="06 12345678"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.phone_number']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.phone_number'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">Adres</h3>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.street_name" placeholder="Straat"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.street_name']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.street_name'] }}</p>
                                </div>
                                <div class="sm:col-span-1">
                                    <input type="text" v-model="form.customer.street_number" placeholder="Nr."
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.street_number']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.street_number'] }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="text" v-model="form.customer.postal_code" placeholder="Postcode"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.postal_code']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.postal_code'] }}</p>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.city" placeholder="Plaats"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.city']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.city'] }}</p>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.country" placeholder="Land"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                                    <p v-if="form.errors['customer.country']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.country'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Account Aanmaken optie -->
                        <div class="flex-col gap-2  border-t border-gray-100 pt-6">
                            <div class="flex text-xs text-gray-800 gap-2">
                                <p>Maak een account aan en bekijk je boeking wanneer je maar wilt.</p>
                                <checkbox :checked="false" v-model="form.customer.create_account">

                                </checkbox>
                            </div>
                            <div class="flex text-xs text-gray-800 gap-2">

                                <p>Door je account aan te maken ga je akkoord met onze <Link class="href-class" href="/voorwaarden">algemene
                                    voorwaarden</Link> en
                                    <Link class="href-class" href="/privacy">privacyverklaring</Link>. <span class="text-red-600">*</span></p>
                                <checkbox :checked="false" v-model="form.terms_accepted">

                                </checkbox>
                            </div>
                            <p v-if="form.errors.terms_accepted" class="mt-1 text-xs text-red-600">{{ form.errors.terms_accepted }}</p>
                            <p v-if="form.errors['customer.create_account']" class="mt-1 text-xs text-red-600">{{ form.errors['customer.create_account'] }}</p>
                            <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>

                        </div>


                        <div class="flex flex-col border-t border-gray-100 pt-6">

                            <button :disabled="!form.terms_accepted" type="button" @click="submit"
                                    class="w-full positive-button">
                                Reservering aanvragen
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>
