<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { useDebounceFn } from '@vueuse/core';
import { Check, ChevronsUpDown, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

export interface ComboboxItem {
    value: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: ComboboxItem | ComboboxItem[] | null;
        url: string;
        selectItemPlaceholder?: string;
        searchPlaceholder?: string;
        disabled?: boolean;
        allowMultipleSelection?: boolean;
    }>(),
    {
        selectItemPlaceholder: 'Select item...',
        searchPlaceholder: 'Type to search...',
        disabled: false,
        allowMultipleSelection: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [item: ComboboxItem | ComboboxItem[] | null];
}>();

const open = ref(false);
const loading = ref(false);
const query = ref('');

const getSelectedItems = (): ComboboxItem[] => {
    if (!props.modelValue) return [];
    if (Array.isArray(props.modelValue)) return [...props.modelValue];
    return [props.modelValue];
};

const items = ref<ComboboxItem[]>(getSelectedItems());

const isSelectionEmpty = computed(() => getSelectedItems().length === 0);
const isSelected = (item: ComboboxItem): boolean => {
    if (!props.modelValue) return false;

    if (props.allowMultipleSelection && Array.isArray(props.modelValue)) {
        return props.modelValue.some((i) => i.value === item.value);
    }

    return !Array.isArray(props.modelValue) && props.modelValue.value === item.value;
};

const fetchItems = async (query: string) => {
    loading.value = true;

    try {
        const url = new URL(props.url);
        url.searchParams.set('query', query);

        const res = await fetch(url);
        items.value = await res.json();
    } catch (err: any) {
        console.error('Failed to fetch items', err);
        items.value = getSelectedItems();
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = useDebounceFn(fetchItems, 300);

const handleOnSearchChange = (val: string | number) => {
    const queryString = String(val);
    query.value = queryString;

    if (queryString === '') {
        items.value = getSelectedItems();
        return;
    }

    debouncedFetch(queryString);
};

const handleSelect = (item: ComboboxItem) => {
    if (props.allowMultipleSelection) {
        const currentlySelected = getSelectedItems();
        const index = currentlySelected.findIndex((i) => i.value === item.value);

        if (index >= 0) {
            currentlySelected.splice(index, 1);
        } else {
            currentlySelected.push(item);
        }

        emit('update:modelValue', currentlySelected);
        return;
    }

    emit('update:modelValue', item);
    open.value = false;
};

watch(
    () => props.modelValue,
    () => {
        if (!query.value) {
            items.value = getSelectedItems();
        }
    },
    { deep: true },
);

watch(
    () => props.url,
    () => {
        items.value = [];
        query.value = '';
    },
);

watch(open, (isOpen) => {
    if (isOpen) {
        items.value = getSelectedItems();
        query.value = '';
    }
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :disabled="disabled"
                :aria-expanded="open"
                :class="cn('h-auto min-h-[40px] w-full justify-between px-3 font-normal', isSelectionEmpty && 'text-muted-foreground')"
            >
                <div class="flex flex-wrap items-center gap-1 text-left">
                    <span v-if="isSelectionEmpty">
                        {{ selectItemPlaceholder }}
                    </span>

                    <template v-else-if="allowMultipleSelection && Array.isArray(modelValue)">
                        <Badge v-for="item in modelValue" :key="item.value" variant="secondary" class="mr-1">
                            {{ item.label }}
                        </Badge>
                    </template>

                    <span v-else class="truncate">
                        {{ (modelValue as ComboboxItem).label }}
                    </span>
                </div>

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
                            <Check :class="cn('mr-2 h-4 w-4', isSelected(item) ? 'opacity-100' : 'opacity-0')" />
                            {{ item.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
