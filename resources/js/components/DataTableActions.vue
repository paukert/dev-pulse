<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, SquarePen, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    deleteConfirmationTitle: string;
    deleteConfirmationMessage: string;
    deleteUrl: string;
    editUrl: string;
}>();

const isDropdownOpen = ref(false);

const closeDropdown = () => {
    isDropdownOpen.value = false;
};
</script>

<template>
    <DropdownMenu v-model:open="isDropdownOpen">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem @select="() => router.get(props.editUrl)"><SquarePen /> Edit</DropdownMenuItem>

            <Dialog>
                <DialogTrigger as-child>
                    <DropdownMenuItem @select="(e) => e.preventDefault()"><Trash2 /> Delete</DropdownMenuItem>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader class="space-y-3">
                        <DialogTitle>{{ $props.deleteConfirmationTitle }}</DialogTitle>
                        <DialogDescription>{{ $props.deleteConfirmationMessage }}</DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>

                        <DialogClose @click="closeDropdown" as-child>
                            <Link :href="props.deleteUrl" method="delete" as="button">
                                <Button variant="destructive" class="w-full sm:w-auto">Delete</Button>
                            </Link>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
