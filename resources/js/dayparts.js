import {usePage} from '@inertiajs/vue3';
import {__} from '@/translate';

// A reservation is saved with an exact time, but that time doesn't tell the front desk anything.
// So instead of the time we show the daypart it falls in.
// Daypart 1 runs from 00:00 to 11:00, this is when the guests have to check out.
// Daypart 2 runs from 11:00 to 24:00.
export const DAYPART_BOUNDARY_HOUR = 11;

export const FIRST_DAYPART = 1;

export const SECOND_DAYPART = 2;

// We read the date and the clock from the value itself, without converting it to the timezone of
// the browser, so we show the daypart that was really filled in
function toWallClock(value) {
    if (value instanceof Date) {
        return value;
    }

    const parts = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
    if (!parts) {
        return new Date(value);
    }

    const [, year, month, day, hour, minute] = parts.map(Number);

    return new Date(year, month - 1, day, hour, minute);
}

// The daypart a date time falls in
export function dayPartOf(value) {
    if (!value) {
        return null;
    }

    return toWallClock(value).getHours() < DAYPART_BOUNDARY_HOUR ? FIRST_DAYPART : SECOND_DAYPART;
}

// Short label, for the bars in the calendar
export function dayPartLabel(part) {
    return part === FIRST_DAYPART ? __('Dagdeel 1') : __('Dagdeel 2');
}

// Full label with the times in it, for the places that have more room
export function dayPartLabelWithTimes(part) {
    return part === FIRST_DAYPART ? __('Dagdeel 1 (voor 11:00)') : __('Dagdeel 2 (vanaf 11:00)');
}

// The date without the time, in the language of the user
export function formatDay(value, options = {}) {
    if (!value) {
        return '—';
    }

    const {month = 'numeric', withYear = true} = options;
    const locale = usePage().props.locale === 'en' ? 'en-GB' : 'nl-NL';

    return toWallClock(value).toLocaleDateString(locale, {
        day: 'numeric',
        month,
        ...(withYear ? {year: 'numeric'} : {}),
    });
}

// The date with the daypart behind it, for example "15-8-2026 · Dagdeel 2"
export function formatDayPart(value, options = {}) {
    if (!value) {
        return '—';
    }

    const part = dayPartOf(value);
    const label = options.withTimes ? dayPartLabelWithTimes(part) : dayPartLabel(part);

    return `${formatDay(value, options)} · ${label}`;
}
