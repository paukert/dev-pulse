<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import RepositoryForm from '@/components/repositories/RepositoryForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, RepositoryForm as RepositoryFormType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repositories',
        href: '/repositories',
    },
    {
        title: 'Add repository',
        href: '#',
    },
];

interface Props {
    vcs_instances: { id: number; name: string }[];
}

const props = defineProps<Props>();

const form = useForm<RepositoryFormType>({
    name: '',
    sync_interval_hours: 4,
    vcs_id: null,
    vcs_instance_id: null,
    statistics_from: new Date().toISOString(),
});

const createRepository = () => {
    form.transform((data) => ({
        ...data,
        vcs_id: data.vcs_id?.value,
    })).post(route('repositories.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add repository" />

        <div class="flex p-4">
            <div class="mx-auto w-full py-10 sm:w-1/2">
                <Heading title="Add repository" />
                <form @submit.prevent="createRepository">
                    <RepositoryForm v-model:form="form" :is-new-record="true" :vcs_instances="props.vcs_instances">
                        <template #actions>
                            <Button :disabled="form.processing">Add repository</Button>
                        </template>
                    </RepositoryForm>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
