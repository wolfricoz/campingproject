<script setup>
import AuthenticatedLayout from '@/Layouts/DashboardLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import {computed, ref, watch} from "vue";
import ArrangementModal from "@/Components/calendar/ArrangementModal.vue";
import ArrangementListItem from "@/Components/calendar/ArrangementListItem.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    arrangements: {
        type: [Array, Object],
        default: () => ({data: [], links: []}),
    },
    // The search and sorting the server used, so the toolbar keeps showing what you picked
    filters: {
        type: Object,
        default: () => ({}),
    },
    status: {
        type: String,
        default: null,
    }
});


// === Functions  ===
function onSave(data) {
    // Find the arrangement
    const arrangement = rows.value.find(a => a.id === data.id);
    if (!arrangement) {
        rows.value.push(data);
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
    const arrangement = rows.value.find(a => a.id === data.id);
    if(!arrangement) {
        console.log("failed to update status on arrangement");
    }
    arrangement.booking_status = data.status;

}

// Searching, sorting and paging all happen on the server, so we just ask for a new page. The page
// number is left out on purpose, a new search should start at the first page again
function applyFilters(extra = {}) {
    router.get(route('arrangement.index', selectedStatus.value || undefined), {
        search: search.value || undefined,
        sort: sort.value,
        direction: direction.value,
        ...extra,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function toggleDirection() {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    applyFilters();
}

function resetFilters() {
    search.value = '';
    sort.value = 'start_date';
    direction.value = 'desc';
    selectedStatus.value = '';
    applyFilters();
}

// === Data ===
const showCreateModal = ref(false);

const search = ref(props.filters.search ?? '');
const sort = ref(props.filters.sort ?? 'start_date');
const direction = ref(props.filters.direction ?? 'desc');
// The status sits in the url (/dashboard/arrangements/pending), so we can read it from the route
const selectedStatus = ref(props.status ?? route().params.status ?? '');

const statuses = ['pending', 'confirmed', 'checked-in', 'finished', 'cancelled', 'rejected'];

const sortOptions = [
    {value: 'start_date', label: 'Aankomst'},
    {value: 'end_date', label: 'Vertrek'},
    {value: 'customer', label: 'Klant'},
    {value: 'location', label: 'Locatie'},
    {value: 'total_price', label: 'Prijs'},
    {value: 'created_at', label: 'Aangemaakt op'},
];

// The reservations come in as a paginator, but as long as the backend still sends a plain list we
// read that as one page without links
const rows = computed(() => Array.isArray(props.arrangements)
    ? props.arrangements
    : props.arrangements.data ?? []);

const paginationLinks = computed(() => (Array.isArray(props.arrangements)
    ? []
    : props.arrangements.links ?? []));

const total = computed(() => (Array.isArray(props.arrangements)
    ? props.arrangements.length
    : props.arrangements.total ?? rows.value.length));

const hasFilters = computed(() => !!search.value || !!selectedStatus.value
    || sort.value !== 'start_date' || direction.value !== 'desc');

// We wait a moment while typing, otherwise every letter is a request
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 300);
});

</script>

<template>
    <Head :title="__('Alle reserveringen')"/>
    <div v-if="showCreateModal" class="flex justify-center items-center fixed top-0 left-0 w-full h-full bg-black/20 z-50" >
        <arrangement-modal :show-modal="showCreateModal" @close="showCreateModal = false" @save="onSave">

        </arrangement-modal>
    </div>
    <AuthenticatedLayout>
        <div class="h-full w-full p-2 ">
            <section class="border-gray-50 border rounded-lg bg-gray-50">
                <div class="w-full">
                    <h1 class="text-2xl font-bold text-center">{{ __('Alle reserveringen') }}</h1>
                </div>

                <!-- Searching, filtering and sorting, the front desk uses this the whole day -->
                <div class="flex flex-wrap items-end gap-3 px-6 pt-4">
                    <div class="min-w-64 flex-1">
                        <label class="label-base">{{ __('Zoeken') }}</label>
                        <input type="search" v-model="search"
                               :placeholder="__('Naam, e-mail of locatie')"
                               class="w-full input-base"/>
                    </div>

                    <div>
                        <label class="label-base">{{ __('Status') }}</label>
                        <select v-model="selectedStatus" class="input-base" @change="applyFilters()">
                            <option value="">{{ __('Alle statussen') }}</option>
                            <option v-for="option in statuses" :key="option" :value="option">
                                {{ __(option) }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="label-base">{{ __('Sorteren op') }}</label>
                        <div class="flex gap-2">
                            <select v-model="sort" class="input-base" @change="applyFilters()">
                                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                    {{ __(option.label) }}
                                </option>
                            </select>
                            <button type="button" class="general-button"
                                    :title="direction === 'asc' ? __('Oplopend') : __('Aflopend')"
                                    @click="toggleDirection">
                                {{ direction === 'asc' ? '↑' : '↓' }}
                            </button>
                        </div>
                    </div>

                    <button v-if="hasFilters" type="button" class="negative-button" @click="resetFilters">
                        {{ __('Wissen') }}
                    </button>
                </div>

                <div class="gap-2 flex flex-col p-6">
                    <p class="text-xs text-gray-500">
                        {{ __choice(':count reservering|:count reserveringen', total) }}
                    </p>

                    <p v-if="!rows.length" class="p-4 text-sm text-gray-500 text-center">
                        {{ __('Geen reserveringen gevonden.') }}
                    </p>

                    <ArrangementListItem v-for="arrangement in rows" :key="arrangement.id" :arrangement="arrangement"
                                         @save="onSave" @change-status="onChangeStatus">

                    </ArrangementListItem>

                    <Pagination :links="paginationLinks" class="mt-4"/>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>
