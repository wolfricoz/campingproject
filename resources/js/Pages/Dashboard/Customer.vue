<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    reservations: {
        type: Array,
    }
});

const statusColors = {
    'pending': 'bg-gray-100 text-gray-700',
    'confirmed': 'bg-orange-100 text-orange-700',
    'checked-in': 'bg-emerald-100 text-emerald-700',
    'finished': 'bg-blue-100 text-blue-700',
    'cancelled': 'bg-red-100 text-red-700',
    'rejected': 'bg-rose-100 text-rose-700',
};

/**
 * Een reservering is te betalen zolang hij nog openstaat; een geannuleerde of afgewezen
 * reservering hoeft de klant uiteraard niet meer te betalen.
 */
function isPayable(reservation) {
    return !reservation.payment_received
        && !['cancelled', 'rejected'].includes(reservation.booking_status);
}

function formatDate(date) {
    // De datumnotatie volgt de gekozen taal mee.
    const locale = usePage().props.locale === 'en' ? 'en-GB' : 'nl-NL';

    return new Date(date).toLocaleString(locale, {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="__('Reserveringen')" />
    <GuestLayout :canLogin="canLogin" :canRegister="canRegister"  >
        <div class="px-4 py-10">
            <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-lg sm:p-8 h-[100%]">
                <h1 class="title-class">{{ __('Reserveringen') }}</h1>

                <div class="mt-6 flex flex-col divide-y divide-gray-200 border border-gray-200 rounded-lg">

                    <p v-if="reservations.length < 1" class="p-4 text-sm text-gray-500 text-center">{{ __('Je hebt nog geen reserveringen.') }}</p>
                    <div v-else v-for="reservation in reservations" :key="reservation.id"
                         class="flex items-center justify-between gap-4 p-4">
                        <div class="min-w-0">
                            <h2 class="truncate text-sm font-semibold text-gray-800">{{ reservation.location?.name }}</h2>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(reservation.start_date) }} — {{ formatDate(reservation.end_date) }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="rounded-full px-2 py-0.5 text-xs capitalize"
                                  :class="statusColors[reservation.booking_status]">
                                {{ __(reservation.booking_status) }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">
                                {{ reservation.total_price ? '€ ' + reservation.total_price : '—' }}
                            </span>
                            <Link v-if="isPayable(reservation)" :href="route('payment', reservation.guid)"
                                  class="href-class text-sm">
                                {{ __('Betalen') }}
                            </Link>
                            <span v-else-if="reservation.payment_received"
                                  class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">
                                &check; {{ __('Betaald') }}
                            </span>
                        </div>
                    </div>

                </div>

                <Link :href="route('booking')" class="href-class mt-4 inline-block">
                    {{ __('Maak een nieuwe reservering!') }}
                </Link>
            </div>
        </div>
    </GuestLayout>

</template>
