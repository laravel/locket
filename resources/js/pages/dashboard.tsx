import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Form } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Textarea } from '@/components/ui/textarea';
import { ChevronDown, ExternalLink } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface UserLink {
    id: number;
    status: string;
    category: string;
    created_at: string;
    link: {
        id: number;
        url: string;
        title: string;
        description: string | null;
    };
    notes: Array<{
        id: number;
        note: string;
        created_at: string;
    }>;
}

interface DashboardProps {
    userLinks?: UserLink[];
}

export default function Dashboard({ userLinks = [] }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Add Link Form */}
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar p-3 sm:p-6 dark:border-sidebar-border">
                    <Form
                        method="post"
                        action="/links"
                        resetOnSuccess
                        className="space-y-4"
                    >
                        {({ processing, errors, wasSuccessful }) => (
                            <>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="url">URL</Label>
                                        <Input
                                            id="url"
                                            name="url"
                                            type="url"
                                            placeholder="https://example.com/article"
                                            disabled={processing}
                                        />
                                        <InputError message={errors.url} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="category">Category</Label>
                                        <Select name="category" defaultValue="read">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="read">📖 Read</SelectItem>
                                                <SelectItem value="reference">📚 Reference</SelectItem>
                                                <SelectItem value="watch">🎥 Watch</SelectItem>
                                                <SelectItem value="tools">🔧 Tools</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.category} />
                                    </div>
                                </div>
                                <div className="flex justify-between items-center">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Adding...' : 'Add Link'}
                                    </Button>
                                    {wasSuccessful && (
                                        <span className="text-sm text-green-600 dark:text-green-400">
                                            Link added successfully!
                                        </span>
                                    )}
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                {/* User Links List */}
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar p-3 sm:p-6 dark:border-sidebar-border">
                    <h2 className="mb-4 text-lg font-semibold">Locked In Links ({userLinks.length})</h2>
                    {userLinks.length === 0 ? (
                        <p className="text-muted-foreground">No links yet. Add your first link above!</p>
                    ) : (
                        <div className="space-y-3">
                            {userLinks.map((userLink) => (
                                <LinkAccordion key={userLink.id} userLink={userLink} />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

function LinkAccordion({ userLink }: { userLink: UserLink }) {
    const [isOpen, setIsOpen] = useState(false);

    const statusEmojis = {
        unread: '📖',
        reading: '📚',
        read: '✅',
        reference: '📑',
        archived: '🗄️',
    };

    const categoryEmojis = {
        read: '📖',
        reference: '📚',
        watch: '🎥',
        tools: '🔧',
    };

    return (
        <Collapsible open={isOpen} onOpenChange={setIsOpen}>
            <div className="rounded-lg border border-border bg-background p-4">
                <div className="flex w-full items-start min-w-0">
                    <CollapsibleTrigger className="flex flex-1 items-start justify-start text-left min-w-0 pt-2 -mt-2">
                        <div className="flex-1">
                            <div className="flex items-center gap-1.5 mb-1">
                                    <h3 className="text-sm truncate">{userLink.link.title}</h3>
                                    <a
                                    href={userLink.link.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-muted-foreground hover:text-foreground flex-shrink-0"
                                    onClick={(e) => e.stopPropagation()}
                                >
                                    <ExternalLink className="h-4 w-4" />
                                </a>
                            </div>
                            {userLink.link.description && (
                                <p className="mt-1 text-sm text-muted-foreground line-clamp-2">
                                    {userLink.link.description}
                                </p>
                            )}
                                {/* Compact Status and Category Controls */}
                    <div className="w-full flex items-center gap-1 md:gap-2" onClick={(e) => e.stopPropagation()}>
                        <Form
                            method="patch"
                            action={`/user-links/${userLink.id}`}
                        >
                            {({ processing }) => (
                                <>
                                    <Select
                                        name="status"
                                        defaultValue={userLink.status}
                                        onValueChange={(value) => {
                                            const form = document.querySelector(`form[action="/user-links/${userLink.id}"]`) as HTMLFormElement;
                                            const statusInput = form.querySelector('input[name="status"]') as HTMLInputElement;
                                            if (statusInput) {
                                                statusInput.value = value;
                                                form.requestSubmit();
                                            }
                                        }}
                                    >
                                        <SelectTrigger disabled={processing} className="h-8 text-xs">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="unread">📖 Unread</SelectItem>
                                            <SelectItem value="reading">📚 Reading</SelectItem>
                                            <SelectItem value="read">✅ Read</SelectItem>
                                            <SelectItem value="reference">📑 Reference</SelectItem>
                                            <SelectItem value="archived">🗄️ Archived</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="status" />
                                </>
                            )}
                        </Form>

                        <Form
                            method="patch"
                            action={`/user-links/${userLink.id}`}
                        >
                            {({ processing }) => (
                                <>
                                    <Select
                                        name="category"
                                        defaultValue={userLink.category}
                                        onValueChange={(value) => {
                                            const forms = document.querySelectorAll(`form[action="/user-links/${userLink.id}"]`);
                                            const categoryForm = Array.from(forms).find(form =>
                                                form.querySelector('input[name="category"]')
                                            ) as HTMLFormElement;
                                            if (categoryForm) {
                                                const categoryInput = categoryForm.querySelector('input[name="category"]') as HTMLInputElement;
                                                categoryInput.value = value;
                                                categoryForm.requestSubmit();
                                            }
                                        }}
                                    >
                                        <SelectTrigger disabled={processing} className="h-8 text-xs">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="read">📖 Read</SelectItem>
                                            <SelectItem value="reference">📚 Reference</SelectItem>
                                            <SelectItem value="watch">🎥 Watch</SelectItem>
                                            <SelectItem value="tools">🔧 Tools</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="category" />
                                </>
                            )}
                        </Form>
                    </div>
                        </div>

                        <ChevronDown className={`h-4 w-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
                    </CollapsibleTrigger>
                </div>

                <CollapsibleContent className="mt-4 space-y-4">

                    {/* Existing Notes */}
                    {userLink.notes.length > 0 && (
                        <div className="space-y-2">
                            <h4 className="font-medium">Notes</h4>
                            {userLink.notes.map((note) => (
                                <div key={note.id} className="rounded border border-border bg-muted/50 p-3">
                                    <p className="text-sm">{note.note}</p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {new Date(note.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Add Note Form */}
                    <div className="space-y-2">
                        <Form
                            method="post"
                            action="/links/notes"
                            resetOnSuccess
                            options={{
                                preserveScroll: true
                            }}
                        >
                            {({ processing, errors, wasSuccessful }) => (
                                <div className="space-y-2">
                                    <input type="hidden" name="link_id" value={userLink.link.id} />
                                    <Textarea
                                        name="note"
                                        placeholder="Add private notes about this link..."
                                        disabled={processing}
                                        className="min-h-[80px]"
                                    />
                                    <InputError message={errors.note} />
                                    <div className="flex justify-between items-center">
                                        <Button type="submit" size="sm" disabled={processing}>
                                            {processing ? 'Adding...' : 'Add Note'}
                                        </Button>
                                        {wasSuccessful && (
                                            <span className="text-sm text-green-600 dark:text-green-400">
                                                Note added!
                                            </span>
                                        )}
                                    </div>
                                </div>
                            )}
                        </Form>
                    </div>
                </CollapsibleContent>
            </div>
        </Collapsible>
    );
}
