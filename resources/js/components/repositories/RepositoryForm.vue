<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { InertiaForm } from '@inertiajs/vue3';

const form = defineModel<
    InertiaForm<{
        name: string;
        sync_interval_hours: string;
    }>
>('form', { required: true });
</script>

<template>
    <div class="space-y-6">
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
                    <SelectItem value="1">Hourly</SelectItem>
                    <SelectItem value="4">Every 4 hours</SelectItem>
                    <SelectItem value="6">Every 6 hours</SelectItem>
                    <SelectItem value="12">Every 12 hours</SelectItem>
                    <SelectItem value="24">Daily</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="form.errors.sync_interval_hours" />
            <p class="text-sm text-muted-foreground">Choose how often data should be synchronized</p>
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
