<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { columns } from '@/components/users/columns';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, PaginatedResponse, User } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

interface Props {
    users: PaginatedResponse<User>;
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="container mx-auto space-y-4 py-10">
                <DataTable :columns="columns" :paginated-data="props.users" />
            </div>
        </div>
    </AppLayout>
</template>
