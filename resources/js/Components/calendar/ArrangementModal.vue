<script setup>
import {ref, computed, watch} from 'vue';
import {__} from '@/translate';

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
        let result = confirm(__('Weet je zeker dat je deze reservering wilt :status?', {status: __(status)}))
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

// De einddatum mag nooit voor de startdatum liggen, dus we schuiven hem mee.
watch(() => form.value.start_date, () => {
    if (form.value.end_date && form.value.end_date < form.value.start_date) {
        form.value.end_date = form.value.start_date;
    }
});

// === Nachten & prijs ===
const days = ref(0);
const price = ref(0);

function fetchDays() {
    if (!form.value.start_date || !form.value.end_date) {
        days.value = 0;
        return;
    }
    axios.get(route('api.calculations.days'), {
        params: {start_date: form.value.start_date, end_date: form.value.end_date},
    }).then(response => {
        days.value = response.data.days;
    }).catch(error => {
        days.value = 0;
        console.log(error);
    });
}

function fetchPrice() {
    if (!form.value.location_id || !days.value) {
        price.value = 0;
        return;
    }
    axios.get(route('api.calculations.price'), {
        params: {location_id: form.value.location_id, days: days.value},
    }).then(response => {
        price.value = response.data.price;
    }).catch(error => {
        price.value = 0;
        console.log(error);
    });
}

watch(() => [form.value.start_date, form.value.end_date], fetchDays, {immediate: true});
watch(() => [form.value.location_id, days.value], fetchPrice);


</script>

<template>
    <div v-if="showModal"
         class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50 cursor-default"
         @click.self="close"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ arrangement ? __('Reservering bewerken') : __('Nieuwe reservering') }}
                </h2>
                <button class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="close">&times;</button>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- === Booking fields === -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">* {{ __('Klant') }}</label>
                        <select v-model="form.customer_id"
                                class="w-full input-base"
                                required
                        >
                            <option :value="null">{{ __('— Selecteer klant —') }}</option>
                            <option :value="0">{{ __('Nieuwe klant') }}</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-base">* {{ __('Locatie') }}</label>
                        <select v-model="form.location_id"
                                class="w-full input-base"
                                required
                        >
                            <option :value="null">{{ __('— Selecteer locatie —') }}</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-base">* {{ __('Startdatum') }}</label>
                        <div class="flex gap-2">
                            <input type="date" v-model="startDatePart"
                                   class="flex-1 input-base"
                                   required
                            />
                            <input type="time" v-model="startTimePart" step="60"
                                   class="input-base"
                                   required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="label-base">* {{ __('Einddatum') }}</label>
                        <div class="flex gap-2">
                            <input type="date" v-model="endDatePart" :min="startDatePart"
                                   class="flex-1 input-base"
                                   required
                            />
                            <input type="time" v-model="endTimePart" step="60"
                                   class="input-base"
                                   required
                            />
                        </div>
                    </div>
                </div>

                <!-- Berekende nachten en prijs -->
                <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm">
                    <span class="text-gray-700">{{ __choice(':count nacht|:count nachten', days) }}</span>
                    <span class="font-semibold text-emerald-700">€ {{ price }}</span>
                </div>

                <!-- Payment received (read-only) -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">{{ __('Betaling ontvangen') }}:</span>
                    <span class="text-sm px-2 py-0.5 rounded-full"
                          :class="form.payment_received ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                        {{ form.payment_received ? __('Ja') : __('Nee') }}
                    </span>
                    <span class="text-sm font-medium text-gray-700">{{ __('Reservering status') }}:</span>
                    <span class="text-sm px-2 py-0.5 rounded-full"
                          :class="{
        'bg-gray-100 border-gray-400':      arrangement.booking_status === 'pending',
        'bg-orange-100 border-orange-500':  arrangement.booking_status === 'confirmed',
        'bg-emerald-100 border-emerald-500': arrangement.booking_status === 'checked-in',
        'bg-blue-100 border-blue-500':      arrangement.booking_status === 'finished',
        'bg-red-100 border-red-500':        arrangement.booking_status === 'cancelled',
        'bg-rose-100 border-rose-500':      arrangement.booking_status === 'rejected',
    }">
                        {{ __(arrangement.booking_status) }}
                    </span>
                </div>
