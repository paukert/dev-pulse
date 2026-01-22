import Help from '@/components/Help.vue';
import { formatDuration } from '@/lib/utils';
import type { PullRequest } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { truncate } from 'lodash-es';
import { GitMerge, GitPullRequestArrow, GitPullRequestClosed } from 'lucide-vue-next';
import { FunctionalComponent, h } from 'vue';

function formatDateTime(value: string | null) {
    return value === null ? 'Never' : new Date(value).toLocaleString('en-GB');
}

const classesMap: Record<string, string> = {
    open: 'text-green-600',
    merged: 'text-violet-500',
    closed: 'text-red-500',
};

const iconMap: Record<string, FunctionalComponent> = {
    open: GitPullRequestArrow,
    merged: GitMerge,
    closed: GitPullRequestClosed,
};

const baseColumns: ColumnDef<PullRequest>[] = [
    {
        accessorKey: 'state',
        meta: { cellClass: 'pr-0' },
        header: '',
        cell: ({ row }) => {
            const state: string = row.getValue('state');
            return h(iconMap[state], { size: 16, class: `ml-auto block ${classesMap[state]}` });
        },
    },
    {
        accessorKey: 'title',
        header: () => h('div', { class: 'text-left' }, 'Title'),
        cell: ({ row }) => {
            const title: string = row.getValue('title');
            return h('div', { class: 'text-left font-medium', title: row.original.repository.name + ': ' + title }, [
                truncate(title, { length: 40 }),
                h(
                    'div',
                    { class: 'text-muted-foreground max-lg:hidden' },
                    row.original.author.username + ', ' + formatDateTime(row.original.created_at),
                ),
            ]);
        },
    },
    {
        accessorKey: 'metrics.added_lines',
        id: 'metricsAddedLines',
        header: () => h('div', { class: 'text-left' }, 'Additions'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('metricsAddedLines'));
        },
    },
    {
        accessorKey: 'metrics.deleted_lines',
        id: 'metricsDeletedLines',
        header: () => h('div', { class: 'text-left' }, 'Deletions'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('metricsDeletedLines'));
        },
    },
    {
        accessorKey: 'metrics.files_count',
        id: 'metricsFilesCount',
        header: () => h('div', { class: 'text-left' }, 'Files'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('metricsFilesCount'));
        },
    },
    {
        accessorKey: 'metrics.time_to_review',
        id: 'metricsTimeToReview',
        header: () =>
            h('div', { class: 'flex gap-1 items-center text-left' }, [
                'Time to review',
                h(Help, { tooltip: "Time from assignment to the reviewer's first comment or approval" }),
            ]),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDuration(row.getValue('metricsTimeToReview')));
        },
    },
    {
        accessorKey: 'metrics.merge_time',
        id: 'metricsMergeTime',
        header: () =>
            h('span', { class: 'flex gap-1 items-center text-left' }, [
                'Merge time',
                h(Help, { tooltip: 'Time from the creation of a pull request to its merging' }),
            ]),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, formatDuration(row.getValue('metricsMergeTime')));
        },
    },
];

export const developerMetricsColumns: ColumnDef<PullRequest>[] = [
    ...baseColumns,
    {
        accessorKey: 'metrics.comments_from_reviewers_count',
        id: 'metricsCommentsFromReviewersCount',
        header: () => h('div', { class: 'text-left' }, 'Reviews'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('metricsCommentsFromReviewersCount'));
        },
    },
];
