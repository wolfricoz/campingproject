<script setup>
import {ref, watch} from 'vue';

const props = defineProps({
    showModal: {type: Boolean, default: false},
    article: {type: Object, default: null}, // pass when editing, null when creating
});

const emit = defineEmits(['close', 'save']);

const types = ['Algemeen', 'Evenement', 'Onderhoud', 'Aanbieding'];

// === Form state ===
const form = ref({
    id: 0,
    title: '',
    type: 'Algemeen',
    summary: '',
    content: '',
    published: false,
});

const image = ref(null);
const imagePreview = ref('');
const errors = ref({});
const processing = ref(false);

// When the prop is filled, we fill the form
watch(() => props.article, (article) => {
    errors.value = {};
    image.value = null;

    if (!article) {
        form.value = {
            id: 0, // We send 0 to create a new record.
            title: '',
            type: 'Algemeen',
            summary: '',
            content: '',
            published: false,
        };
        imagePreview.value = '';

        return;
    }

    form.value = {
        id: article.id ?? 0,
        title: article.title ?? '',
        type: article.type ?? 'Algemeen',
        summary: article.summary ?? '',
        content: article.content ?? '',
        published: Boolean(article.published),
    };
    imagePreview.value = article.image ?? '';
}, {immediate: true});

function selectImage(event) {
    const file = event.target.files?.[0] ?? null;
    image.value = file;
    imagePreview.value = file ? URL.createObjectURL(file) : (props.article?.image ?? '');
}

function close() {
    emit('close');
}

function save() {
    errors.value = {};
    processing.value = true;

    const payload = new FormData();
    payload.append('id', form.value.id);
    payload.append('title', form.value.title);
    payload.append('type', form.value.type);
    payload.append('summary', form.value.summary ?? '');
    payload.append('content', form.value.content);
    payload.append('published', form.value.published ? '1' : '0');

    if (image.value) {
        payload.append('image', image.value);
    }

    axios.post(route('api.news.store'), payload).then(response => {
        emit('save', {...response.data.data});
    }).catch(error => {
        errors.value = error.response?.data?.errors ?? {};
        console.log(error);
    }).finally(() => {
        processing.value = false;
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
                    {{ article ? __('Nieuwsbericht bewerken') : __('Nieuw bericht') }}
                </h2>
                <button class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="close">&times;</button>
            </div>

            <div class="px-6 py-4 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">* {{ __('Titel') }}</label>
                        <input type="text" v-model="form.title" maxlength="100"
                               class="w-full input-base"
                               required
                        />
                        <p v-if="errors.title" class="error-base">{{ errors.title[0] }}</p>
                    </div>

                    <div>
                        <label class="label-base">* {{ __('Type') }}</label>
                        <select v-model="form.type" class="w-full input-base">
                            <option v-for="type in types" :key="type" :value="type">{{ __(type) }}</option>
                        </select>
                        <p v-if="errors.type" class="error-base">{{ errors.type[0] }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">{{ __('Samenvatting') }}</label>
                        <textarea v-model="form.summary" rows="2"
                                  :placeholder="__('Korte tekst die op de overzichtspagina staat')"
                                  class="w-full input-base"
                        ></textarea>
                        <p v-if="errors.summary" class="error-base">{{ errors.summary[0] }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">* {{ __('Inhoud') }}</label>
                        <textarea v-model="form.content" rows="8"
                                  class="w-full input-base"
                                  required
                        ></textarea>
                        <p v-if="errors.content" class="error-base">{{ errors.content[0] }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">{{ __('Afbeelding') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="file" accept="image/*" @change="selectImage"
                                   class="flex-1 text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:text-gray-700 hover:file:bg-gray-200"
                            />
                            <img v-if="imagePreview" :src="imagePreview" alt=""
                                 class="h-16 w-24 rounded-lg object-cover object-center"/>
                            <div v-else
                                 class="flex h-16 w-24 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-300 text-[10px] text-emerald-800/70">
                                {{ __('Geen foto') }}
                            </div>
                        </div>
                        <p v-if="errors.image" class="error-base">{{ errors.image[0] }}</p>
                    </div>
                </div>

                <!-- === Publicatie === -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" v-model="form.published"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        {{ __('Publiceren op de website') }}
                    </label>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Concepten blijven zichtbaar in het dashboard, maar niet op de nieuwspagina.') }}
                    </p>
                    <p v-if="errors.published" class="error-base">{{ errors.published[0] }}</p>
                </div>
            </div>

            <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200">
                <button class="positive-button" :disabled="processing" @click="save">
                    {{ article ? __('Bijwerken') : __('Opslaan') }}
                </button>
                <button class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                        @click="close">
                    {{ __('Annuleren') }}
                </button>
            </div>
        </div>
    </div>
</template>
