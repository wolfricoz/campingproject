<script setup>
import {ref} from "vue";
import arrangementModal from "@/Components/calendar/arrangementModal.vue";


const emit = defineEmits(['save']);

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
        class="rounded-lg px-2 py-1 text-center shadow-sm border-l-4 transition-colors hover:brightness-95"
        :class="{
            'bg-emerald-100 border-emerald-500': arrangement.booking_status === 'checked-in' && arrangement.payment_received,
            'bg-orange-100 border-orange-500': arrangement.booking_status === 'confirmed' && arrangement.payment_received,
            'bg-red-100 border-red-500': !arrangement.payment_received
        }"
        @click="showModal = true"
    >
        <h3 class="truncate whitespace-nowrap w-full text-sm font-medium text-gray-800">
            {{ arrangement.location.name }}
        </h3>
        <p class="truncate text-xs text-gray-600">{{ arrangement.customer.name }}</p>
        <div class="mt-0.5 text-[11px] text-gray-500">
            <span>{{ formatDate(arrangement.start_date) }}</span> – <span>{{ formatDate(arrangement.end_date) }}</span>
        </div>
    </div>
    <div v-if="showModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20" >
        <arrangement-modal :arrangement="arrangement" :show-modal="showModal" @close="showModal = false" @save="onSave">

        </arrangement-modal>
    </div>
</template>

<style scoped>

</style>
