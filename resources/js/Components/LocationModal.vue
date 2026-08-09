<script setup>
import {ref, watch} from 'vue';

const props = defineProps({
    showModal: {type: Boolean, default: false},
    location: {type: Object, default: null}, // pass when editing, null when creating
});

const emit = defineEmits(['close', 'save']);

// === Form state ===
const form = ref({
    id: 0,
    name: '',
    type: '',
    description: '',
    photo: '',
    capacity: null,
    bedrooms: 1,
    size: null,
    price_per_night: null,
    has_electricity: false,
    has_water: false,
    has_shade: false,
    status: 1,
    is_advertised: false,
});

const errors = ref({});

// When the prop is filled, we fill the form
watch(() => props.location, (location) => {
    if (!location) {
        form.value = {
            id: 0, // We send 0 to create a new record.
            name: '',
            type: '',
            description: '',
            photo: '',
            capacity: null,
            bedrooms: 1,
            size: null,
            price_per_night: null,
            has_electricity: false,
            has_water: false,
            has_shade: false,
            status: 1,
            is_advertised: false,
        };

        return;
    }

    form.value = {
        id: location.id ?? 0,
        name: location.name ?? '',
        type: location.type ?? '',
        description: location.description ?? '',
        photo: location.photo ?? '',
        capacity: location.capacity ?? null,
        bedrooms: location.bedrooms ?? 1,
        size: location.size ?? null,
        price_per_night: location.price_per_night ?? null,
        has_electricity: location.has_electricity ?? false,
        has_water: location.has_water ?? false,
        has_shade: location.has_shade ?? false,
        status: location.status ?? 1,
        is_advertised: location.is_advertised ?? false,
    };
}, {immediate: true});

function close() {
    emit('close');
}

function save() {
    errors.value = {};

    axios.post(route('api.locations.store'), form.value).then(response => {
        emit('save', {...response.data.updated_data});
    }).catch(error => {
        errors.value = error.response?.data?.errors ?? {};
        console.log(error);
    });
}
</script>

<template>
    <div v-if="showModal"
         class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50 cursor-default"
         @click.self="close"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ location ? __('Locatie bewerken') : __('Nieuwe locatie') }}
                </h2>
                <button class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="close">&times;</button>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- === Algemeen === -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">* {{ __('Naam') }}</label>
                        <input type="text" v-model="form.name"
                               class="w-full input-base"
                               required
                        />
                        <p v-if="errors.name" class="error-base">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="label-base">{{ __('Type') }}</label>
                        <input type="text" v-model="form.type" :placeholder="__('Bijv. chalet, caravanplaats')"
                               class="w-full input-base"
                        />
                        <p v-if="errors.type" class="error-base">{{ errors.type[0] }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">{{ __('Beschrijving') }}</label>
                        <textarea v-model="form.description" rows="3"
                                  class="w-full input-base"
                        ></textarea>
                        <p v-if="errors.description" class="error-base">{{ errors.description[0] }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">{{ __('Foto') }}</label>
                        <div class="flex gap-4">
                            <input type="text" v-model="form.photo" placeholder="/images/chalet.jpg"
                                   class="flex-1 input-base"
                            />
                            <img v-if="form.photo" :src="form.photo" alt=""
                                 class="h-16 w-24 rounded-lg object-cover object-center"/>
                            <div v-else
                                 class="flex h-16 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-300 text-[10px] text-emerald-800/70">
                                {{ __('Geen foto') }}
                            </div>
                        </div>
                        <p v-if="errors.photo" class="error-base">{{ errors.photo[0] }}</p>
                    </div>
                </div>

                <!-- === Eigenschappen === -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Eigenschappen') }}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="label-base">{{ __('Capaciteit') }}</label>
                            <input type="number" v-model.number="form.capacity" min="1"
                                   class="w-full input-base"
                            />
                            <p v-if="errors.capacity" class="error-base">{{ errors.capacity[0] }}</p>
                        </div>
                        <div>
                            <label class="label-base">* {{ __('Slaapkamers') }}</label>
                            <input type="number" v-model.number="form.bedrooms" min="0"
                                   class="w-full input-base"
                                   required
                            />
                            <p v-if="errors.bedrooms" class="error-base">{{ errors.bedrooms[0] }}</p>
                        </div>
                        <div>
                            <label class="label-base">{{ __('Grootte (m²)') }}</label>
                            <input type="number" v-model.number="form.size" min="0" step="0.01"
                                   class="w-full input-base"
                            />
                            <p v-if="errors.size" class="error-base">{{ errors.size[0] }}</p>
                        </div>
                        <div>
                            <label class="label-base">{{ __('Prijs/nacht') }}</label>
                            <input type="number" v-model.number="form.price_per_night" min="0" step="0.01"
                                   class="w-full input-base"
                            />
                            <p v-if="errors.price_per_night" class="error-base">{{ errors.price_per_night[0] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-6 mt-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.has_electricity"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            {{ __('Stroom') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.has_water"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            {{ __('Water') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.has_shade"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            {{ __('Schaduw') }}
                        </label>
                    </div>
                </div>

                <!-- === Status === -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">{{ __('Status') }}:</span>
                    <select v-model.number="form.status"
                            class="input-base">
                        <option :value="1">{{ __('Actief') }}</option>
                        <option :value="0">{{ __('Inactief') }}</option>
                    </select>
                    <span class="text-xs text-gray-500">{{ __('Inactieve locaties zijn niet te boeken.') }}</span>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" v-model="form.is_advertised"
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                    />
                    {{ __('Uitlichten op de homepage') }}
                </label>
            </div>

            <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200">
                <button class="positive-button" @click="save">
                    {{ location ? __('Bijwerken') : __('Opslaan') }}
                </button>
                <button class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                        @click="close">
                    {{ __('Annuleren') }}
                </button>
            </div>
        </div>
    </div>
</template>
