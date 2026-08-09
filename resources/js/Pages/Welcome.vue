<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { computed } from 'vue';

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
    news: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const dateLocale = computed(() => (page.props.locale === 'en' ? 'en-GB' : 'nl-NL'));

function formatDate(value) {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleDateString(dateLocale.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

const typeStyles = {
    Evenement: 'bg-blue-100 text-blue-800',
    Onderhoud: 'bg-amber-100 text-amber-800',
    Aanbieding: 'bg-emerald-100 text-emerald-800',
};

function typeClass(type) {
    return typeStyles[type] ?? 'bg-gray-100 text-gray-700';
}
</script>

<template>
    <Head :title="__('Welkom')" />
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister"  >
        <div class="relative h-96 w-full">
            <img src="/images/header.jpg" :alt="__('Uitzicht over de camping')"
                 class="absolute inset-0 w-full h-full object-cover object-center" />
            <div class="relative z-10 flex justify-left h-full p-10">
                <div
                    class="flex flex-col justify-between w-64 rounded-xl bg-emerald-500/80 backdrop-blur-sm px-6 py-4 text-center shadow-lg ring-1 ring-white/20 transition hover:bg-emerald-600 hover:shadow-xl cursor-pointer">
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ __('Boek je vakantie!') }}</h3>
                        <p class="mt-1 text-sm text-white/80">{{ __('Vind jouw perfecte plek') }}</p>
                    </div>
                    <Link class="general-button" :href="route('booking')">
                        {{ __('Reserveer nu!') }}
                    </Link>
                </div>

            </div>
        </div>

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

        <!-- === Laatste nieuws === -->
        <div v-if="news.length" class="bg-white px-4 py-12">
            <div class="mx-auto max-w-6xl">
                <h2 class="text-center text-2xl font-bold text-gray-800">{{ __('Laatste nieuws') }}</h2>
                <p class="mt-1 text-center text-sm text-gray-500">
                    {{ __('Het laatste nieuws en de aankomende activiteiten op de camping.') }}
                </p>

                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="article in news" :key="article.id"
                             class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-xl">
                        <img v-if="article.image_url" :src="article.image_url" :alt="article.title"
                             class="h-40 w-full object-cover object-center"/>
                        <div v-else
                             class="flex h-40 w-full items-center justify-center bg-gradient-to-br from-emerald-100 to-emerald-300">
                            <span class="text-sm font-medium text-emerald-800/70">{{ __('Geen foto beschikbaar') }}</span>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="typeClass(article.type)">
                                    {{ __(article.type) }}
                                </span>
                                <time class="text-[11px] text-gray-500" :datetime="article.created_at">
                                    {{ formatDate(article.created_at) }}
                                </time>
                            </div>

                            <h3 class="mt-2 text-base font-semibold text-gray-800">{{ article.title }}</h3>
                            <p v-if="article.summary" class="mt-2 line-clamp-3 text-sm text-gray-600">{{ article.summary }}</p>
                        </div>
                    </article>
                </div>

                <div class="mt-8 flex justify-center">
                    <Link class="positive-button" :href="route('news')">{{ __('Bekijk al het nieuws') }}</Link>
                </div>
            </div>
        </div>
    </GuestLayout>

</template>
