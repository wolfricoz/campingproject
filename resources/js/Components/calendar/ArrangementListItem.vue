<script setup>
import {ref} from "vue";
import arrangementModal from "@/Components/calendar/arrangementModal.vue";


const emit = defineEmits(['save', 'changeStatus']);

const props = defineProps({
    arrangement: {
        type: Object,
        required: true
    }
})

let arrangement = props.arrangement;

function onSave(data){
    // Update the arrangement with the data from the modal
    arrangement.id = data.id ?? 0;
    arrangement.customer_id = data.customer_id ?? null;
    arrangement.location_id = data.location_id ?? null;
    arrangement.start_date = data.start_date ?? '';
    arrangement.end_date = data.end_date ?? '';
    arrangement.payment_received = data.payment_received ?? false;

    // editable customer fields
    arrangement.customer.name = data.customer?.name ?? '';
    arrangement.customer.email = data.customer?.email ?? '';
    arrangement.customer.phone_number = data.customer?.phone_number ?? '';
    arrangement.customer.street_name = data.customer?.street_name ?? '';
    arrangement.customer.street_number = data.customer?.street_number ?? '';
    arrangement.customer.postal_code = data.customer?.postal_code ?? '';
    arrangement.customer.city = data.customer?.city ?? '';
    arrangement.customer.country = data.customer?.country ?? '';
    arrangement.customer.create_account = data.customer?.create_account ?? false;
    emit('save', { ...data });
    showModal.value = false;

}
function onChangeStatus(data){
    arrangement.status = data.status;
    emit('changeStatus', { ...data });
}

let showModal = ref(false);

function formatDate(date) {
    return new Date(date).toLocaleString('nl-NL', {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',

    })
}

</script>

<template>
    <div
        class="rounded-lg px-3 py-2 shadow-sm border-l-4 transition-colors hover:brightness-95 cursor-pointer"
        :class="{
            'bg-gray-100 border-gray-400':       arrangement.booking_status === 'pending',
            'bg-orange-100 border-orange-500':   arrangement.booking_status === 'confirmed',
            'bg-emerald-100 border-emerald-500': arrangement.booking_status === 'checked-in',
            'bg-blue-100 border-blue-500':       arrangement.booking_status === 'finished',
            'bg-red-100 border-red-500':         arrangement.booking_status === 'cancelled',
            'bg-red-100 border-rose-500':        arrangement.booking_status === 'rejected',
        }"
        @click="showModal = true"
    >
        <div class="flex items-stretch gap-4 divide-x divide-black/10">
            <!-- Location -->
            <div class="min-w-0 flex-1">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Locatie</p>
                <h3 class="truncate text-sm font-semibold text-gray-800">{{ arrangement.location?.name }}</h3>
                <p class="truncate text-[11px] text-gray-500">
                    {{ arrangement.location?.type }} · {{ arrangement.location?.capacity }} pers · {{ arrangement.location?.bedrooms }} slk
                </p>
            </div>

            <!-- Customer -->
            <div class="min-w-0 flex-1 pl-4">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Klant</p>
                <p class="truncate text-sm font-medium text-gray-700">{{ arrangement.customer?.name }}</p>
                <p class="truncate text-[11px] text-gray-500">{{ arrangement.customer?.email }}</p>
                <p class="truncate text-[11px] text-gray-500">
                    {{ arrangement.customer?.phone_number }} · {{ arrangement.customer?.city }}
                </p>
            </div>

            <!-- Dates -->
            <div class="min-w-0 flex-1 pl-4">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Periode</p>
                <p class="truncate text-[11px] text-gray-600">{{ formatDate(arrangement.start_date) }}</p>
                <p class="truncate text-[11px] text-gray-600">{{ formatDate(arrangement.end_date) }}</p>
            </div>

            <!-- Status / price / payment -->
            <div class="min-w-0 flex-1 pl-4 flex flex-col items-start gap-1">
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white/60 text-gray-700 capitalize">
                    {{ arrangement.booking_status }}
                </span>
                <span class="text-sm font-semibold text-gray-800">€ {{ arrangement.total_price }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                      :class="arrangement.payment_received ? 'bg-emerald-200 text-emerald-800' : 'bg-red-200 text-red-800'">
                    {{ arrangement.payment_received ? 'Betaald' : 'Niet betaald' }}
                </span>
            </div>
        </div>
    </div>

    <div v-if="showModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20">
        <arrangement-modal :arrangement="arrangement" :show-modal="showModal"
                           @close="showModal = false" @save="onSave" @change-status="onChangeStatus" />
    </div>
</template>

<style scoped>

</style>
