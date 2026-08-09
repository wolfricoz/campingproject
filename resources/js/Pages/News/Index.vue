<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {computed, ref} from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    // De paginator uit NewsController@index: {data: [...], links: [...]}
    news: {
        type: Object,
        default: () => ({data: [], links: []}),
    },
});

const page = usePage();
const dateLocale = computed(() => (page.props.locale === 'en' ? 'en-GB' : 'nl-NL'));

// Er is nog geen detailpagina, dus we klappen het bericht open in de kaart zelf.
const openedId = ref(null);

function toggle(article) {
    openedId.value = openedId.value === article.id ? null : article.id;
}

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
    <Head :title="__('Nieuws')"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-gray-50 px-4 py-12">
            <div class="mx-auto max-w-6xl">
                <h1 class="title-class">{{ __('Nieuws') }}</h1>
                <p class="mt-1 text-center text-sm text-gray-500">
                    {{ __('Het laatste nieuws en de aankomende activiteiten op de camping.') }}
                </p>

                <p v-if="!news.data.length" class="mt-8 text-center text-sm text-gray-500">
                    {{ __('Er is nog geen nieuws geplaatst.') }}
                </p>

                <div v-else class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="article in news.data" :key="article.id"
                             class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-lg transition hover:shadow-xl">
                        <img v-if="article.image" :src="article.image" :alt="article.title"
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

                            <h2 class="mt-2 text-base font-semibold text-gray-800">{{ article.title }}</h2>

                            <p v-if="article.summary" class="mt-2 text-sm text-gray-600">{{ article.summary }}</p>

                            <p v-if="openedId === article.id"
                               class="mt-3 whitespace-pre-line border-t border-gray-100 pt-3 text-sm text-gray-700">
                                {{ article.content }}
                            </p>

                            <div class="mt-4 flex items-center justify-end border-t border-gray-100 pt-4">
                                <button type="button" class="href-class text-sm" @click="toggle(article)">
                                    {{ openedId === article.id ? __('Lees minder') : __('Lees meer') }}
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <Pagination :links="news.links" class="mt-8"/>
            </div>
        </div>
    </GuestLayout>
</template>
