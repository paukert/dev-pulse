<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, VcsInstance } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Repositories',
        href: '/repositories',
    },
    {
        title: 'Add VCS instance',
        href: '#',
    },
];

interface Props {
    platforms: Record<string, string>;
}

defineProps<Props>();

const form = useForm<VcsInstance>({
    name: '',
    api_url: '',
    token: '',
    installation_id: '',
    platform: 'gitlab',
});

const createVcsInstance = () => {
    form.post(route('vcs-instances.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add VCS instance" />

        <div class="flex p-4">
            <div class="mx-auto w-full py-10 sm:w-1/2">
                <Heading title="Add VCS instance" />
                <form @submit.prevent="createVcsInstance" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="api_url">API URL</Label>
                        <Input id="api_url" v-model="form.api_url" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.api_url" />
                        <p class="text-sm text-muted-foreground">Base API URL e.g. https://api.github.com/ or https://gitlab.com/api/</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="platform">Platform</Label>
                        <Select id="platform" v-model="form.platform" class="mt-1 block w-full" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Select platform" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(label, value) in platforms" :key="value" :value="value">{{ label }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.platform" />
                    </div>

                    <div class="grid gap-2" v-if="form.platform === 'github'">
                        <Label for="installation_id">Installation ID</Label>
                        <Input id="installation_id" v-model="form.installation_id" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.installation_id" />
                        <p class="text-sm text-muted-foreground">
                            Installation ID could be found at Settings → Integrations → Applications → App as part of the URL
                        </p>
                    </div>

                    <div class="grid gap-2" v-if="form.platform === 'gitlab'">
                        <Label for="token">Token</Label>
                        <Input id="token" type="password" v-model="form.token" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.token" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Create VCS instance</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
