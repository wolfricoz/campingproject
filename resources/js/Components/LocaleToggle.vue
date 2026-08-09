<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {computed} from 'vue';

const locales = ['nl', 'en'];

const currentLocale = computed(() => usePage().props.locale ?? 'nl');
</script>

<template>
    <!-- Zolang de locale.update route nog niet bestaat tonen we de toggle niet; anders
         gooit Ziggy een error die het renderen van de hele pagina afbreekt.
         De kleur wordt geërfd van de plek waar de toggle staat, zodat hij zowel op de
         blauwe navbar als op de witte sidebar leesbaar blijft. -->
    <div v-if="route().has('locale.update')" class="flex items-center gap-1 text-xs font-medium uppercase">
        <Link v-for="locale in locales" :key="locale"
              :href="route('locale.update', locale)" method="post" as="button"
              preserve-scroll
              class="px-1 transition-opacity"
              :class="locale === currentLocale ? 'underline underline-offset-4' : 'opacity-60 hover:opacity-100'"
        >
            {{ locale }}
        </Link>
    </div>
</template>