<!--            === Locatie gegevens ===    -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">* {{ __('Locatiegegevens') }}</h3>
                    <div v-if="selectedLocation" class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                        <div><span class="text-gray-500">{{ __('Naam') }}:</span> {{ selectedLocation.name }}</div>
                        <div><span class="text-gray-500">{{ __('Type') }}:</span> {{ selectedLocation.type ?? '—' }}</div>
                        <div><span class="text-gray-500">{{ __('Capaciteit') }}:</span> {{ selectedLocation.capacity ?? '—' }}</div>
                        <div><span class="text-gray-500">{{ __('Slaapkamers') }}:</span> {{ selectedLocation.bedrooms }}</div>
                        <div><span class="text-gray-500">{{ __('Grootte') }}:</span> {{ selectedLocation.size ? selectedLocation.size + ' m²' : '—' }}
                        </div>
                        <div><span class="text-gray-500">{{ __('Prijs/nacht') }}:</span>
                            {{ selectedLocation.price_per_night ? '€ ' + selectedLocation.price_per_night : '—' }}
                        </div>
                        <div><span class="text-gray-500">{{ __('Stroom') }}:</span> {{ selectedLocation.has_electricity ? __('Ja') : __('Nee') }}</div>
                        <div><span class="text-gray-500">{{ __('Water') }}:</span> {{ selectedLocation.has_water ? __('Ja') : __('Nee') }}</div>
                        <div><span class="text-gray-500">{{ __('Schaduw') }}:</span> {{ selectedLocation.has_shade ? __('Ja') : __('Nee') }}</div>
                        <div class="col-span-full">
                            <span class="text-gray-500">{{ __('Beschrijving') }}:</span> {{ selectedLocation.description ?? '—' }}
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400">{{ __('Selecteer een locatie om de gegevens te zien.') }}</p>
                </div>

                <!--            === Klant gegevens ===    -->
                <div class="border border-gray-200 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Klantgegevens') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-base">* {{ __('Naam') }}</label>
                            <input type="text" v-model="form.customer.name"
                                   class="w-full input-base"
                                   required

                            />
                        </div>
                        <div>
                            <label class="label-base">* {{ __('E-mail') }}</label>
                            <input type="email" v-model="form.customer.email"
                                   class="w-full input-base"
                                   required

                            />
                        </div>
                        <div>
                            <label class="label-base">* {{ __('Telefoonnummer') }}</label>
                            <input type="tel" v-model="form.customer.phone_number"
                                   class="w-full input-base"
                                   required

                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="label-base">* {{ __('Adres') }}</label>
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                            <input type="text" v-model="form.customer.street_name" :placeholder="__('Straat')"
                                   class="col-span-2 sm:col-span-3 input-base"
                                   required
                            />
                            <input type="text" v-model="form.customer.street_number" :placeholder="__('Nr.')"
                                   class="sm:col-span-1 input-base"
                                   required
                            />
                            <input type="text" v-model="form.customer.postal_code" :placeholder="__('Postcode')"
                                   class="sm:col-span-2 input-base"
                                   required
                            />
                            <input type="text" v-model="form.customer.city" :placeholder="__('Plaats')"
                                   class="col-span-2 sm:col-span-3 input-base"
                                   required
                            />
                            <input type="text" v-model="form.customer.country" :placeholder="__('Land')"
                                   class="col-span-2 sm:col-span-3 input-base"
                                   required
                            />
                        </div>
                    </div>

                    <label class="flex items-center gap-2 mt-4 text-sm text-gray-700" v-if="!arrangement?.customer.user_id">
                        <input type="checkbox" v-model="form.customer.create_account"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        {{ __('Account aanmaken voor deze klant') }}
                    </label>
                    <span v-else class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                        {{ __('Klant heeft al een account') }}
                    </span>
                </div>
            </div>

            <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                    <button v-if="arrangement?.booking_status === 'checked-in'"
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('finished')"
                            v-show="props.arrangement"
                    >
                        {{ __('Check-out') }}
                    </button>
                    <button v-if="arrangement?.booking_status === 'confirmed' "
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('checked-in')"
                            v-show="props.arrangement"
                    >
                        {{ __('Check-in') }}
                    </button>

                    <button v-if="arrangement?.booking_status === 'pending' "
                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('confirmed')"
                            v-show="props.arrangement"
                    >
                        {{ __('Bevestig') }}
                    </button>
                    <button v-if="arrangement?.booking_status === 'pending' "
                            class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-emerald-700"
                            @click="changeStatus('rejected')"
                            v-show="props.arrangement"
                    >
                        {{ __('Afwijzen') }}
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" @click="save">
                        {{ props.arrangement ? __('Bijwerken') : __('Opslaan') }}
                    </button>
                    <button class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-emerald-700" @click="changeStatus('cancelled')"
                            v-show="props.arrangement"
                            v-if="arrangement?.booking_status !== 'checked-in' && arrangement?.booking_status !== 'finished' &&
                            arrangement?.booking_status !== 'pending' "
                    >
                        {{ __('Reservering annuleren') }}
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                    <button
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" @click="close">
                        {{ __('Annuleren') }}
                    </button>

                </div>

            </div>
        </div>
    </div>
</template>
