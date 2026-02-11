<script setup lang="ts">
import { LucideIcon } from 'lucide-vue-next';

interface Props {
    value: string | number;
    label?: string;
    icon: LucideIcon;
    iconColor: string;
}

defineProps<Props>();

const colorClassesMap: Record<string, string> = {
    yellow: 'bg-yellow-50 text-yellow-500 dark:bg-yellow-500/20',
    blue: 'bg-blue-50 text-blue-500 dark:bg-blue-500/20',
    violet: 'bg-violet-50 text-violet-500 dark:bg-violet-500/20',
    orange: 'bg-orange-50 text-orange-500 dark:bg-orange-500/20',
};
</script>

<template>
    <div class="flex items-center justify-between rounded-xl border px-5 py-4 shadow-sm">
        <div>
            <p class="mb-1 flex items-center gap-1 text-sm font-medium text-muted-foreground">
                <slot name="label">{{ label }}</slot>
            </p>
            <h3 class="text-2xl font-bold">{{ value }}</h3>
        </div>
        <div :class="colorClassesMap[iconColor]" class="flex h-12 w-12 items-center justify-center rounded-full">
            <component :is="icon" />
        </div>
    </div>
</template>
