<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { useDebounceFn } from '@vueuse/core';
import { Check, ChevronsUpDown, Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

export interface ComboboxItem {
    value: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: ComboboxItem | null;
        url: string;
        selectItemPlaceholder?: string;
        searchPlaceholder?: string;
        disabled?: boolean;
    }>(),
    {
        selectItemPlaceholder: 'Select item...',
        searchPlaceholder: 'Type to search...',
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [item: ComboboxItem];
}>();

const open = ref(false);
const items = ref<ComboboxItem[]>(props.modelValue ? [props.modelValue] : []);
const loading = ref(false);
const query = ref('');

const fetchItems = async (query: string) => {
    loading.value = true;

    try {
        const url = new URL(props.url);
        url.searchParams.set('query', query);

        const res = await fetch(url);
        items.value = await res.json();
    } catch (err: any) {
        console.error('Failed to fetch items', err);
        items.value = props.modelValue ? [props.modelValue] : [];
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = useDebounceFn(fetchItems, 300);

const handleOnSearchChange = (val: string | number) => {
    const queryString = String(val);
    query.value = queryString;

    if (queryString === '') {
        items.value = props.modelValue ? [props.modelValue] : [];
        return;
    }

    debouncedFetch(queryString);
};

const handleSelect = (item: ComboboxItem) => {
    emit('update:modelValue', item);
    open.value = false;
};

watch(
    () => props.modelValue,
    (newValue) => {
        items.value = newValue ? [newValue] : [];
        query.value = '';
    },
);

watch(
    () => props.url,
    () => {
        items.value = [];
        query.value = '';
    },
);
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :disabled="disabled"
                :aria-expanded="open"
                :class="cn('w-full justify-between px-3 font-normal', !modelValue && 'text-muted-foreground')"
            >
                <span class="truncate">
                    {{ modelValue ? modelValue.label : selectItemPlaceholder }}
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-[var(--reka-popover-trigger-width)] p-0">
            <Command :ignore-filter="true">
                <CommandInput :placeholder="searchPlaceholder" @update:model-value="handleOnSearchChange" />

                <CommandList>
                    <div v-if="loading" class="flex items-center justify-center py-6 text-sm text-muted-foreground">
                        <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                        Searching...
                    </div>

                    <CommandEmpty v-if="!loading && query && items.length === 0">No results</CommandEmpty>

                    <CommandGroup v-if="!loading && items.length > 0">
                        <CommandItem v-for="item in items" :key="item.value" :value="item.value" @select="() => handleSelect(item)">
                            <Check :class="cn('mr-2 h-4 w-4', modelValue?.value === item.value ? 'opacity-100' : 'opacity-0')" />
                            {{ item.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
