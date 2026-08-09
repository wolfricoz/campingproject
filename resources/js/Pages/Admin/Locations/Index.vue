<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head} from '@inertiajs/vue3';
import {ref} from 'vue';
import LocationModal from '@/Components/LocationModal.vue';

const props = defineProps({
    locations: {
        type: Array,
    }
});

const showModal = ref(false);
const selectedLocation = ref(null); // null betekent: nieuwe locatie aanmaken

function createLocation() {
    selectedLocation.value = null;
    showModal.value = true;
}

function editLocation(location) {
    selectedLocation.value = location;
    showModal.value = true;
}

function onSave(data) {
    const location = props.locations.find(l => l.id === data.id);
    if (!location) {
        props.locations.push(data);
        showModal.value = false;

        return;
    }

    Object.assign(location, data);
    showModal.value = false;
}
</script>

<template>
    <Head :title="__('Locaties')"/>
    <LocationModal :show-modal="showModal" :location="selectedLocation"
                   @close="showModal = false" @save="onSave"/>

    <AuthenticatedLayout>
        <div class="h-full w-full p-2">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="flex items-center justify-between p-4">
                    <h1 class="text-2xl font-bold">{{ __('Locaties') }}</h1>
                    <button class="positive-button" @click="createLocation">{{ __('Nieuwe locatie') }}</button>
                </div>

                <div class="gap-2 flex flex-col p-6 pt-0">
                    <p v-if="locations.length < 1" class="text-sm text-gray-500 text-center">
                        {{ __('Er zijn nog geen locaties.') }}
                    </p>

                    <div v-for="location in locations" :key="location.id"
                         class="rounded-lg px-3 py-2 shadow-sm border-l-4 bg-white transition-colors hover:brightness-95 cursor-pointer"
                         :class="location.status === 1 ? 'border-emerald-500' : 'border-gray-400'"
                         @click="editLocation(location)"
                    >
                        <div class="flex items-stretch gap-4 divide-x divide-black/10">
                            <!-- Naam en type -->
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Locatie') }}</p>
                                <h3 class="truncate text-sm font-semibold text-gray-800">{{ location.name }}</h3>
                                <p class="truncate text-[11px] text-gray-500">{{ location.type ?? '—' }}</p>
                            </div>

                            <!-- Capaciteit -->
                            <div class="min-w-0 flex-1 pl-4">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Capaciteit') }}</p>
                                <p class="truncate text-[11px] text-gray-600">
                                    {{ __(':count pers', {count: location.capacity ?? '—'}) }} ·
                                    {{ __choice(':count slaapkamer|:count slaapkamers', location.bedrooms) }}
                                </p>
                                <p class="truncate text-[11px] text-gray-600">
                                    {{ location.size ? location.size + ' m²' : '—' }}
                                </p>
                            </div>

                            <!-- Voorzieningen -->
                            <div class="min-w-0 flex-1 pl-4">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Voorzieningen') }}</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="location.has_electricity"
                                          class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ __('Stroom') }}</span>
                                    <span v-if="location.has_water"
                                          class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ __('Water') }}</span>
                                    <span v-if="location.has_shade"
                                          class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ __('Schaduw') }}</span>
                                    <span v-if="!location.has_electricity && !location.has_water && !location.has_shade"
                                          class="text-[11px] text-gray-500">—</span>
                                </div>
                            </div>

                            <!-- Status en prijs -->
                            <div class="min-w-0 flex-1 pl-4 flex flex-col items-start gap-1">
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                                      :class="location.status === 1 ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-700'">
                                    {{ location.status === 1 ? __('Actief') : __('Inactief') }}
                                </span>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ location.price_per_night ? '€ ' + location.price_per_night : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
