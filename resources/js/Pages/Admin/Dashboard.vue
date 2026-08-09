<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head, router, usePage} from '@inertiajs/vue3';
import ArrangementCard from "@/Components/calendar/ArrangementCard.vue";
import {computed, ref} from "vue";
import ArrangementModal from "@/Components/calendar/ArrangementModal.vue";

const props = defineProps({
    arrangements: {
        type: Array,
    },
    month: {
        type: String,
        default: null,
    }
});


// === Functions  ===
function getMonthWeeks(reference) {
    const year = reference.getFullYear();
    const monthIndex = reference.getMonth();
    const dayCount = new Date(year, monthIndex + 1, 0).getDate();

    const weeks = [];
    let week = null;

    for (let dayNumber = 1; dayNumber <= dayCount; dayNumber++) {
        const date = new Date(year, monthIndex, dayNumber);
        const col = (date.getDay() || 7) - 1; // maandag = 0, zondag = 6

        if (!week || col === 0) {
            week = new Array(7).fill(null);
            weeks.push(week);
        }
        week[col] = {date};
    }

    return weeks;
}

function toDayStart(value) {
    const date = new Date(value);
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

function assignLanes(segments) {
    segments.sort((a, b) => a.firstCol - b.firstCol || a.arrangement.id - b.arrangement.id);

    const lastColumnPerLane = [];
    for (const segment of segments) {
        let lane = lastColumnPerLane.findIndex((lastCol) => lastCol < segment.firstCol);
        if (lane === -1) {
            lane = lastColumnPerLane.length;
        }
        lastColumnPerLane[lane] = segment.firstCol + segment.span - 1;
        segment.lane = lane;
    }

    return lastColumnPerLane.length;
}

function buildWeeks() {
    return getMonthWeeks(referenceDate.value).map((slots, weekIndex) => {
        const segments = [];

        for (const arrangement of props.arrangements) {
            const start = toDayStart(arrangement.start_date);
            const end = toDayStart(arrangement.end_date);

            let firstCol = null;
            let lastCol = null;
            for (let col = 0; col < 7; col++) {
                const slot = slots[col];
                if (!slot) {
                    continue;
                }
                if (slot.date >= start && slot.date <= end) {
                    if (firstCol === null) {
                        firstCol = col;
                    }
                    lastCol = col;
                }
            }

            if (firstCol === null) {
                continue;
            }

            segments.push({
                arrangement,
                firstCol,
                span: lastCol - firstCol + 1,
                continuesLeft: start < slots[firstCol].date,
                continuesRight: end > slots[lastCol].date,
                lane: 0,
            });
        }

        return {
            key: weekIndex,
            slots,
            segments,
            laneCount: assignLanes(segments),
        };
    });
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

function toMonthParam(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

const referenceDate = computed(() => {
    if (!props.month) {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), 1);
    }
    const [year, month] = props.month.split('-').map(Number);
    return new Date(year, month - 1, 1);
});

const isCurrentMonth = computed(() => toMonthParam(referenceDate.value) === toMonthParam(new Date()));

function showMonth(date) {
    router.get(route('dashboard'), {month: toMonthParam(date)}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function shiftMonth(offset) {
    showMonth(new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth() + offset, 1));
}

function showCurrentMonth() {
    showMonth(new Date());
}

// === Data ===
const monthName = computed(() => referenceDate.value.toLocaleDateString(
    usePage().props.locale === 'en' ? 'en-GB' : 'nl-NL',
    {month: 'long', year: 'numeric'}
));
const weeks = computed(() => buildWeeks());
const showCreateModal = ref(false);

const weekdayNames = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];


</script>

<template>
    <Head :title="__('Dashboard')"/>
    <div v-if="showCreateModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50" >
        <arrangement-modal :show-modal="showCreateModal" @close="showCreateModal = false" @save="onSave">

        </arrangement-modal>
    </div>
    <AuthenticatedLayout>
        <div class="h-full w-full p-2">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="title-class">
                    {{ __('Planning Dashboard') }}

                    <div class="flex flex-wrap items-center justify-center gap-2 mt-2">
                        <button type="button"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-lg leading-none text-gray-700 hover:bg-gray-100"
                                :aria-label="__('Vorige maand')"
                                @click="shiftMonth(-1)">
                            &lsaquo;
                        </button>
                        <span class="text-lg underline min-w-48 capitalize">{{ monthName }}</span>
                        <button type="button"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-lg leading-none text-gray-700 hover:bg-gray-100"
                                :aria-label="__('Volgende maand')"
                                @click="shiftMonth(1)">
                            &rsaquo;
                        </button>
                        <button type="button"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="isCurrentMonth"
                                @click="showCurrentMonth">
                            {{ __('Deze maand') }}
                        </button>
                    </div>

                    <div id="actions" class="flex justify-end px-4 py-2 text-sm text-gray-700">
                        <button class="positive-button" @click="showCreateModal = true" @change-status="onChangeStatus">
                            {{ __('Maak nieuwe reservering') }}
                        </button>
                    </div>

                </div>
                <div id="calendar" class="overflow-x-auto">
                    <div class="min-w-[52rem] border border-gray-500 rounded-lg overflow-hidden">
                        <div class="grid grid-cols-7">
                            <div v-for="name in weekdayNames" :key="name" class="table-base font-semibold text-center">
                                {{ __(name) }}
                            </div>
                        </div>

                        <!-- Per week een eigen grid: de dagen vormen de kolommen, de reserveringen
                             liggen er als balken overheen en lopen dus door over meerdere dagen. -->
                        <div v-for="week in weeks" :key="week.key" class="grid grid-cols-7">

                            <!-- De dagvakjes liggen onderop en spannen alle rijen, zodat de balken
                                 er dwars overheen kunnen lopen. Bewust zonder z-index: de dagnummers
                                 en balken staan later in de DOM en tekenen daardoor al bovenop.
                                 Een z-index hier zou een stacking context maken die de modal opsluit. -->
                            <div v-for="col in 7" :key="`bg-${col}`"
                                 class="min-h-[6rem] border border-gray-200"
                                 :class="{
                                     'bg-gray-500': !week.slots[col - 1],
                                     'bg-gray-300': week.slots[col - 1] && col % 2 === 1,
                                     'bg-gray-200': week.slots[col - 1] && col % 2 === 0,
                                 }"
                                 :style="{gridColumn: col, gridRow: `1 / span ${week.laneCount + 1}`}"
                            ></div>

                            <div v-for="col in 7" :key="`num-${col}`"
                                 class="px-2 pt-2 pb-1"
                                 :style="{gridColumn: col, gridRow: 1}">
                                <span v-if="week.slots[col - 1]"
                                      class="block text-sm font-semibold text-gray-700 border-b border-gray-500 pb-1">
                                    {{ week.slots[col - 1].date.getDate() }}
                                </span>
                            </div>

                            <div v-for="segment in week.segments"
                                 :key="segment.arrangement.id"
                                 class="mb-1 min-w-0"
                                 :style="{
                                     gridColumn: `${segment.firstCol + 1} / span ${segment.span}`,
                                     gridRow: segment.lane + 2,
                                 }">
                                <ArrangementCard :arrangement="segment.arrangement"
                                                 :continues-left="segment.continuesLeft"
                                                 :continues-right="segment.continuesRight"
                                                 @save="onSave" @change-status="onChangeStatus">
                                </ArrangementCard>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>
