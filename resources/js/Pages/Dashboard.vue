<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head} from '@inertiajs/vue3';
import ArrangementCard from "@/Components/calendar/ArrangementCard.vue";
import {computed} from "vue";

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
        console.warn('onSave: no arrangement found with id', data.id);
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


}

const now = new Date();
const weekNumber = getWeek(now);
const monthName = new Date().toLocaleDateString('nl-NL', {month: 'long', year: 'numeric'});


const days = computed(() => populateArrangements());



</script>

<template>
    <Head title="Dashboard"/>

    <AuthenticatedLayout>
        <div class="h-full w-full p-2">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="title-class">
                    Planning Dashboard<br>
                    <span class="text-lg underline">{{ monthName }}</span>
                </div>
                <div id="calendar" class="">
                    <table class="table border-gray-500 border rounded-lg w-full">
                        <thead>
                        <tr>
                            <th class="table-base">
                                Maandag
                            </th>
                            <th class="table-base">
                                Dinsdag
                            </th>
                            <th class="table-base">
                                Woensdag
                            </th>
                            <th class="table-base">
                                Donderdag
                            </th>
                            <th class="table-base">
                                Vrijdag
                            </th>
                            <th class="table-base">
                                Zaterdag
                            </th>
                            <th class="table-base">
                                Zondag
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(week, weekKey) in days" :key="weekKey">
                            <td v-for="col in 7" :key="col" class="table-base align-top"
                                :class="{
                                    'bg-gray-500': !week[col - 1],
                                    'bg-gray-200 itemrow': week[col - 1]
                                }"
                            >
                                <template v-if="week[col - 1]">
                                    <div class="flex flex-col gap-1.5 cursor-pointer">
                                        <div class="flex items-center justify-between text-gray-700 border-b border-gray-500 pb-1 mb-0.5">
                                            <span class="text-sm font-semibold">{{ week[col - 1].date.getDate() }}</span>
                                        </div>
                                        <ArrangementCard v-for="a in week[col - 1].arrangements" :key="a.id" :arrangement="a"
                                                         @save="onSave">
                                        </ArrangementCard>


                                    </div>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>
