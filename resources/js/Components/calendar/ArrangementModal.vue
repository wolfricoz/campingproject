<script setup>
import {ref, computed, watch} from 'vue';

const props = defineProps({
    showModal: {type: Boolean, default: false},
    arrangement: {type: Object, default: null}, // pass when editing, null when creating
});

const customers = ref([]);
const locations = ref([]);

const emit = defineEmits(['close', 'save', 'changeStatus']);

// === Form state ===
const form = ref({
    id: 0,
    customer_id: null,
    location_id: null,
    start_date: '',
    end_date: '',
    payment_received: false,
    // editable customer fields
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
        create_account: false,
    },
});

function fetchLocations() {
    axios.get(route('api.locations')).then(response => {
        console.log(response);
        locations.value = response.data;
    }).catch(error => {
        console.log(error);
    })
}

function fetchCustomers() {
    axios.get(route('api.customers.index')).then(response => {
        customers.value = response.data;
    }).catch(error => {
        console.log(error);
    })

}


function toLocalInput(value) {
    if (!value) return '';
    const d = new Date(value);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

// When the prop is filled, we fill the form
watch(() => props.arrangement, (arrangement) => {
    if (!arrangement) return;
    form.value.id = arrangement.id ?? 0; // We send 0 to create a new record.
    form.value.customer_id = arrangement.customer_id ?? null;
    form.value.location_id = arrangement.location_id ?? null;
    form.value.start_date = toLocalInput(arrangement.start_date);
    form.value.end_date = toLocalInput(arrangement.end_date);
    form.value.payment_received = arrangement.payment_received ?? false;
    if (arrangement.customer) {
        form.value.customer = {
            id: arrangement.customer_id,
            name: arrangement.customer.name ?? '',
            email: arrangement.customer.email ?? '',
            phone_number: arrangement.customer.phone_number ?? '',
            street_name: arrangement.customer.street_name ?? '',
            street_number: arrangement.customer.street_number ?? '',
            postal_code: arrangement.customer.postal_code ?? '',
            city: arrangement.customer.city ?? '',
            country: arrangement.customer.country ?? '',
            create_account: !!arrangement.customer.user_id,
        };
    }
}, {immediate: true});

// When a customer is picked from the dropdown, copy their details into the editable block
watch(() => form.value.customer_id, (id) => {
    const found = customers.value.find((c) => c.id === id);
    if (!found) {
        form.value.customer = {
            id: form.value.customer_id,
            name: '',
            email: '',
            phone_number: '',
            street_name: '',
            street_number: '',
            postal_code: '',
            city: '',
            country: '',
            create_account: false,
        };
        console.log('couldn\'t find person.');
        return;
    }
    form.value.customer = {
        id: form.value.customer_id,
        name: found.name ?? '',
        email: found.email ?? '',
        phone_number: found.phone_number ?? '',
        street_name: found.street_name ?? '',
        street_number: found.street_number ?? '',
        postal_code: found.postal_code ?? '',
        city: found.city ?? '',
        country: found.country ?? '',
        create_account: !!found.user_id,
    };
});

// The read-only location info panel reflects the currently selected location
const selectedLocation = computed(() =>
    locations.value.find((l) => l.id === form.value.location_id) ?? null
);

function close() {
    emit('close');
}

async function save() {
    // save the changes
    try {

        const customerRes = await axios.post(route('api.customers.store'), form.value);
        if (customerRes?.data?.updated_data?.id) {
            form.value.customer_id = customerRes.data.updated_data.id;
            form.value.customer.id = customerRes.data.updated_data.id;
        }


        const arrangementRes = await axios.post(route('api.arrangements.store'), form.value);
        form.value.id = arrangementRes.data.updated_data.id; // capture new arrangement id too

        emit('save', {...form.value});

    } catch (error) {
        console.log(error);

    }
}
function changeStatus(status){
    // As a safety, we will prompt a confirm for certain statusses (eg. cancel)
    if (status === 'cancelled' || status === 'rejected') {
        let result = confirm(`Are you sure you want to ${status} this reservation?`)
        if (!result) {
            return
        }
    }

    // update the status of a post
    axios.post(route('api.arrangements.status'),{
        id: form.value.id,
        status: status,
    }).then(response => {
        props.arrangement.booking_status = status;
    }).catch(error => {

    });


    emit('changeStatus', {
        id: form.value.id,
        status: status,
    });

}

fetchLocations();
fetchCustomers()


function splitDate(value) {
    if (!value) return {date: '', time: ''};
    const [date, time = ''] = value.split('T');
    return {date, time: time.slice(0, 5)}; // HH:mm
}

const startDatePart = computed({
    get: () => splitDate(form.value.start_date).date,
    set: (val) => {
        const {time} = splitDate(form.value.start_date);
        form.value.start_date = val ? `${val}T${time || '00:00'}` : '';
    }
});
const startTimePart = computed({
    get: () => splitDate(form.value.start_date).time,
    set: (val) => {
        const {date} = splitDate(form.value.start_date);
        form.value.start_date = date ? `${date}T${val || '00:00'}` : '';
    }
});

const endDatePart = computed({
    get: () => splitDate(form.value.end_date).date,
    set: (val) => {
        const {time} = splitDate(form.value.end_date);
        form.value.end_date = val ? `${val}T${time || '00:00'}` : '';
    }
});
const endTimePart = computed({
    get: () => splitDate(form.value.end_date).time,
    set: (val) => {
        const {date} = splitDate(form.value.end_date);
        form.value.end_date = date ? `${date}T${val || '00:00'}` : '';
    }
});


</script>

<template>
    <div v-if="showModal"
         class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50 cursor-default"
         @click.self="close"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ arrangement ? 'Reservering bewerken' : 'Nieuwe reservering' }}
                </h2>
                <button class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="close">&times;</button>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- === Booking fields === -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Klant</label>
                        <select v-model="form.customer_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option :value="null">— Selecteer klant —</option>
                            <option :value="0">Nieuwe Klant</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Locatie</label>
                        <select v-model="form.location_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option :value="null">— Selecteer locatie —</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Startdatum</label>
                        <div class="flex gap-2">
                            <input type="date" v-model="startDatePart"
                                   class="flex-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="time" v-model="startTimePart" step="60"
                                   class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Einddatum</label>
                        <div class="flex gap-2">
                            <input type="date" v-model="endDatePart"
                                   class="flex-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="time" v-model="endTimePart" step="60"
                                   class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                    </div>
                </div>

                <!-- Payment received (read-only) -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">Betaling ontvangen:</span>
                    <span class="text-sm px-2 py-0.5 rounded-full"
                          :class="form.payment_received ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                        {{ form.payment_received ? 'Ja' : 'Nee' }}
                    </span>
                    <span class="text-sm font-medium text-gray-700">Reservering Status:</span>
                    <span class="text-sm px-2 py-0.5 rounded-full"
                          :class="{
        'bg-gray-100 border-gray-400':      arrangement.booking_status === 'pending',
        'bg-orange-100 border-orange-500':  arrangement.booking_status === 'confirmed',
        'bg-emerald-100 border-emerald-500': arrangement.booking_status === 'checked-in',
        'bg-blue-100 border-blue-500':      arrangement.booking_status === 'finished',
        'bg-red-100 border-red-500':        arrangement.booking_status === 'cancelled',
        'bg-rose-100 border-rose-500':      arrangement.booking_status === 'rejected',
    }">
                        {{ arrangement.booking_status }}
                    </span>
                </div>

                <!-- === Location info (read-only) === -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Locatiegegevens</h3>
                    <div v-if="selectedLocation" class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                        <div><span class="text-gray-500">Naam:</span> {{ selectedLocation.name }}</div>
                        <div><span class="text-gray-500">Type:</span> {{ selectedLocation.type ?? '—' }}</div>
                        <div><span class="text-gray-500">Capaciteit:</span> {{ selectedLocation.capacity ?? '—' }}</div>
                        <div><span class="text-gray-500">Slaapkamers:</span> {{ selectedLocation.bedrooms }}</div>
                        <div><span class="text-gray-500">Grootte:</span> {{ selectedLocation.size ? selectedLocation.size + ' m²' : '—' }}
                        </div>
                        <div><span class="text-gray-500">Prijs/nacht:</span>
                            {{ selectedLocation.price_per_night ? '€ ' + selectedLocation.price_per_night : '—' }}
                        </div>
                        <div><span class="text-gray-500">Stroom:</span> {{ selectedLocation.has_electricity ? 'Ja' : 'Nee' }}</div>
                        <div><span class="text-gray-500">Water:</span> {{ selectedLocation.has_water ? 'Ja' : 'Nee' }}</div>
                        <div><span class="text-gray-500">Schaduw:</span> {{ selectedLocation.has_shade ? 'Ja' : 'Nee' }}</div>
                        <div class="col-span-full">
                            <span class="text-gray-500">Beschrijving:</span> {{ selectedLocation.description ?? '—' }}
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400">Selecteer een locatie om de gegevens te zien.</p>
                </div>

                <!-- === Customer info (editable) === -->
                <div class="border border-gray-200 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Klantgegevens</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Naam</label>
                            <input type="text" v-model="form.customer.name"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" v-model="form.customer.email"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefoonnummer</label>
                            <input type="tel" v-model="form.customer.phone_number"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                    </div>

                    <!-- Address combination -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                            <input type="text" v-model="form.customer.street_name" placeholder="Straat"
                                   class="col-span-2 sm:col-span-3 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="text" v-model="form.customer.street_number" placeholder="Nr."
                                   class="sm:col-span-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="text" v-model="form.customer.postal_code" placeholder="Postcode"
                                   class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="text" v-model="form.customer.city" placeholder="Plaats"
                                   class="col-span-2 sm:col-span-3 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                            <input type="text" v-model="form.customer.country" placeholder="Land"
                                   class="col-span-2 sm:col-span-3 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                        </div>
                    </div>

                    <!-- Create account checkbox -->
                    <label class="flex items-center gap-2 mt-4 text-sm text-gray-700" v-if="!arrangement?.customer.user_id">
                        <input type="checkbox" v-model="form.customer.create_account"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        Account aanmaken voor deze klant
                    </label>
                    <span v-else class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                        Klant heeft al een account
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                    <button v-if="arrangement?.booking_status === 'checked-in'"
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('finished')"
                            v-show="props.arrangement"
                    >
                        Check-out
                    </button>
                    <button v-if="arrangement?.booking_status === 'confirmed' "
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('checked-in')"
                            v-show="props.arrangement"
                    >
                        Check-in
                    </button>

                    <button v-if="arrangement?.booking_status === 'pending' "
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('confirmed')"
                            v-show="props.arrangement"
                    >
                        Bevestig
                    </button>
                    <button v-if="arrangement?.booking_status === 'pending' "
                            class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('rejected')"
                            v-show="props.arrangement"
                    >
                        Afwijzen
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" @click="save">
                        {{ props.arrangement ? 'Bijwerken' : 'Opslaan' }}
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-emerald-700" @click="changeStatus('cancelled')"
                            v-show="props.arrangement"
                            v-if="arrangement?.booking_status !== 'checked-in' && arrangement?.booking_status !== 'finished' &&
                            arrangement?.booking_status !== 'pending' "
                    >
                        Reservatie Annuleren
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                    <button
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" @click="close">
                        Annuleren
                    </button>

                </div>

            </div>
        </div>
    </div>
</template>
