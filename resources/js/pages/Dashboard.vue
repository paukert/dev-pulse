<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import { Calendar } from 'lucide-vue-next';
import type { DateRange } from 'reka-ui';
import { Ref, ref } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const df = new DateFormatter('en-GB');
const end = today(getLocalTimeZone());
const start = end.subtract({ days: 7 });

const dateRange = ref({
    start,
    end,
}) as Ref<DateRange>;
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
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
