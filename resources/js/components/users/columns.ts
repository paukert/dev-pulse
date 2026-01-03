import DataTableActions from '@/components/DataTableActions.vue';
import { User } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export const columns: ColumnDef<User>[] = [
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
        accessorKey: 'email',
        header: () => h('div', { class: 'text-left' }, 'Email'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('email'));
        },
    },
    {
        accessorKey: 'role',
        header: () => h('div', { class: 'text-left' }, 'Role'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('role'));
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const user = row.original;
            return h(
                'div',
                { class: 'relative text-right' },
                h(DataTableActions, {
                    deleteConfirmationTitle: `Do you really want to delete user ${user.name}?`,
                    deleteConfirmationMessage: `This action cannot be undone. This will permanently delete user ${user.name} and all of his data.`,
                    deleteUrl: route('users.destroy', { id: user.id }),
                    editUrl: route('users.edit', { id: user.id }),
                }),
            );
        },
    },
];
