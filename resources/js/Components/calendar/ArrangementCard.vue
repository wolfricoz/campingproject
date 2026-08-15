<script setup>
import {ref} from "vue";
import arrangementModal from "@/Components/calendar/arrangementModal.vue";
import {formatDayPart} from "@/dayparts";


const emit = defineEmits(['save', 'changeStatus']);

const props = defineProps({
    arrangement: {
        type: Object,
        required: true
    },
    continuesLeft: {
        type: Boolean,
        default: false
    },
    continuesRight: {
        type: Boolean,
        default: false
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

</script>

<template>
    <div
        class="cursor-pointer overflow-hidden px-2 py-1 shadow-sm transition-colors hover:brightness-95"
        :class="[
        {
        'bg-gray-100 border-gray-400':      arrangement.booking_status === 'pending',
        'bg-orange-100 border-orange-500':  arrangement.booking_status === 'confirmed',
        'bg-emerald-100 border-emerald-500': arrangement.booking_status === 'checked-in',
        // Finished, cancelled and rejected records are hidden, but just incase I've added it here - if they show up we can easily spot them
        'bg-blue-100 border-blue-500':      arrangement.booking_status === 'finished',
        'bg-red-100 border-red-500':        arrangement.booking_status === 'cancelled',
        'bg-red-100 border-rose-500':      arrangement.booking_status === 'rejected',
        },

        continuesLeft ? 'pl-2' : 'ml-1 rounded-l-lg border-l-4',
        continuesRight ? '' : 'mr-1 rounded-r-lg',
    ]"
        :title="`${arrangement.location.name} — ${arrangement.customer.name}\n${formatDayPart(arrangement.start_date, {withTimes: true})} – ${formatDayPart(arrangement.end_date, {withTimes: true})}`"
        @click="showModal = true"
    >
        <template v-if="!continuesLeft">
            <h3 class="truncate whitespace-nowrap w-full text-sm font-medium text-gray-800">
                {{ arrangement.location.name }}
            </h3>
            <p class="truncate text-xs text-gray-600">{{ arrangement.customer.name }}</p>
            <!-- The calendar already shows the days, so we leave out the year here -->
            <div class="truncate text-[11px] text-gray-500">
                <span>{{ formatDayPart(arrangement.start_date, {withYear: false}) }}</span> –
                <span>{{ formatDayPart(arrangement.end_date, {withYear: false}) }}</span>
            </div>
        </template>
        <!-- Vervolg van vorige week: alleen de kleur, de gegevens staan al aan het begin. -->
        <p v-else class="truncate text-xs italic text-gray-600">
            {{ __('vervolg') }} — {{ arrangement.customer.name }}
        </p>
    </div>
    <Teleport to="body">
        <div v-if="showModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50" >
            <arrangement-modal :arrangement="arrangement" :show-modal="showModal" @close="showModal = false" @save="onSave" @change-status="onChangeStatus">

            </arrangement-modal>
        </div>
    </Teleport>
</template>

<style scoped>

</style>
