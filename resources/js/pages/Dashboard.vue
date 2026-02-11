<script setup lang="ts">
import ApiCombobox, { type ComboboxItem } from '@/components/ApiCombobox.vue';
import LineChart from '@/components/charts/LineChart.vue';
import PolarChart from '@/components/charts/PolarChart.vue';
import DataTable from '@/components/DataTable.vue';
import Help from '@/components/Help.vue';
import { developerMetricsColumns, reviewerMetricsColumns } from '@/components/metrics/columns';
import Panel from '@/components/Panel.vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn, formatDuration } from '@/lib/utils';
import { type BreadcrumbItem, PaginatedResponse, PullRequest } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { xorBy } from 'lodash-es';
import { Calendar, FileDiff, GitMerge, Timer, Trophy } from 'lucide-vue-next';
import type { DateRange } from 'reka-ui';
import { Ref, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Props {
    users: ComboboxItem[];
    from: string;
    to: string;
    overallStats: {
        badgesEarned: number;
        averageTimeToReview: number | null;
        averageMergeTime: number | null;
        totalLinesChanged: number;
    };
    polarChartOptions: object;
    lineChartOptions: object;
    developerStats: {
        data: PaginatedResponse<PullRequest>;
        config: { id: string; pageParamName: string; perPageParamName: string };
    };
    reviewerStats: {
        data: PaginatedResponse<PullRequest>;
        config: { id: string; pageParamName: string; perPageParamName: string };
    };
}

const props = defineProps<Props>();
const selectedUsers = ref<ComboboxItem[]>(props.users);

watch(
    selectedUsers,
    (newUsers) => {
        router.get(
            route('dashboard'),
            {
                ...route().params,
                userIds: newUsers.map((user) => user.value),
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    },
    { deep: true },
);

watch(
    () => props.users,
    (newUsers) => {
        if (xorBy(newUsers, selectedUsers.value, 'value').length === 0) {
            return;
        }
        selectedUsers.value = newUsers;
    },
);

const df = new DateFormatter('en-GB');
const start = parseDate(props.from);
const end = parseDate(props.to);

const dateRange = ref({
    start,
    end,
}) as Ref<DateRange>;

watch(
    dateRange,
    (newRange) => {
        if (!newRange.start || !newRange.end) {
            return;
        }

        router.get(
            route('dashboard'),
            {
                ...route().params,
                from: newRange.start.toString(),
                to: newRange.end.toString(),
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    },
    { deep: true },
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-6">
            <div class="flex w-full flex-col justify-between gap-2 md:flex-row">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <ApiCombobox
                        v-model="selectedUsers"
                        :url="route('users.search')"
                        :allow-multiple-selection="true"
                        select-item-placeholder="Select users"
                        search-placeholder="Search users..."
                    />
                </div>

                <div class="w-full md:w-auto">
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button variant="outline" :class="cn('w-full justify-start self-end px-3')">
                                <Calendar />
                                <template v-if="dateRange.start">
                                    <template v-if="dateRange.end">
                                        {{ df.format(dateRange.start.toDate(getLocalTimeZone())) }} -
                                        {{ df.format(dateRange.end.toDate(getLocalTimeZone())) }}
                                    </template>
                                    <template v-else>
                                        {{ df.format(dateRange.start.toDate(getLocalTimeZone())) }}
                                    </template>
                                </template>
                                <template v-else>Select date range</template>
                            </Button>
                        </PopoverTrigger>

                        <PopoverContent class="w-auto p-0" align="end">
                            <RangeCalendar
                                v-model="dateRange"
                                class="rounded-md border shadow-sm"
                                :maximum-days="365"
                                :max-value="today(getLocalTimeZone())"
                                :number-of-months="2"
                            />
                        </PopoverContent>
                    </Popover>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Panel :value="overallStats.badgesEarned" label="Completed challenges" :icon="Trophy" icon-color="yellow" />
                <Panel :value="formatDuration(overallStats.averageTimeToReview)" :icon="Timer" icon-color="blue">
                    <template #label>
                        Average time to review
                        <Help tooltip="Average time from assignment to the reviewer's first comment or approval in PRs reviewed by selected users" />
                    </template>
                </Panel>
                <Panel :value="formatDuration(overallStats.averageMergeTime)" :icon="GitMerge" icon-color="violet">
                    <template #label>
                        Average merge time
                        <Help tooltip="Average time from the creation of a pull request to its merging in PRs created by the selected users" />
                    </template>
                </Panel>
                <Panel :value="overallStats.totalLinesChanged" :icon="FileDiff" icon-color="orange">
                    <template #label>
                        Total lines changed
                        <Help tooltip="Total number of lines changed in PRs created by the selected users" />
                    </template>
                </Panel>
            </div>

            <section class="my-4">
                <h3 class="text-lg font-semibold tracking-tight">Selected users' activity</h3>
                <div class="flex flex-col gap-10 lg:flex-row">
                    <div class="w-full lg:w-1/2">
                        <PolarChart :options="polarChartOptions" />
                    </div>
                    <div class="w-full lg:w-1/2">
                        <LineChart :options="lineChartOptions" />
                    </div>
                </div>
            </section>

            <section class="my-4">
                <h3 class="text-lg font-semibold tracking-tight">Created pull requests</h3>
                <p class="mb-4 text-sm text-muted-foreground">Recently updated pull requests created by the selected users</p>
                <DataTable
                    :columns="developerMetricsColumns"
                    :paginated-data="props.developerStats.data"
                    :id="props.developerStats.config.id"
                    :page-param-name="props.developerStats.config.pageParamName"
                    :per-page-param-name="props.developerStats.config.perPageParamName"
                />
            </section>

            <section class="my-4">
                <h3 class="text-lg font-semibold tracking-tight">Code reviews</h3>
                <p class="mb-4 text-sm text-muted-foreground">Recently updated pull requests where the selected users were requested as reviewers</p>
                <DataTable
                    :columns="reviewerMetricsColumns"
                    :paginated-data="props.reviewerStats.data"
                    :id="props.reviewerStats.config.id"
                    :page-param-name="props.reviewerStats.config.pageParamName"
                    :per-page-param-name="props.reviewerStats.config.perPageParamName"
                />
            </section>
        </div>
    </AppLayout>
</template>
