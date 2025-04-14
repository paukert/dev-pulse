<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal, SquarePen, Trash2 } from 'lucide-vue-next'
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User } from '@/types';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';

const props = defineProps<{
    user: User
}>()

const editUser = () => {
    router.get(route('users.edit', { id: props.user.id }));
}

const isDropdownOpen = ref(false)

const closeDropdown = () => {
    isDropdownOpen.value = false
}

</script>

<template>
    <DropdownMenu v-model:open="isDropdownOpen">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem @select="editUser"><SquarePen /> Edit</DropdownMenuItem>

            <Dialog>
                <DialogTrigger as-child>
                    <DropdownMenuItem @select="(e) => e.preventDefault()"><Trash2 /> Delete</DropdownMenuItem>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader class="space-y-3">
                        <DialogTitle>Do you really want to delete user {{ $props.user.name }}?</DialogTitle>
                        <DialogDescription>
                            This action cannot be undone. This will permanently delete user {{ $props.user.name }}
                            and all of his data.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>

                        <DialogClose @click="closeDropdown">
                            <Link :href="route('users.destroy', {id: props.user.id})" method="delete" as="button">
                                <Button variant="destructive">Delete</Button>
                            </Link>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </DropdownMenuContent>
    </DropdownMenu>
</template>
