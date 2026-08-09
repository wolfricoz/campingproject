<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import NewsModal from '@/Components/NewsModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    // De paginator uit NewsController@admin: {data: [...], links: [...]}
    news: {
        type: Object,
        default: () => ({data: [], links: []}),
    },
});

const page = usePage();
const dateLocale = computed(() => (page.props.locale === 'en' ? 'en-GB' : 'nl-NL'));

const showModal = ref(false);
const selectedArticle = ref(null); // null betekent: nieuw bericht aanmaken

function createArticle() {
    selectedArticle.value = null;
    showModal.value = true;
}

function editArticle(article) {
    selectedArticle.value = article;
    showModal.value = true;
}

function onSave(data) {
    const article = props.news.data.find(a => a.id === data.id);
    if (!article) {
        props.news.data.unshift(data);
        showModal.value = false;

        return;
    }

    Object.assign(article, data);
    showModal.value = false;
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(dateLocale.value, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="__('Nieuws')"/>
    <NewsModal :show-modal="showModal" :article="selectedArticle"
               @close="showModal = false" @save="onSave"/>

    <AuthenticatedLayout>
        <div class="h-full w-full p-2">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="flex items-center justify-between p-4">
                    <h1 class="text-2xl font-bold">{{ __('Nieuws') }}</h1>
                    <button class="positive-button" @click="createArticle">{{ __('Nieuw bericht') }}</button>
                </div>

                <div class="gap-2 flex flex-col p-6 pt-0">
                    <p v-if="!news.data.length" class="text-sm text-gray-500 text-center">
                        {{ __('Er zijn nog geen nieuwsberichten.') }}
                    </p>

                    <div v-for="article in news.data" :key="article.id"
                         class="rounded-lg px-3 py-2 shadow-sm border-l-4 bg-white transition-colors hover:brightness-95 cursor-pointer"
                         :class="article.published ? 'border-emerald-500' : 'border-gray-400'"
                         @click="editArticle(article)"
                    >
                        <div class="flex items-stretch gap-4 divide-x divide-black/10">
                            <!-- Titel en type -->
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Titel') }}</p>
                                <h3 class="truncate text-sm font-semibold text-gray-800">{{ article.title }}</h3>
                                <p class="truncate text-[11px] text-gray-500">{{ __(article.type) }}</p>
                            </div>

                            <!-- Samenvatting -->
                            <div class="min-w-0 flex-[2] pl-4">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Samenvatting') }}</p>
                                <p class="truncate text-[11px] text-gray-600">{{ article.summary || '—' }}</p>
                            </div>

                            <!-- Datum -->
                            <div class="min-w-0 flex-1 pl-4">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Geplaatst op') }}</p>
                                <p class="truncate text-[11px] text-gray-600">{{ formatDate(article.created_at) }}</p>
                            </div>

                            <!-- Status -->
                            <div class="min-w-0 flex-1 pl-4 flex flex-col items-start gap-1">
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                                      :class="article.published ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-700'">
                                    {{ article.published ? __('Gepubliceerd') : __('Concept') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Pagination :links="news.links" class="mt-4"/>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
