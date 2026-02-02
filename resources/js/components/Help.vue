<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { CircleQuestionMark } from 'lucide-vue-next';

defineProps<{
    tooltip: string;
}>();
</script>

<template>
    <TooltipProvider>
        <Tooltip :delay-duration="0">
            <TooltipTrigger as-child>
                <CircleQuestionMark class="h-4 w-4 cursor-help" />
            </TooltipTrigger>
            <TooltipContent>
                {{ tooltip }}
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
