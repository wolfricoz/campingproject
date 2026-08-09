<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    locations: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="__('Beschikbare Locaties')" />
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister"  >

        <!-- === Uitgelichte locaties === -->
        <div v-if="locations.length" class="bg-gray-50 px-4 py-12">
            <div class="mx-auto max-w-6xl">
                <h2 class="text-center text-2xl font-bold text-gray-800">{{ __('Uitgelichte locaties') }}</h2>
                <p class="mt-1 text-center text-sm text-gray-500">{{ __('Een greep uit onze mooiste plekken.') }}</p>

                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="location in locations" :key="location.id"
                         class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-xl">
                        <img v-if="location.photo" :src="location.photo" :alt="location.name"
                             class="h-40 w-full object-cover object-center"/>
                        <div v-else
                             class="flex h-40 w-full items-center justify-center bg-gradient-to-br from-emerald-100 to-emerald-300">
                            <span class="text-sm font-medium text-emerald-800/70">{{ __('Geen foto beschikbaar') }}</span>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <h3 class="truncate text-base font-semibold text-gray-800">{{ location.name }}</h3>
                            <p class="text-xs text-gray-500">{{ location.type ?? '—' }}</p>

                            <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ location.description }}</p>

                            <div class="mt-3 flex flex-wrap gap-1">
                                <span v-if="location.has_electricity"
                                      class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-700">{{ __('Stroom') }}</span>
                                <span v-if="location.has_water"
                                      class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-700">{{ __('Water') }}</span>
                                <span v-if="location.has_shade"
                                      class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-700">{{ __('Schaduw') }}</span>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-2 border-t border-gray-100 pt-4">
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ location.price_per_night ? '€ ' + location.price_per_night : '—' }}
                                    <span class="text-xs font-normal text-gray-500">{{ __('/ nacht') }}</span>
                                </span>
                                <Link class="positive-button" :href="route('booking')">{{ __('Boeken') }}</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>

</template>
