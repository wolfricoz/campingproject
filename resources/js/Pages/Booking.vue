<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";
import {__} from '@/translate';

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

// Klanten mogen niet in het verleden boeken, dus de datumvelden beginnen bij vandaag.
const today = computed(() => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
});

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

// === Beschikbaarheid ===
const availability = ref({checking: false, available: null, message: ''});

// Alles wat niet expliciet beschikbaar is blokkeert de aanvraag: de endpoint meldt zo
// zowel een bezette locatie als een datum in het verleden.
const isUnavailable = computed(() =>
    availability.value.available !== true && !!availability.value.message
);

let availabilityTimer = null;

function checkAvailability() {
    if (!form.location_id || !form.start_date || !form.end_date) {
        availability.value = {checking: false, available: null, message: ''};
        return;
    }

    availability.value = {checking: true, available: null, message: ''};

    axios.post(route('api.locations.available'), {
        location_id: form.location_id,
        start_date: form.start_date,
        end_date: form.end_date,
    }).then(response => {
        const available = response.data.available === true;
        availability.value = {
            checking: false,
            available: available,
            // De melding komt van de backend, die weet waarom het niet kan. De vertaalsleutels
            // zijn de Nederlandse teksten, dus we halen hem alsnog door __() heen.
            message: available ? '' : __(response.data.message ?? 'Deze locatie is in de gekozen periode al bezet.'),
        };
        if (available) {
            form.clearErrors('location_id');
        }
    }).catch(error => {
        availability.value = {checking: false, available: null, message: ''};
        console.log(error);
    });
}

// De gebruiker typt datums, dus we wachten even voordat we de server bevragen.
watch(() => [form.location_id, form.start_date, form.end_date], () => {
    availability.value = {checking: false, available: null, message: ''};
    clearTimeout(availabilityTimer);
    availabilityTimer = setTimeout(checkAvailability, 300);
}, {immediate: true});

