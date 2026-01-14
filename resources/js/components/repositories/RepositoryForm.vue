<script setup lang="ts">
import ApiCombobox from '@/components/ApiCombobox.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { RepositoryForm } from '@/types';
import { InertiaForm } from '@inertiajs/vue3';
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { computed, watch } from 'vue';

const form = defineModel<InertiaForm<RepositoryForm>>('form', { required: true });

const props = withDefaults(
    defineProps<{
        isNewRecord?: boolean;
        vcs_instances?: { id: number; name: string }[];
    }>(),
    { isNewRecord: false },
);

const df = new DateFormatter('en-GB');
const now = today(getLocalTimeZone());
const sixMonthsAgo = now.subtract({ months: 6 });

const statisticsFromProxy = computed({
    get: () => {
        return form.value.statistics_from ? parseDate(form.value.statistics_from.split('T')[0]) : undefined;
    },
    set: (val: CalendarDate | undefined) => {
        form.value.statistics_from = val ? val.toString() : '';
    },
});

watch(
    () => form.value.vcs_instance_id,
    () => {
        form.value.vcs_id = null;
    },
);
</script>

<template>
    <div class="space-y-6">
        <div v-if="isNewRecord" class="grid gap-2">
            <Label for="vcs_instance_id">Instance</Label>
            <Select id="vcs_instance_id" v-model="form.vcs_instance_id" class="mt-1 block w-full" required>
                <SelectTrigger>
                    <SelectValue placeholder="Select instance" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="item in props.vcs_instances" :key="item.id" :value="item.id"> {{ item.name }} </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.vcs_instance_id" />
        </div>

        <div v-if="isNewRecord" class="grid gap-2">
            <Label for="vcs_id">Repository</Label>
            <ApiCombobox
                id="vcs_id"
                v-model="form.vcs_id"
                :disabled="form.vcs_instance_id === null"
                :url="route('repositories.search', { vcs_instance_id: form.vcs_instance_id })"
                select-item-placeholder="Select repository"
                search-placeholder="Search repositories..."
            />
            <InputError :message="form.errors.vcs_id" />
        </div>

        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" class="mt-1 block w-full" required />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="sync_interval_hours">Sync interval</Label>
            <Select id="sync_interval_hours" v-model="form.sync_interval_hours" class="mt-1 block w-full" required>
                <SelectTrigger>
                    <SelectValue placeholder="Select an interval" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="1">Hourly</SelectItem>
                    <SelectItem :value="4">Every 4 hours</SelectItem>
                    <SelectItem :value="6">Every 6 hours</SelectItem>
                    <SelectItem :value="12">Every 12 hours</SelectItem>
                    <SelectItem :value="24">Daily</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.sync_interval_hours" />
            <p class="text-sm text-muted-foreground">Choose how often data should be synchronized</p>
        </div>

        <div v-if="isNewRecord" class="grid gap-2">
            <Label for="statistics_from">Statistics from</Label>
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        :class="cn('w-full justify-start px-3 text-left font-normal', !statisticsFromProxy && 'text-muted-foreground')"
                    >
                        {{ statisticsFromProxy ? df.format(statisticsFromProxy.toDate(getLocalTimeZone())) : 'Select date' }}
                    </Button>
                </PopoverTrigger>

                <PopoverContent class="w-auto p-0" align="start">
                    <Calendar id="statistics_from" v-model="statisticsFromProxy" :min-value="sixMonthsAgo" :max-value="now" />
                </PopoverContent>
            </Popover>
            <InputError :message="form.errors.statistics_from" />
        </div>

        <div class="flex items-center gap-4">
            <slot name="actions" />

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
            </Transition>
        </div>
    </div>
</template>
