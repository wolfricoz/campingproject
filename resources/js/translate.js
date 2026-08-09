import { usePage } from '@inertiajs/vue3';

export function __(key, replacements = {}) {
    let line = usePage().props.translations?.[key] || key;

    Object.entries(replacements).forEach(([placeholder, value]) => {
        line = line.replace(`:${placeholder}`, value);
    })
    return line;
}


export function __choice(key, count, replacements = {}) {
    const [singular, plural = singular] = __(key).split('|');

    let line = count === 1 ? singular : plural;
    Object.entries({count, ...replacements}).forEach(([placeholder, value]) => {
        line = line.replace(`:${placeholder}`, value);
    })
    return line;
}
