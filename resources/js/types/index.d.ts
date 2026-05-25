import type { ComboboxItem } from '@/components/ApiCombobox.vue';
import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Activity {
    id: number;
    type: string;
    needed_actions_count: number;
}

export interface ActivityForm extends Activity {
    isNewRecord?: boolean;
}

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface Challenge {
    id: number;
    name: string;
    description: string;
    active_from: string;
    active_to: string;
    state: 'active' | 'completed' | 'expired' | 'upcoming';
}

export interface ChallengeForm extends Omit<Challenge, 'id'> {
    id?: number;
    activities: ActivityForm[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    isVisible?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role: string;
}

export interface Repository {
    id: number;
    name: string;
    sync_interval: number;
    sync_interval_hours?: number;
    statistics_from: string;
    last_synced_at: string | null;
}

export interface RepositoryForm {
    name: string;
    sync_interval_hours: number;
    vcs_id?: ComboboxItem | null;
    vcs_instance_id?: number | null;
    statistics_from?: string;
}

export interface VcsInstance {
    name: string;
    api_url: string;
    token: string | null;
    installation_id: string | null;
    platform: 'github' | 'gitlab';
}

export interface VcsInstanceUser {
    username: string;
}

export interface PullRequest {
    title: string;
    state: 'open' | 'merged' | 'closed';
    updated_at: string;
    repository: Repository;
    author: VcsInstanceUser;
    metrics: PullRequestMetrics;
}

export interface PullRequestMetrics {
    added_lines: number;
    deleted_lines: number;
    files_count: number;
    merge_time: number | null;
    time_to_review: number | null;
    comments_from_reviewers_count: number;
    comments_as_reviewer_count: number;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface PaginatedResponse<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}
