import AppLogoIcon from '@/components/app-logo-icon';
import BottomAuthNav from '@/components/bottom-auth-nav';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { Head, Link } from '@inertiajs/react';
import { Copy, CopySlash, Download, LayoutGrid, Lock, Terminal } from 'lucide-react';
import { useEffect, useState, type ReactNode } from 'react';

export default function Mcp() {
    const [copied, setCopied] = useState<string | null>(null);
    const [openKey, setOpenKey] = useState<string | null>(null);
    const [isCoarsePointer, setIsCoarsePointer] = useState<boolean>(false);
    const [activeInstaller, setActiveInstaller] = useState<'claude' | 'other' | null>(null);

    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }
        const mediaQuery = window.matchMedia('(pointer: coarse)');
        const updatePointerType = () => setIsCoarsePointer(mediaQuery.matches);
        updatePointerType();
        try {
            mediaQuery.addEventListener('change', updatePointerType);
            return () => mediaQuery.removeEventListener('change', updatePointerType);
        } catch {
            // Fallback for older Safari
            mediaQuery.addListener(updatePointerType);
            return () => mediaQuery.removeListener(updatePointerType);
        }
    }, []);

    const copyToClipboard = async (text: string, key: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(key);
            setTimeout(() => setCopied((current) => (current === key ? null : current)), 1500);
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            alert('Failed to copy. Please copy manually.');
        }
    };

    const tools = ['GetRecentLinks', 'GetRecentStatuses', 'GetTrendingLinks', 'AddLink'];
    const resources = ['LastAddedLink'];
    const prompts = ['SummarizeLink'];

    const toolRequiresAuth: Record<string, boolean> = {
        GetRecentLinks: false,
        GetTrendingLinks: false,
        GetRecentStatuses: false,
        AddLink: true,
    };

    const resourceRequiresAuth: Record<string, boolean> = {
        LastAddedLink: true,
    };

    const promptRequiresAuth: Record<string, boolean> = {
        SummarizeLink: false,
    };

    const toolDescriptions: Record<string, string> = {
        GetRecentLinks: 'Get the most recently added links to Locket. Shows what new content the community has discovered and shared.',
        GetTrendingLinks:
            'Get trending links that are popular today based on how many users have bookmarked them. Shows what the Locket community is reading right now.',
        GetRecentStatuses: 'Get recent status messages from all Locket users. Useful to show the user the Locket feed and recent Locket updates',
        AddLink:
            "Add a link to your Locket reading list with optional thoughts and category hint. Creates a status update showing what you're reading and saves private notes if thoughts provided.",
    };

    const resourceDescriptions: Record<string, string> = {
        LastAddedLink: "The user's most recently added link with any attached notes.",
    };

    const promptDescriptions: Record<string, string> = {
        SummarizeLink:
            'Generate a comprehensive AI prompt to analyze and summarize web content with actionable insights, thought-provoking questions, and suggestions for further exploration',
    };

    const claudeInstallCommand = 'claude mcp add -s user -t http locket https://locket.laravel.cloud/mcp';
    const mcpUrl = 'https://locket.laravel.cloud/mcp';

    type PromptItem = { raw: string; display?: ReactNode };
    const examplePrompts: PromptItem[] = [
        {
            raw: 'Add to my Locket: https://pestphp.com/docs/pest-v4-is-here-now-with-browser-testing with note "So much better than Dusk"',
            display: (
                <>
                    Add to my Locket:{' '}
                    <a
                        href="https://pestphp.com/docs/pest-v4-is-here-now-with-browser-testing"
                        className="text-blue-600 underline dark:text-blue-400"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        https://pestphp.com/docs/pest-v4-is-here-now-with-browser-testing
                    </a>{' '}
                    with note "So much better than Dusk"
                </>
            ),
        },
        { raw: 'Show me the Locket feed' },
        { raw: 'What Locket links are trending?' },
        { raw: '[ Type /summarize to activate the Summarize prompt ]' },
    ];

    return (
        <>
            <Head title="Add Locket MCP">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-3 text-[#1b1b18] lg:justify-center lg:p-4 dark:bg-[#0a0a0a]">
                <div className="flex w-full flex-col items-center justify-center gap-y-3 pb-24 lg:grow">
                    <div className="relative sticky top-0 z-20 w-full bg-[#FDFDFC]/95 bg-white px-2 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/80 dark:bg-[#0a0a0a]/95 dark:supports-[backdrop-filter]:bg-[#0a0a0a]/80">
                        <Link
                            href={dashboard()}
                            title="Dashboard"
                            className="absolute top-3 right-0.5 rounded-md border border-[#19140035] p-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a]"
                        >
                            <LayoutGrid className="size-5 dark:text-white" />
                        </Link>
                        <Link href="/">
                            <AppLogoIcon className="mx-auto size-12 lg:size-14" />
                        </Link>
                    </div>

                    <div className="flex w-full max-w-7xl grow flex-col gap-6">
                        {/* Overview */}
                        <Card className="rounded-xl">
                            <CardHeader>
                                <CardTitle>What you get from Locket's MCP server</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col md:flex-row md:gap-x-16">
                                <div className="space-y-2">
                                    <h3 className="text-sm font-medium">Tools</h3>
                                    <TooltipProvider delayDuration={0}>
                                        <div className="flex flex-wrap gap-2">
                                            {tools.map((toolName) => (
                                                <Tooltip
                                                    key={toolName}
                                                    open={isCoarsePointer ? openKey === `tool-${toolName}` : undefined}
                                                    onOpenChange={(isOpen) => {
                                                        if (!isCoarsePointer) {
                                                            return;
                                                        }
                                                        if (!isOpen && openKey === `tool-${toolName}`) {
                                                            setOpenKey(null);
                                                        }
                                                    }}
                                                >
                                                    <TooltipTrigger asChild>
                                                        <Badge
                                                            variant="secondary"
                                                            onClick={
                                                                isCoarsePointer
                                                                    ? () => setOpenKey(openKey === `tool-${toolName}` ? null : `tool-${toolName}`)
                                                                    : undefined
                                                            }
                                                        >
                                                            {toolName}
                                                            {toolRequiresAuth[toolName] && <Lock className="opacity-70" />}
                                                        </Badge>
                                                    </TooltipTrigger>
                                                    <TooltipContent side="top" className="max-w-[28rem]">
                                                        <p>{toolDescriptions[toolName]}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            ))}
                                        </div>
                                    </TooltipProvider>
                                </div>
                                <div className="space-y-2">
                                    <h3 className="text-sm font-medium">Resources</h3>
                                    <TooltipProvider delayDuration={0}>
                                        <div className="flex flex-wrap gap-2">
                                            {resources.map((resourceName) => (
                                                <Tooltip
                                                    key={resourceName}
                                                    open={isCoarsePointer ? openKey === `resource-${resourceName}` : undefined}
                                                    onOpenChange={(isOpen) => {
                                                        if (!isCoarsePointer) {
                                                            return;
                                                        }
                                                        if (!isOpen && openKey === `resource-${resourceName}`) {
                                                            setOpenKey(null);
                                                        }
                                                    }}
                                                >
                                                    <TooltipTrigger asChild>
                                                        <Badge
                                                            variant="outline"
                                                            onClick={
                                                                isCoarsePointer
                                                                    ? () =>
                                                                          setOpenKey(
                                                                              openKey === `resource-${resourceName}`
                                                                                  ? null
                                                                                  : `resource-${resourceName}`,
                                                                          )
                                                                    : undefined
                                                            }
                                                        >
                                                            {resourceName}
                                                            {resourceRequiresAuth[resourceName] && <Lock className="opacity-70" />}
                                                        </Badge>
                                                    </TooltipTrigger>
                                                    <TooltipContent side="top" className="max-w-[28rem]">
                                                        <p>{resourceDescriptions[resourceName]}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            ))}
                                        </div>
                                    </TooltipProvider>
                                </div>
                                <div className="space-y-2">
                                    <h3 className="text-sm font-medium">Prompts</h3>
                                    <TooltipProvider delayDuration={0}>
                                        <div className="flex flex-wrap gap-2">
                                            {prompts.map((promptName) => (
                                                <Tooltip
                                                    key={promptName}
                                                    open={isCoarsePointer ? openKey === `prompt-${promptName}` : undefined}
                                                    onOpenChange={(isOpen) => {
                                                        if (!isCoarsePointer) {
                                                            return;
                                                        }
                                                        if (!isOpen && openKey === `prompt-${promptName}`) {
                                                            setOpenKey(null);
                                                        }
                                                    }}
                                                >
                                                    <TooltipTrigger asChild>
                                                        <Badge
                                                            onClick={
                                                                isCoarsePointer
                                                                    ? () =>
                                                                          setOpenKey(
                                                                              openKey === `prompt-${promptName}` ? null : `prompt-${promptName}`,
                                                                          )
                                                                    : undefined
                                                            }
                                                        >
                                                            {promptName}
                                                            {promptRequiresAuth[promptName] && <Lock className="opacity-70" />}
                                                        </Badge>
                                                    </TooltipTrigger>
                                                    <TooltipContent side="top" className="max-w-[28rem]">
                                                        <p>{promptDescriptions[promptName]}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            ))}
                                        </div>
                                    </TooltipProvider>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Install */}
                        <Card className="rounded-xl">
                            <CardHeader>
                                <CardTitle>Install</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div className="flex flex-col gap-3 sm:flex-row">
                                    <Button asChild>
                                        <a
                                            href="cursor://anysphere.cursor-deeplink/mcp/install?name=locket&config=eyJuYW1lIjoibG9ja2V0IiwidHlwZSI6Imh0dHAiLCJ1cmwiOiJodHRwczpcL1wvbG9ja2V0LmxhcmF2ZWwuY2xvdWRcL21jcCJ9"
                                            className="flex items-center justify-center gap-x-1"
                                        >
                                            <Download className="size-4" />
                                            Add to Cursor
                                        </a>
                                    </Button>
                                    <Button asChild variant="secondary">
                                        <a
                                            href="vscode:mcp/install?%7B%22name%22%3A%22locket%22%2C%22type%22%3A%22http%22%2C%22url%22%3A%22https%3A%5C%2F%5C%2Flocket.laravel.cloud%5C%2Fmcp%22%7D"
                                            className="flex items-center justify-center gap-x-1"
                                        >
                                            <Download className="size-4" />
                                            Add to VS Code
                                        </a>
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        onClick={() => setActiveInstaller(activeInstaller === 'claude' ? null : 'claude')}
                                        className={cn(
                                            'flex items-center justify-center gap-x-1 bg-[#c6613f] text-white hover:bg-[#cb6644] hover:text-white',
                                            activeInstaller === 'claude' && 'ring-1 ring-blue-500',
                                        )}
                                    >
                                        <Terminal className="size-4" />
                                        Add to Claude Code
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setActiveInstaller(activeInstaller === 'other' ? null : 'other')}
                                        className={
                                            activeInstaller === 'other'
                                                ? 'flex items-center justify-center gap-x-1 ring-1 ring-blue-500'
                                                : 'flex items-center justify-center gap-x-1'
                                        }
                                    >
                                        <CopySlash className="size-4" />
                                        Add to Other Agents
                                    </Button>
                                </div>

                                <div
                                    className={`overflow-hidden transition-all duration-200 ${activeInstaller === 'claude' ? 'mt-2 max-h-40 opacity-100' : '-mt-2 max-h-0 opacity-0'}`}
                                    aria-hidden={activeInstaller !== 'claude'}
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <code className="flex-1 rounded border bg-background p-3 font-mono text-xs leading-relaxed break-all">
                                            {claudeInstallCommand}
                                        </code>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => copyToClipboard(claudeInstallCommand, 'claude')}
                                            className="shrink-0"
                                        >
                                            <Copy className="mr-2 h-4 w-4" />
                                            {copied === 'claude' ? 'Copied' : 'Copy'}
                                        </Button>
                                    </div>
                                </div>

                                <div
                                    className={`overflow-hidden transition-all duration-200 ${activeInstaller === 'other' ? 'mt-2 max-h-40 opacity-100' : '-mt-2 max-h-0 opacity-0'}`}
                                    aria-hidden={activeInstaller !== 'other'}
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <code className="flex-1 rounded border bg-background p-3 font-mono text-xs leading-relaxed break-all">
                                            {mcpUrl}
                                        </code>
                                        <Button size="sm" variant="outline" onClick={() => copyToClipboard(mcpUrl, 'mcp-url')} className="shrink-0">
                                            <Copy className="mr-2 h-4 w-4" />
                                            {copied === 'mcp-url' ? 'Copied' : 'Copy'}
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Prompts */}
                        <Card className="rounded-xl">
                            <CardHeader>
                                <CardTitle>Try these prompts</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {examplePrompts.map((prompt, idx) => (
                                    <div key={idx} className="flex items-center gap-3">
                                        <div className="flex-1 rounded border border-[#19140014] bg-white p-3 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]">
                                            {prompt.display ?? prompt.raw}
                                        </div>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => copyToClipboard(prompt.raw, `prompt-${idx}`)}
                                            className="shrink-0"
                                        >
                                            <Copy className="mr-2 h-4 w-4" />
                                            {copied === `prompt-${idx}` ? 'Copied' : 'Copy'}
                                        </Button>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                </div>
                <BottomAuthNav />
            </div>
        </>
    );
}
