<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head} from '@inertiajs/vue3';
import ArrangementCard from "@/Components/calendar/ArrangementCard.vue";
import {computed, ref} from "vue";
import ArrangementModal from "@/Components/calendar/ArrangementModal.vue";
import ArrangementListItem from "@/Components/calendar/ArrangementListItem.vue";

const props = defineProps({
    arrangements: {
        type: Array,
    }
});


// === Functions  ===
function getWeek(now) {
    let d = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    let dayNum = d.getDay() || 7;
    d.setDate(d.getDate() + 4 - dayNum);
    let yearStart = new Date(d.getFullYear(), 0, 1);
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

function getMonthFields() {
    let now = new Date();
    let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    let monthArray = {};
    for (let i = 0; i < lastDay.getDate(); i++) {
        let day = new Date(now.getFullYear(), now.getMonth(), i + 1);
        let week = getWeek(day)
        let dayNumber = (day.getDay() || 7) - 1
        if (monthArray[week] === undefined) {
            monthArray[week] = [];
        }
        monthArray[week][dayNumber] = {
            date: day,
            week: week,
            arrangements: []
        }
    }
    return monthArray;
}

function populateArrangements() {
    let days = getMonthFields();
    let weeks = Object.keys(days)
    // We go through all the arrangements and add the arrangements to the right days. o(nm)
    for (let i = 0; i < props.arrangements.length; i++) {
        const arrangement = props.arrangements[i]
        arrangement.start_date = new Date(arrangement.start_date)
        arrangement.end_date = new Date(arrangement.end_date)


        for (let j = 0; j < weeks.length; j++) {
            const week = weeks[j];
            for (let k = 0; k < days[week].length; k++) {
                const day = days[week][k];
                if (!day) {
                    continue;
                }
                const start = new Date(arrangement.start_date.getFullYear(), arrangement.start_date.getMonth(), arrangement.start_date.getDate());
                const end = new Date(arrangement.end_date.getFullYear(), arrangement.end_date.getMonth(), arrangement.end_date.getDate());
                if (day.date >= start && day.date <= end) {
                    day.arrangements.push(arrangement);
                    console.log('Adding new arrangement...');
                }
            }
        }
    }
    return days;
}

function onSave(data) {
    // Find the arrangement
    const arrangement = props.arrangements.find(a => a.id === data.id);
    if (!arrangement) {
        props.arrangements.push(data);
        showCreateModal.value = false;
        return;
    }

    // Update the arrangement
    arrangement.customer_id = data.customer_id ?? null;
    arrangement.location_id = data.location_id ?? null;
    arrangement.start_date = data.start_date ? new Date(data.start_date) : arrangement.start_date;
    arrangement.end_date = data.end_date ? new Date(data.end_date) : arrangement.end_date;
    arrangement.payment_received = data.payment_received ?? false;

    // Edit the customer fields with new data
    if (data.customer) {
        arrangement.customer = arrangement.customer ?? {};
        arrangement.customer.name = data.customer.name ?? '';
        arrangement.customer.email = data.customer.email ?? '';
        arrangement.customer.phone_number = data.customer.phone_number ?? '';
        arrangement.customer.street_name = data.customer.street_name ?? '';
        arrangement.customer.street_number = data.customer.street_number ?? '';
        arrangement.customer.postal_code = data.customer.postal_code ?? '';
        arrangement.customer.city = data.customer.city ?? '';
        arrangement.customer.country = data.customer.country ?? '';
        arrangement.customer.create_account = data.customer.create_account ?? false;
    }
    showCreateModal.value = false;

}

function onChangeStatus(data){
    const arrangement = props.arrangements.find(a => a.id === data.id);
    if(!arrangement) {
        console.log("failed to update status on arrangement");
    }
    arrangement.booking_status = data.status;

}

// === Data ===
const now = new Date();
const weekNumber = getWeek(now);
const monthName = new Date().toLocaleDateString('nl-NL', {month: 'long', year: 'numeric'});
const days = computed(() => populateArrangements());
const showCreateModal = ref(false);


</script>

<template>
    <Head title="Alle Reserveringen"/>
    <div v-if="showCreateModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20" >
        <arrangement-modal :show-modal="showCreateModal" @close="showCreateModal = false" @save="onSave">

        </arrangement-modal>
    </div>
    <AuthenticatedLayout>
        <div class="h-full w-full p-2 ">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="w-full">
                    <h1 class="text-2xl font-bold text-center">Alle Reserveringen</h1>
                </div>
                <div class="gap-2 flex flex-col p-6">
                    <ArrangementListItem v-for="arrangement in arrangements" :key="arrangement.id"  :arrangement="arrangement">

                    </ArrangementListItem>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>
