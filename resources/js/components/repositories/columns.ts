import DataTableActions from '@/components/DataTableActions.vue';
import { Repository } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

function formatDateTime(value: string | null) {
    return value === null ? 'Never' : new Date(value).toLocaleDateString('en-GB');
}

export const columns: ColumnDef<Repository>[] = [
    {
        accessorKey: 'id',
        header: () => h('div', { class: 'text-left' }, 'ID'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('id'));
        },
    },
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('name'));
        },
    },
    {
        accessorKey: 'statistics_from',
        header: () => h('div', { class: 'text-left' }, 'Statistics from'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDateTime(row.getValue('statistics_from')));
        },
    },
    {
        accessorKey: 'sync_interval_hours',
        header: () => h('div', { class: 'text-left' }, 'Sync interval'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('sync_interval_hours') + ' hour(s)');
        },
    },
    {
        accessorKey: 'last_synced_at',
        header: () => h('div', { class: 'text-left' }, 'Last sync'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDateTime(row.getValue('last_synced_at')));
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const repository = row.original;
            return h(
                'div',
                { class: 'relative text-right' },
                h(DataTableActions, {
                    deleteConfirmationTitle: `Do you really want to delete repository ${repository.name}?`,
                    deleteConfirmationMessage: `This action cannot be undone. This will permanently delete repository ${repository.name} and all associated data.`,
                    deleteUrl: route('repositories.destroy', { repository: repository.id }),
                    editUrl: route('repositories.edit', { repository: repository.id }),
                }),
            );
        },
    },
];
