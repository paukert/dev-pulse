<script setup lang="ts">
import { columns } from '@/components/users/columns';
import DataTable from '@/components/DataTable.vue';
import { type BreadcrumbItem, PaginatedResponse, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

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
            <div class="container mx-auto py-10 space-y-4">
                <DataTable :columns="columns" :paginated-data="props.users" />
            </div>
        </div>
    </AppLayout>
</template>
