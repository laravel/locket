import { useForm } from '@inertiajs/react';
import { SendIcon } from 'lucide-react';
import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import type React from 'react';

export default function InlineStatusForm() {
    const [showModal, setShowModal] = useState(false);
    
    const form = useForm({
        url: '',
        thoughts: '',
    });

    const handleUrlSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!form.data.url.trim()) return;
        
        // Validate URL format
        try {
            new URL(form.data.url);
            setShowModal(true);
        } catch {
            alert('Please enter a valid URL');
        }
    };

    const submitWithThoughts = (thoughts: string) => {
        // Transform the data and then submit
        form.transform(() => ({
            ...form.data,
            thoughts: thoughts
        }));
        
        form.post('/status-with-link', {
            onSuccess: () => {
                form.reset();
                setShowModal(false);
            },
            preserveUrl: true
        });
    };

    return (
        <>
            {/* Single URL input for all devices */}
            <form onSubmit={handleUrlSubmit} className="flex w-full items-center gap-2">
                <input
                    type="url"
                    value={form.data.url}
                    onChange={(e) => form.setData('url', e.target.value)}
                    placeholder="Share link"
                    className="h-10 w-full rounded-lg border border-[#19140045] bg-transparent px-3 py-2 text-sm text-[#1a1a16] placeholder-[#3e3e3a]/70 transition-all duration-200 focus:border-[#1915014a] focus:outline-none focus:ring-0 dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:placeholder-[#a3a3a3]/60 dark:focus:border-[#62605b]"
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
            
            {form.errors.url && (
                <div className="absolute -bottom-5 left-0 text-xs text-red-500">
                    {form.errors.url}
                </div>
            )}

            {/* Modal for thoughts */}
            <Dialog open={showModal} onOpenChange={setShowModal}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Share your thoughts</DialogTitle>
                        <DialogDescription>
                            Add optional thoughts about: {form.data.url}
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form onSubmit={(e) => {
                        e.preventDefault();
                        const thoughts = (e.currentTarget.thoughts as HTMLTextAreaElement).value;
                        submitWithThoughts(thoughts);
                    }} className="space-y-4">
                        <Textarea
                            name="thoughts"
                            placeholder="Your thoughts... (optional)"
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
                        
                        <div className="flex flex-col-reverse sm:flex-row justify-end gap-2">
                            <Button 
                                type="button" 
                                variant="outline" 
                                onClick={() => setShowModal(false)}
                                disabled={form.processing}
                            >
                                Cancel
                            </Button>
                            <Button 
                                type="submit"
                                disabled={form.processing}
                            >
                                {form.processing ? 'Sharing...' : 'Share'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
