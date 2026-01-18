<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import RepositoryForm from '@/components/repositories/RepositoryForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Repository, RepositoryForm as RepositoryFormType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    repository: Repository;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repositories',
        href: '/repositories',
    },
    {
        title: props.repository.name,
        href: '#',
    },
];

const form = useForm<RepositoryFormType>({
    name: props.repository.name,
    sync_interval_hours: props.repository.sync_interval_hours,
});

const updateRepository = () => {
    form.patch(route('repositories.update', { repository: props.repository.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit repository ${props.repository.name}`" />

        <div class="flex p-4">
            <div class="mx-auto w-full py-10 sm:w-1/2">
                <Heading title="Edit repository" />
                <form @submit.prevent="updateRepository">
                    <RepositoryForm v-model:form="form">
                        <template #actions>
                            <Button :disabled="form.processing">Edit repository</Button>
                        </template>
                    </RepositoryForm>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
