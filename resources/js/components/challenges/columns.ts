import DataTableActions from '@/components/DataTableActions.vue';
import { type Challenge } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { truncate } from 'lodash-es';
import { h } from 'vue';

function formatDateTime(value: string) {
    return new Date(value).toLocaleString('en-GB');
}

export const columns: ColumnDef<Challenge>[] = [
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
            const name: string = row.getValue('name');
            return h('div', { class: 'text-left font-medium' }, truncate(name, { length: 35 }));
        },
    },
    {
        accessorKey: 'description',
        header: () => h('div', { class: 'text-left' }, 'Description'),
        cell: ({ row }) => {
            const description: string = row.getValue('description') ?? '';
            return h('div', { class: 'text-left font-medium' }, truncate(description, { length: 75 }));
        },
    },
    {
        accessorKey: 'active_from',
        header: () => h('div', { class: 'text-left' }, 'Active from'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDateTime(row.getValue('active_from')));
        },
    },
    {
        accessorKey: 'active_to',
        header: () => h('div', { class: 'text-left' }, 'Active to'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDateTime(row.getValue('active_to')));
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const challenge = row.original;
            return h(
                'div',
                { class: 'relative text-right' },
                h(DataTableActions, {
                    deleteConfirmationTitle: `Do you really want to delete challenge ${challenge.name}?`,
                    deleteConfirmationMessage: `This action cannot be undone. This will permanently delete challenge ${challenge.name} and all associated data.`,
                    deleteUrl: route('challenges.destroy', { challenge: challenge.id }),
                    editUrl: route('challenges.edit', { challenge: challenge.id }),
                }),
            );
        },
    },
];
