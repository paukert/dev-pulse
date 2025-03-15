<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Activity } from 'lucide-vue-next';

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
