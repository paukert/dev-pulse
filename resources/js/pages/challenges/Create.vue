<script setup lang="ts">
import ChallengeForm from '@/components/challenges/ChallengeForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ActivityForm, BreadcrumbItem, ChallengeForm as ChallengeFormType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { getLocalTimeZone, today } from '@internationalized/date';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Challenges',
        href: '/challenges',
    },
    {
        title: 'Create challenge',
        href: '#',
    },
];

interface Props {
    supportedActivityTypes: Record<string, string>;
}

defineProps<Props>();

const now = today(getLocalTimeZone());
const tomorrow = now.add({ days: 1 }).toString();
const nextWeek = now.add({ weeks: 1 }).toString();

const form = useForm<ChallengeFormType>({
    name: '',
    description: '',
    active_from: tomorrow,
    active_to: nextWeek,
    activities: [{ id: Date.now(), type: '', needed_actions_count: 5, isNewRecord: true }],
});

const createChallenge = () => {
    form.transform((data) => ({
        ...data,
        activities: data.activities.map((activity: ActivityForm) => {
            const { id, isNewRecord, ...rest } = activity;
            return isNewRecord ? rest : { id, ...rest };
        }),
    })).post(route('challenges.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create challenge" />

        <div class="flex p-4">
            <div class="mx-auto w-full py-10 sm:w-1/2">
                <Heading title="Create challenge" />
                <form @submit.prevent="createChallenge">
                    <ChallengeForm v-model:form="form" :supported-activity-types="supportedActivityTypes">
                        <template #actions>
                            <Button :disabled="form.processing">Create challenge</Button>
                        </template>
                    </ChallengeForm>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
