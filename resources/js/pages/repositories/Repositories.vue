<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { columns } from '@/components/repositories/columns';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, PaginatedResponse, Repository } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-6">
            <div class="flex justify-end gap-2">
                <Link :href="route('repositories.create')" as="button">
                    <Button><Plus /> Add repository</Button>
                </Link>
                <Link :href="route('vcs-instances.create')" as="button">
                    <Button><Plus /> Add VCS instance</Button>
                </Link>
            </div>
            <div class="container mx-auto space-y-4 pb-10">
                <DataTable :columns="columns" :paginated-data="props.repositories" />
            </div>
        </div>
    </AppLayout>
</template>
