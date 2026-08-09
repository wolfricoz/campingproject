<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head :title="__('Profile')" />

    <GuestLayout>
        <div class="bg-gray-50 py-12">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Profile') }}
                </h2>

                <div class="card-base">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="card-base">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="card-base">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
