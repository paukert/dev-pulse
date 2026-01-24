<script setup lang="ts" generic="TData, TValue">
import DataTablePaginator from '@/components/DataTablePaginator.vue';
import { PaginatedResponse } from '@/types';
import { router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<TData, TValue>[];
        paginatedData: PaginatedResponse<TData>;
        id?: string;
        pageParamName?: string;
        perPageParamName?: string;
    }>(),
    {
        pageParamName: 'page',
        perPageParamName: 'per_page',
    },
);

const table = useVueTable({
    get data() {
        return props.paginatedData.data;
    },
    get columns() {
        return props.columns;
    },
    get pageCount() {
        return props.paginatedData.last_page;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    onPaginationChange: (updater) => {
        const currentState = {
            pageIndex: props.paginatedData.current_page - 1,
            pageSize: props.paginatedData.per_page,
        };

        const nextState = updater instanceof Function ? updater(currentState) : updater;

        router.get(
            props.paginatedData.path,
            {
                ...route().params,
                [props.pageParamName]: nextState.pageIndex + 1,
                [props.perPageParamName]: nextState.pageSize,
            },
            {
                preserveState: true,
                preserveScroll: true,
                ...(props.id ? { only: [props.id] } : {}),
            },
        );
    },
    state: {
        get pagination() {
            return {
                pageIndex: props.paginatedData.current_page - 1,
                pageSize: props.paginatedData.per_page,
            };
        },
    },
});
</script>

<template>
    <div class="space-y-4">
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
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" :class="['py-3', cell.column.columnDef.meta?.cellClass]">
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
    </div>
</template>
