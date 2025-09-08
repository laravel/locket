import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Form } from '@inertiajs/react';
import StatusController from '@/actions/App/Http/Controllers/StatusController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Status Update Form */}
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar p-6 dark:border-sidebar-border">
                    <h2 className="mb-4 text-lg font-semibold">Update Your Status</h2>
                    <Form
                        {...StatusController.store.form()}
                        resetOnSuccess
                        className="space-y-4"
                    >
                        {({ processing, errors, wasSuccessful }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="status">What's on your mind?</Label>
                                    <Input
                                        id="status"
                                        name="status"
                                        placeholder="Share your current status..."
                                        maxLength={500}
                                        disabled={processing}
                                        className="resize-none"
                                    />
                                    <InputError message={errors.status} />
                                </div>
                                <div className="flex justify-between items-center">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Updating...' : 'Update Status'}
                                    </Button>
                                    {wasSuccessful && (
                                        <span className="text-sm text-green-600 dark:text-green-400">
                                            Status updated successfully!
                                        </span>
                                    )}
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
