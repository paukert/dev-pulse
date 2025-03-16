<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3';
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

defineProps<{
    user: User
}>()

</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem><Link href="#">Edit</Link></DropdownMenuItem>

            <Dialog>
                <DialogTrigger as-child>
                    <DropdownMenuItem @select="(e) => e.preventDefault()">Delete</DropdownMenuItem>
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

                        <Button variant="destructive">
                            <button type="submit">Delete</button>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </DropdownMenuContent>
    </DropdownMenu>
</template>
