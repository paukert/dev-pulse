<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { ChallengeForm } from '@/types';
import type { InertiaForm } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { Plus, X } from 'lucide-vue-next';
import type { DateRange } from 'reka-ui';
import { Ref, ref, watch } from 'vue';

const form = defineModel<InertiaForm<ChallengeForm>>('form', { required: true });

withDefaults(
    defineProps<{
        supportedActivityTypes: Record<string, string>;
        canEditChallengeDefinition?: boolean;
    }>(),
    {
        canEditChallengeDefinition: true,
    },
);

const df = new DateFormatter('en-GB');
const start = parseDate(form.value.active_from.split('T')[0]);
const end = parseDate(form.value.active_to.split('T')[0]);

const dateRange = ref({
    start,
    end,
}) as Ref<DateRange>;

watch(
    dateRange,
    (newRange) => {
        form.value.active_from = newRange.start ? newRange.start.toString() : '';
        form.value.active_to = newRange.end ? newRange.end.toString() : '';
    },
    { deep: true },
);

const addActivity = () => {
    form.value.activities.push({ id: Date.now(), type: '', needed_actions_count: 5, isNewRecord: true });
};

const removeActivity = (index: number) => {
    form.value.activities.splice(index, 1);
};
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" class="mt-1 block w-full" required />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Input id="description" v-model="form.description" class="mt-1 block w-full" />
            <InputError :message="form.errors.description" />
        </div>

        <div class="grid gap-2">
            <Label for="period">Period</Label>
            <Popover>
                <PopoverTrigger as-child>
                    <Button :disabled="!canEditChallengeDefinition" variant="outline" :class="cn('w-full justify-start self-end px-3')">
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

                <PopoverContent class="w-auto p-0" align="start">
                    <RangeCalendar
                        v-model="dateRange"
                        class="rounded-md border shadow-sm"
                        :maximum-days="180"
                        :min-value="today(getLocalTimeZone()).add({ days: 1 })"
                        :number-of-months="2"
                    />
                </PopoverContent>
            </Popover>
            <InputError :message="form.errors.active_from" />
            <InputError :message="form.errors.active_to" />
        </div>

        <div class="grid gap-2">
            <div class="flex items-center justify-between">
                <Label>Activities</Label>
                <Button :disabled="!canEditChallengeDefinition" variant="secondary" @click="addActivity"><Plus /> Add activity</Button>
            </div>

            <div class="space-y-4">
                <div v-for="(activity, index) in form.activities" :key="activity.id" class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <Select :disabled="!canEditChallengeDefinition" v-model="activity.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select activity type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in supportedActivityTypes" :key="value" :value="value">{{ label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="w-24 md:w-32">
                            <NumberField v-model="activity.needed_actions_count" :disabled="!canEditChallengeDefinition" :min="1" required>
                                <NumberFieldContent>
                                    <NumberFieldDecrement />
                                    <NumberFieldInput />
                                    <NumberFieldIncrement />
                                </NumberFieldContent>
                            </NumberField>
                        </div>

                        <Button
                            :disabled="!canEditChallengeDefinition || form.activities.length <= 1"
                            variant="outline"
                            size="icon"
                            @click="removeActivity(index)"
                            class="text-destructive hover:text-red-600"
                        >
                            <X />
                        </Button>
                    </div>

                    <InputError :message="form.errors[`activities.${index}.type`]" />
                    <InputError :message="form.errors[`activities.${index}.needed_actions_count`]" />
                </div>
            </div>

            <InputError :message="form.errors.activities" />
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
