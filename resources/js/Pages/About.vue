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
});

const values = [
    {title: 'Rust en ruimte', body: 'Onze cabines staan in een natuur gebied waar je niemand zal vinden om je heen, ideaal voor mensen.'},
    {title: 'Midden in de natuur', body:
            'We bieden meerdere natuurgebieden aan in meerdere landen, waaronder nederland, duitsland en belgie.'},
    {title: 'Persoonlijk contact', body:
            'Wanneer je bij onze camping aankomt dan zorgen we dat al je zorgen weg gaan! onze medewerkers helpen je van begin tot einde!'},
];
</script>

<template>
    <Head :title="__('Over Ons')"/>
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister">

        <!-- === Kop === -->
        <div class="relative h-72 w-full">
            <img src="/images/header.jpg" :alt="__('Uitzicht over de camping')"
                 class="absolute inset-0 h-full w-full object-cover object-center"/>
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-4 text-center">
                <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ __('Over Ons') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-white/90">
                    {{ __('Hoe is de syntec camping ontstaan?') }}
                </p>
            </div>
        </div>

        <!-- === Ons verhaal === -->
        <div class="bg-white px-4 py-12">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-2xl font-bold text-gray-800">{{ __('Ons verhaal') }}</h2>
                <div class="mt-4 space-y-4 text-sm leading-relaxed text-gray-600">
                    <!-- TODO Rico: hier komt het verhaal van de camping. -->
                    <p>{{ __('De Syntec Camping was opgericht door Martijn Babens Beulens rond 2006, waar hij zijn eerste cabine met de \
                        hand heeft gebouwd. Over de jaren is de camping erg gegroeid en elk jaar komen er nieuwe cabines bij! ')
                        }}</p>
                    <p>{{ __('Onze cabines zijn de standaard voor luxe en geavanceerde cabines; elke cabine is voorzien met een hoge \
                        aantal van technische wonderen zoals masseer stoelen, water bedden, en nog veel meer! En all dit voor een goede \
                        prijs!') }}</p>
                </div>
            </div>
        </div>

        <!-- === Waar we voor staan === -->
        <div class="bg-gray-50 px-4 py-12">
            <div class="mx-auto max-w-6xl">
                <h2 class="text-center text-2xl font-bold text-gray-800">{{ __('Waar we voor staan') }}</h2>

                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div v-for="value in values" :key="value.title"
                         class="rounded-2xl bg-white p-6 shadow-lg transition hover:shadow-xl">
                        <h3 class="text-base font-semibold text-gray-800">{{ __(value.title) }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ __(value.body) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- === Praktisch === -->
        <div class="bg-white px-4 py-12">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-2xl font-bold text-gray-800">{{ __('Praktische informatie') }}</h2>
                <dl class="mt-4 grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <!-- TODO Rico: de juiste gegevens invullen. -->
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <dt class="text-gray-500">{{ __('Adres') }}</dt>
                        <dd class="text-gray-800">Florijnstraat 20, 4879 AN Etten-Leur</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <dt class="text-gray-500">{{ __('Telefoonnummer') }}</dt>
                        <dd class="text-gray-800">033 711 5712</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <dt class="text-gray-500">{{ __('E-mail') }}</dt>
                        <dd class="text-gray-800">support@syntec-it.nl</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <dt class="text-gray-500">{{ __('Receptie') }}</dt>
                        <dd class="text-gray-800">receptie@syntec-camping.nl</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- === Doorverwijzing === -->
        <div class="bg-emerald-50 px-4 py-12">
            <div class="mx-auto flex max-w-3xl flex-col items-center gap-4 text-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ __('Zin gekregen?') }}</h2>
                <p class="text-sm text-gray-600">{{ __('Bekijk onze locaties of reserveer direct je plek.') }}</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <Link class="positive-button" :href="route('booking')">{{ __('Reserveer nu!') }}</Link>
                    <Link class="general-button" :href="route('locations')">{{ __('Bekijk locaties') }}</Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
