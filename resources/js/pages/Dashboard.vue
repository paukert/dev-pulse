<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { developerMetricsColumns, reviewerMetricsColumns } from '@/components/metrics/columns';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, PaginatedResponse, PullRequest } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { Calendar } from 'lucide-vue-next';
import type { DateRange } from 'reka-ui';
import { Ref, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Props {
    from: string;
    to: string;
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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Popover>
                <PopoverTrigger as-child>
                    <Button variant="outline" :class="cn('self-end px-3')">
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
                        :maximum-days="31"
                        :max-value="today(getLocalTimeZone())"
                        :number-of-months="2"
                    />
                </PopoverContent>
            </Popover>

            <section class="my-4">
                <h3 class="text-lg font-semibold tracking-tight">User's pull requests</h3>
                <p class="mb-4 text-sm text-muted-foreground">Recently updated pull requests created by the selected user</p>
                <DataTable
                    :columns="developerMetricsColumns"
                    :paginated-data="props.developerStats.data"
                    :id="props.developerStats.config.id"
                    :page-param-name="props.developerStats.config.pageParamName"
                    :per-page-param-name="props.developerStats.config.perPageParamName"
                />
            </section>

            <section class="my-4">
                <h3 class="text-lg font-semibold tracking-tight">Assigned pull requests</h3>
                <p class="mb-4 text-sm text-muted-foreground">Recently updated pull requests assigned to the selected user for review</p>
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
