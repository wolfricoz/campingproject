<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const base =
    'w-full flex items-center gap-3 px-3 py-2 text-sm transition-colors duration-150 focus:outline-none';

const classes = computed(() =>
    props.active
        ? `${base} bg-gray-100 text-gray-900 font-medium border-l border-blue-500 rounded-none rounded-r-xl`
        : `${base} text-gray-500 font-medium hover:bg-gray-50 hover:text-gray-900 rounded-none rounded-r-xl`,
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
