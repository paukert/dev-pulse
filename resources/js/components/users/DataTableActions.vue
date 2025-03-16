<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3';
import { User } from '@/types';

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
            <DropdownMenuItem><Link href="#">Delete</Link></DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
