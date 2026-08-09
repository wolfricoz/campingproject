<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {computed} from "vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const form = useForm({
    email: '',
    title: '',
    message: '',
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

function submit() {
    form.post(route('contact.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="__('Contact Ons')"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <div class="bg-gray-50 px-4 py-10">
            <div class="mx-auto max-w-3xl">

                <div class="card-base">
                    <h1 class="title-class">{{ __('Contact Ons') }}</h1>
                    <p class="mt-1 text-center text-sm text-gray-500">
                        {{ __('Stel je vraag en we nemen zo snel mogelijk contact met je op.') }}
                    </p>

                    <!-- Algemene foutmelding wanneer er validatiefouten zijn -->
                    <div v-if="hasErrors"
                         class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-medium">{{ __('Er ging iets mis. Controleer de gemarkeerde velden:') }}</p>
                        <ul class="mt-1 list-inside list-disc">
                            <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
                        </ul>
                    </div>

                    <!-- Bevestiging na een succesvolle verzending -->
                    <div v-if="form.recentlySuccessful"
                         class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        {{ __('Bedankt voor je bericht! We nemen zo snel mogelijk contact met je op.') }}
                    </div>

                    <div class="mt-6 space-y-6">
                        <div>
                            <label class="label-base">{{ __('E-mail') }} <span class="text-red-600">*</span></label>
                            <input type="email" v-model="form.email" :placeholder="__('naam@voorbeeld.nl')"
                                   class="w-full input-base"/>
                            <p v-if="form.errors.email" class="error-base">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="label-base">{{ __('Onderwerp') }} <span class="text-red-600">*</span></label>
                            <input type="text" v-model="form.title" :placeholder="__('Waar gaat je vraag over?')"
                                   class="w-full input-base"/>
                            <p v-if="form.errors.title" class="error-base">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="label-base">{{ __('Bericht') }} <span class="text-red-600">*</span></label>
                            <textarea v-model="form.message" rows="6" :placeholder="__('Je bericht')"
                                      class="w-full input-base"></textarea>
                            <p v-if="form.errors.message" class="error-base">{{ form.errors.message }}</p>
                        </div>

                        <div class="flex flex-col border-t border-gray-100 pt-6">
                            <button type="button" @click="submit" :disabled="form.processing"
                                    class="w-full positive-button">
                                {{ __('Verstuur bericht') }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>
