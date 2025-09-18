import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { SendIcon } from 'lucide-react';
import type React from 'react';
import { useState } from 'react';

// URL extraction regex - matches http/https URLs
const URL_REGEX = /https?:\/\/(?:[-\w.])+(?::[0-9]+)?(?:\/(?:[\w\/_.])*)?(?:\?(?:[\w&=%.])*)?(?:#(?:[\w.])*)?/gi;

// Helper function to extract URLs from text
function extractUrlsFromText(text: string): { urls: string[]; remainingText: string } {
    const urls = text.match(URL_REGEX) || [];
    const remainingText = text.replace(URL_REGEX, '').trim().replace(/\s+/g, ' ');
    return { urls, remainingText };
}

// Helper function to validate if a string is a valid URL
function isValidUrl(string: string): boolean {
    try {
        new URL(string);
        return true;
    } catch {
        return false;
    }
}

export default function InlineStatusForm() {
    const [showModal, setShowModal] = useState(false);
    const [customError, setCustomError] = useState<string>('');

    const form = useForm({
        url: '',
        thoughts: '',
    });

    const handleUrlSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setCustomError('');

        const inputText = form.data.url.trim();
        if (!inputText) return;

        // Extract URLs from the text
        const { urls, remainingText } = extractUrlsFromText(inputText);

        if (urls.length === 0) {
            setCustomError('Please include a valid URL (starting with http:// or https://)');
            return;
        }

        // Validate the first URL
        if (!isValidUrl(urls[0])) {
            setCustomError('The URL found is not valid');
            return;
        }

        // Set the first URL as the link and remaining text as thoughts
        form.setData('url', urls[0]);
        form.setData('thoughts', remainingText);
        setShowModal(true);
    };

    const submitWithThoughts = (thoughts: string) => {
        // Transform the data and then submit
        form.transform(() => ({
            ...form.data,
            thoughts: thoughts,
        }));

        form.post('/status-with-link', {
            onSuccess: () => {
                form.reset();
                setShowModal(false);
            },
            preserveUrl: true,
        });
    };

    return (
        <>
            {/* Single URL input for all devices */}
            <form onSubmit={handleUrlSubmit} className="flex w-full items-center gap-2">
                <input
                    type="text"
                    value={form.data.url}
                    onChange={(e) => {
                        form.setData('url', e.target.value);
                        setCustomError(''); // Clear error when user types
                    }}
                    placeholder="Share link with your thoughts..."
                    className="h-10 w-full rounded-lg border border-[#19140045] bg-transparent px-3 py-2 text-sm text-[#1a1a16] placeholder-[#3e3e3a]/70 transition-all duration-200 focus:border-[#1915014a] focus:ring-0 focus:outline-none dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:placeholder-[#a3a3a3]/60 dark:focus:border-[#62605b]"
                    required
                    disabled={form.processing}
                />
                <button
                    type="submit"
                    disabled={!form.data.url.trim() || form.processing}
                    className="flex h-10 w-10 items-center justify-center rounded-lg border border-[#19140035] text-[#1a1a16] transition-all duration-200 hover:border-[#1915014a] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    title="Share link"
                >
                    {form.processing ? (
                        <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                    ) : (
                        <SendIcon className="h-4 w-4" />
                    )}
                </button>
            </form>

            {(form.errors.url || customError) && (
                <div className="absolute -bottom-5 left-0 text-xs text-red-500">
                    {form.errors.url || customError}
                </div>
            )}

            {/* Modal for thoughts */}
            <Dialog open={showModal} onOpenChange={setShowModal}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Share your thoughts</DialogTitle>
                        <DialogDescription>
                            Add or edit thoughts about: <span className="font-medium">{form.data.url}</span>
                        </DialogDescription>
                    </DialogHeader>

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            const thoughts = (e.currentTarget.thoughts as HTMLTextAreaElement).value;
                            submitWithThoughts(thoughts);
                        }}
                        className="space-y-4"
                    >
                        <Textarea
                            name="thoughts"
                            placeholder="Your thoughts... (optional)"
                            defaultValue={form.data.thoughts}
                            maxLength={200}
                            disabled={form.processing}
                            className="min-h-[80px]"
                            onKeyDown={(e) => {
                                if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                                    e.preventDefault();
                                    const thoughts = (e.target as HTMLTextAreaElement).value;
                                    submitWithThoughts(thoughts);
                                } else if (e.key === 'Tab' && !e.shiftKey) {
                                    e.preventDefault();
                                    // Focus the submit button (next button in DOM order)
                                    const form = e.currentTarget.closest('form');
                                    const submitButton = form?.querySelector('button[type="submit"]') as HTMLButtonElement;
                                    submitButton?.focus();
                                }
                            }}
                        />

                        <div className="flex flex-col-reverse justify-end gap-2 sm:flex-row">
                            <Button type="button" variant="outline" onClick={() => setShowModal(false)} disabled={form.processing}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? 'Sharing...' : 'Share'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
