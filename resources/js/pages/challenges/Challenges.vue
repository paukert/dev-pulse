<script setup lang="ts">
import { columns } from '@/components/challenges/columns';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Challenge, PaginatedResponse, SharedData, User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Challenges',
        href: '/challenges',
    },
];

interface Props {
    challenges: PaginatedResponse<Challenge>;
}

const props = defineProps<Props>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
</script>

<template>
    <Head title="Challenges" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-6">
            <div v-if="user.isAdmin" class="flex justify-end">
                <Link :href="route('challenges.create')" as="button">
                    <Button><Plus /> Add challenge</Button>
                </Link>
            </div>
            <div class="container mx-auto space-y-4 pb-10">
                <DataTable :columns="columns" :paginated-data="props.challenges" />
            </div>
        </div>
    </AppLayout>
</template>
