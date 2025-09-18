import { Form, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { Copy, Key, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

import HeadingSmall from '@/components/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Token {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
}

interface ApiTokenManagerProps {
    tokens: Token[];
    createdToken?: string;
}

export default function ApiTokenManager({ tokens, createdToken: propCreatedToken }: ApiTokenManagerProps) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [createdToken, setCreatedToken] = useState<string | null>(null);

    const handleRevokeToken = (tokenId: number) => {
        router.delete(ProfileController.revokeToken.url(tokenId), {
            preserveScroll: true,
        });
    };

    const copyToClipboard = async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            // Show user feedback with the actual token so they can copy manually
            prompt('Failed to copy automatically. Please copy this token manually:', text);
        }
    };

    const closeCreateDialog = () => {
        setIsCreateDialogOpen(false);
        setCreatedToken(null);
    };

    return (
        <div className="space-y-6">
            {/* Show the created token if we have one from the server */}
            {propCreatedToken && (
                <Card id="new-token-card" className="border-green-500 bg-green-50 dark:bg-green-950">
                    <CardHeader>
                        <CardTitle className="text-green-700 dark:text-green-300">Token Created Successfully!</CardTitle>
                        <CardDescription className="text-green-600 dark:text-green-400">
                            Make sure to copy this token now. You won't be able to see it again!
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            <code className="block rounded border bg-background p-3 font-mono text-xs break-all">{propCreatedToken}</code>
                            <Button size="sm" variant="outline" onClick={() => copyToClipboard(propCreatedToken)}>
                                <Copy className="mr-2 h-4 w-4" />
                                Copy Token
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            )}

            <div className="flex items-center justify-between">
                <HeadingSmall title="API Tokens" description="Manage API tokens for accessing your account programmatically" />

                <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                    <DialogTrigger asChild>
                        <Button size="sm">
                            <Plus className="h-4 w-4" />
                            Create Token
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New API Token</DialogTitle>
                            <DialogDescription>Create a new API token to access your account programmatically.</DialogDescription>
                        </DialogHeader>

                        {!createdToken ? (
                            <Form
                                {...ProfileController.createToken.form()}
                                options={{
                                    preserveScroll: true,
                                }}
                                onSuccess={() => {
                                    // Just close the dialog, the token will appear at the top of the page
                                    setIsCreateDialogOpen(false);
                                }}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-4 py-4">
                                            <div className="grid gap-2">
                                                <Label htmlFor="token-name">Token Name</Label>
                                                <Input id="token-name" name="name" placeholder="e.g., Mobile App, CLI Tool" disabled={processing} />
                                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button type="button" variant="outline" onClick={closeCreateDialog} disabled={processing}>
                                                Cancel
                                            </Button>
                                            <Button type="submit" disabled={processing}>
                                                {processing ? 'Creating...' : 'Create Token'}
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        ) : (
                            <div className="space-y-4">
                                <div className="rounded-lg bg-muted p-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <p className="text-sm font-medium">Your new API token</p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Make sure to copy this token now. You won't be able to see it again!
                                            </p>
                                        </div>
                                    </div>
                                    <div className="mt-3 flex flex-col gap-2">
                                        <code className="rounded border bg-background p-3 font-mono text-xs leading-relaxed break-all">
                                            {createdToken}
                                        </code>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => {
                                                console.log('Copy button clicked!');
                                                console.log('createdToken:', createdToken);
                                                copyToClipboard(createdToken);
                                            }}
                                            className="self-start"
                                        >
                                            <Copy className="mr-2 h-4 w-4" />
                                            Copy Token
                                        </Button>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button onClick={closeCreateDialog}>Done</Button>
                                </DialogFooter>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>
            </div>

            {tokens.length === 0 ? (
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-12">
                        <Key className="mb-4 h-12 w-12 text-muted-foreground" />
                        <CardTitle className="mb-2 text-center">No API tokens yet</CardTitle>
                        <CardDescription className="text-center">
                            Create your first API token to start accessing your account programmatically.
                        </CardDescription>
                    </CardContent>
                </Card>
            ) : (
                <div className="space-y-4">
                    {tokens.map((token) => (
                        <Card key={token.id}>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-base">{token.name}</CardTitle>
                                        <CardDescription>
                                            Created {format(new Date(token.created_at), 'MMM d, yyyy')}
                                            {token.last_used_at && <> • Last used {format(new Date(token.last_used_at), 'MMM d, yyyy')}</>}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {token.last_used_at ? <Badge variant="secondary">Active</Badge> : <Badge variant="outline">Unused</Badge>}
                                        <Button size="sm" variant="destructive" onClick={() => handleRevokeToken(token.id)}>
                                            <Trash2 className="h-4 w-4" />
                                            Revoke
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    );
}
