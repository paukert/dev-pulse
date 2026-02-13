import DataTableActions from '@/components/DataTableActions.vue';
import Help from '@/components/Help.vue';
import { type Challenge } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { truncate } from 'lodash-es';
import { CircleCheckBig, CircleDashed, CircleX, Clock, LucideIcon } from 'lucide-vue-next';
import { h } from 'vue';

function formatDateTime(value: string) {
    return new Date(value).toLocaleString('en-GB');
}

const classesMap: Record<string, string> = {
    active: 'text-blue-500',
    completed: 'text-green-500',
    expired: 'text-gray-500',
    upcoming: 'text-gray-500',
};

const iconMap: Record<string, LucideIcon> = {
    active: CircleDashed,
    completed: CircleCheckBig,
    expired: CircleX,
    upcoming: Clock,
};

const titleMap: Record<string, string> = {
    active: 'Currently ongoing',
    completed: 'Successfully completed',
    expired: 'Expired',
    upcoming: 'Starting soon',
};

export const columns: ColumnDef<Challenge>[] = [
    {
        accessorKey: 'state',
        meta: { cellClass: 'pr-1 h-14 w-[50px]' },
        header: '',
        cell: ({ row }) => {
            const state: string = row.getValue('state');
            return h(
                Help,
                { tooltip: titleMap[state] },
                { icon: () => h(iconMap[state], { size: 16, class: `ml-auto block ${classesMap[state]}` }) },
            );
        },
    },
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
