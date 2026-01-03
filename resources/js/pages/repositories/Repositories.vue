<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { columns } from '@/components/repositories/columns';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, PaginatedResponse, Repository } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repositories',
        href: '/repositories',
    },
];

interface Props {
    repositories: PaginatedResponse<Repository>;
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Repositories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="container mx-auto space-y-4 py-10">
                <DataTable :columns="columns" :paginated-data="props.repositories" />
            </div>
        </div>
    </AppLayout>
</template>
