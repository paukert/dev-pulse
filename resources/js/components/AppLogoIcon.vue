<script setup lang="ts">
import { Activity } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    class?: HTMLAttributes['class'];
    size?: number;
}

const props = withDefaults(defineProps<Props>(), {
    size: 24,
    class: 'text-black dark:text-white',
});
</script>

<template>
    <Activity :size="props.size" :class="props.class" />
</template>
