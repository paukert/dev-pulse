import { h } from 'vue'
import { User } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import DataTableActions from '@/components/users/DataTableActions.vue';

export const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'id',
        header: () => h('div', { class: 'text-left' }, 'ID'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('id'))
        },
    },
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('name'))
        },
    },
    {
        accessorKey: 'email',
        header: () => h('div', { class: 'text-left' }, 'Email'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('email'))
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const user = row.original;
            return h('div', { class: 'relative text-right' }, h(DataTableActions, {
                user,
            }))
        },
    },
]