function submit() {
    // Een bezette locatie mag niet worden opgeslagen; we blokkeren de aanvraag hier al.
    if (isUnavailable.value) {
        form.setError('location_id', availability.value.message);
        return;
    }

    form.post(route('booking.store'), {
        onError: (errors) => {
            if (errors.location_id) {
                availability.value = {checking: false, available: false, message: errors.location_id};
            }
        },
    });

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
    <Head :title="__('Reservering aanvragen')"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-[url(/images/header.jpg)]  px-4 py-10 bg-cover bg-center">
            <div class="max-w-3xl mx-auto ">

                <div class="card-base">
                    <h2 class="text-xl font-semibold text-gray-800">{{ __('Reservering aanvragen') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Vul je gegevens in en kies een locatie.') }}</p>

                    <!-- Algemene foutmelding wanneer er validatiefouten zijn -->
                    <div v-if="hasErrors"
                         class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-medium">{{ __('Er ging iets mis. Controleer de gemarkeerde velden:') }}</p>
                        <ul class="mt-1 list-inside list-disc">
                            <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
                        </ul>
                    </div>

                    <div class="mt-6 space-y-6">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-3">
                                <label class="label-base">{{ __('Locatie') }} <span class="text-red-600">*</span></label>
                                <select v-model="form.location_id"
                                        class="w-full input-base"
                                        :class="{'border-red-500': isUnavailable}">
                                    <option :value="null">{{ __('— Selecteer een locatie —') }}</option>
                                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                                <p v-if="form.errors.location_id" class="error-base">{{ form.errors.location_id }}</p>
                                <p v-else-if="availability.checking" class="mt-1 text-xs text-gray-500">
                                    {{ __('Beschikbaarheid controleren…') }}
                                </p>
                                <p v-else-if="isUnavailable" class="error-base">
                                    {{ availability.message }}
                                </p>
                                <p v-else-if="availability.available" class="mt-1 text-xs text-emerald-600">
                                    {{ __('Deze locatie is beschikbaar in de gekozen periode.') }}
                                </p>
                            </div>

                            <div>
                                <label class="label-base">{{ __('Aankomst') }} <span class="text-red-600">*</span></label>
                                <div class="flex gap-2">
                                    <input type="date" v-model="startDatePart" :min="today"
                                           class="flex-1 input-base"/>
                                    <input type="time" v-model="startTimePart" step="60"
                                           class="input-base"/>
                                </div>
                                <p v-if="form.errors.start_date" class="error-base">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="label-base">{{ __('Vertrek') }} <span class="text-red-600">*</span></label>
                                <div class="flex gap-2">
                                    <input type="date" v-model="endDatePart" :min="startDatePart || today"
                                           class="flex-1 input-base"/>
                                    <input type="time" v-model="endTimePart" step="60"
                                           class="input-base"/>
                                </div>
                                <p v-if="form.errors.end_date" class="error-base">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Berekende nachten en prijs -->
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ __choice(':count nacht|:count nachten', days) }}</span>
                            <span class="font-semibold text-emerald-700">€ {{ price }}</span>
                        </div>


                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">{{ __('Jouw gegevens') }}</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label-base">{{ __('Naam') }} <span class="text-red-600">*</span></label>
                                    <input type="text" v-model="form.customer.name" :placeholder="__('Volledige naam')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.name']" class="error-base">{{ form.errors['customer.name'] }}</p>
                                </div>
                                <div>
                                    <label class="label-base">{{ __('E-mail') }} <span class="text-red-600">*</span></label>
                                    <input type="email" v-model="form.customer.email" :placeholder="__('naam@voorbeeld.nl')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.email']" class="error-base">{{ form.errors['customer.email'] }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label-base">{{ __('Telefoonnummer') }}</label>
                                    <input type="tel" v-model="form.customer.phone_number" placeholder="06 12345678"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.phone_number']" class="error-base">{{ form.errors['customer.phone_number'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800">{{ __('Adres') }}</h3>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.street_name" :placeholder="__('Straat')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.street_name']" class="error-base">{{ form.errors['customer.street_name'] }}</p>
                                </div>
                                <div class="sm:col-span-1">
                                    <input type="text" v-model="form.customer.street_number" :placeholder="__('Nr.')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.street_number']" class="error-base">{{ form.errors['customer.street_number'] }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="text" v-model="form.customer.postal_code" :placeholder="__('Postcode')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.postal_code']" class="error-base">{{ form.errors['customer.postal_code'] }}</p>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.city" :placeholder="__('Plaats')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.city']" class="error-base">{{ form.errors['customer.city'] }}</p>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <input type="text" v-model="form.customer.country" :placeholder="__('Land')"
                                           class="w-full input-base"/>
                                    <p v-if="form.errors['customer.country']" class="error-base">{{ form.errors['customer.country'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Account Aanmaken optie -->
                        <div class="flex-col gap-2  border-t border-gray-100 pt-6">
                            <div class="flex text-xs text-gray-800 gap-2">
                                <p>{{ __('Maak een account aan en bekijk je boeking wanneer je maar wilt.') }}</p>
                                <checkbox :checked="false" v-model="form.customer.create_account">

                                </checkbox>
                            </div>
                            <div class="flex text-xs text-gray-800 gap-2">

                                <p>{{ __('Door je account aan te maken ga je akkoord met onze') }}
                                    <Link class="href-class" href="/voorwaarden">{{ __('algemene voorwaarden') }}</Link>
                                    {{ __('en') }}
                                    <Link class="href-class" href="/privacy">{{ __('privacyverklaring') }}</Link>. <span class="text-red-600">*</span></p>
                                <checkbox :checked="false" v-model="form.terms_accepted">

                                </checkbox>
                            </div>
                            <p v-if="form.errors.terms_accepted" class="error-base">{{ form.errors.terms_accepted }}</p>
                            <p v-if="form.errors['customer.create_account']" class="error-base">{{ form.errors['customer.create_account'] }}</p>
                            <p v-if="form.errors.customer_id" class="error-base">{{ form.errors.customer_id }}</p>

                        </div>


                        <div class="flex flex-col border-t border-gray-100 pt-6">

                            <button :disabled="!form.terms_accepted || isUnavailable || availability.checking || form.processing"
                                    type="button" @click="submit"
                                    class="w-full positive-button disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ __('Reservering aanvragen') }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>
