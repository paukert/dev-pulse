<script setup lang="ts" generic="TData, TValue">
import DataTablePaginator from '@/components/DataTablePaginator.vue';
import { PaginatedResponse } from '@/types';
import { router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { ref } from 'vue';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    paginatedData: PaginatedResponse<TData>;
}>();

const pagination = ref({
    pageIndex: props.paginatedData.current_page - 1,
    pageSize: props.paginatedData.per_page,
});

const table = useVueTable({
    get data() {
        return props.paginatedData.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: props.paginatedData.last_page,
    onPaginationChange: (updater) => {
        pagination.value = updater instanceof Function ? updater(pagination.value) : updater;

        router.get(
            props.paginatedData.path,
            {
                page: pagination.value.pageIndex + 1,
                per_page: pagination.value.pageSize,
            },
            { preserveState: false, preserveScroll: true },
        );
    },
    state: {
        get pagination() {
            return pagination.value;
        },
    },
});
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() ? 'selected' : undefined">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
    <DataTablePaginator :table="table" />
</template>
