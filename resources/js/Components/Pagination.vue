<script setup>
import {Link} from '@inertiajs/vue3';

// De links komen rechtstreeks uit de Laravel paginator (news.links).
defineProps({
    links: {type: Array, default: () => []},
});
</script>

<template>
    <!-- Minder dan 4 links betekent: vorige, één pagina, volgende. Dan is paginatie overbodig. -->
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span v-if="!link.url"
                  class="rounded-lg px-3 py-1.5 text-sm text-gray-400"
                  v-html="link.label"/>
            <Link v-else :href="link.url" preserve-scroll
                  class="rounded-lg px-3 py-1.5 text-sm transition-colors"
                  :class="link.active ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                  v-html="link.label"/>
        </template>
    </div>
</template>
