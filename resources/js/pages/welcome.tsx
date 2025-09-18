import AppLogoIcon from '@/components/app-logo-icon';
import BottomAuthNav from '@/components/bottom-auth-nav';
import SafeTextWithBreaks from '@/components/safe-text-with-breaks';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { dashboard, mcp } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRightIcon, Bookmark, BookmarkCheck, ExternalLink, LayoutGrid, Link as LinkIcon, TrendingUp } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

type Status = {
    id: number;
    status: string;
    created_at: string;
    user: { name: string; github_username: string | null; avatar: string; avatar_fallback: string };
    link?: {
        id: number;
        url: string;
        title: string;
        description: string | null;
    } | null;
};

type TrendingLink = {
    id: number;
    url: string;
    title: string;
    description: string | null;
    category: string;
    bookmark_count: number;
};

export default function Welcome() {
    const { auth, statuses = [], trendingLinks = [] } = usePage<SharedData & { statuses: Status[]; trendingLinks: TrendingLink[] }>().props;
    const isFirstLoad = useRef(true);
    const [bookmarkingLinks, setBookmarkingLinks] = useState<Set<number>>(new Set());

    const handleBookmark = (linkId: number) => {
        if (!auth.user || bookmarkingLinks.has(linkId) || auth.bookmarked_link_ids.includes(linkId)) {
            return; // Don't allow bookmarking if already bookmarked
        }

        setBookmarkingLinks((prev) => new Set(prev).add(linkId));

        router.post(
            `/links/${linkId}/bookmark`,
            {},
            {
                onSuccess: () => {
                    router.reload({ only: ['statuses'] });
                },
                onError: (errors) => {
                    console.error('Failed to bookmark:', errors);
                },
                onFinish: () => {
                    setBookmarkingLinks((prev) => {
                        const next = new Set(prev);
                        next.delete(linkId);
                        return next;
                    });
                },
            },
        );
    };

    // Sort statuses by created_at in descending order (newest first)
    // Only 20 so this is fine
    const sortedStatuses = [...statuses].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

    // Mark that we've completed the first load
    useEffect(() => {
        if (isFirstLoad.current) {
            isFirstLoad.current = false;
        }
    }, []);

    // Refresh recent statuses when tab becomes visible again (not on initial load)
    useEffect(() => {
        let wasHidden = false;

        const onVisibilityChange = () => {
            if (document.hidden) {
                wasHidden = true;
                return;
            }

            if (wasHidden) {
                router.reload({ only: ['statuses'] });
                wasHidden = false;
            }
        };

        document.addEventListener('visibilitychange', onVisibilityChange);
        return () => document.removeEventListener('visibilitychange', onVisibilityChange);
    }, []);

    return (
        <>
            <Head title="Feed">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-3 text-[#1b1b18] lg:justify-center lg:p-4 dark:bg-[#0a0a0a]">
                <div className="flex w-full flex-col items-center justify-center gap-y-3 pb-24 lg:grow">
                    <div className="sticky top-0 z-20 flex w-full items-center justify-between bg-[#FDFDFC]/95 bg-white px-2 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/80 dark:bg-[#0a0a0a]/95 dark:supports-[backdrop-filter]:bg-[#0a0a0a]/80">
                        <a href="https://github.com/laravel/locket">
                            <svg className="h-auto w-6 text-[#24292f] hover:text-[#24292f]/80" viewBox="0 0 98 96" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    fillRule="evenodd"
                                    clipRule="evenodd"
                                    d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z"
                                    fill="currentColor"
                                />
                            </svg>
                        </a>
                        <div>
                            <Link href="/">
                                <AppLogoIcon className="mx-auto size-12 lg:size-14" />
                            </Link>
                            <Link
                                href={mcp()}
                                className="group flex items-center justify-center gap-x-0.5 rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-600 shadow-sm hover:bg-blue-100 dark:bg-blue-500/20 dark:text-blue-400 dark:shadow-none dark:hover:bg-blue-500/30"
                            >
                                Locket MCP <ArrowRightIcon className="size-3 transition duration-600 group-hover:translate-x-1" />
                            </Link>
                        </div>
                        <Link
                            href={dashboard()}
                            title="Dashboard"
                            className={`rounded-md border border-[#19140035] p-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] hover:text-[#1b1b18bb] ${isFirstLoad.current ? 'opacity-100 transition-opacity duration-500 starting:opacity-0' : ''}`}
                        >
                            <LayoutGrid className="size-5 dark:text-white" />
                        </Link>
                    </div>

                    <div className="flex w-full max-w-7xl grow flex-col gap-6 lg:flex-row">
                        {/* Mobile trending links - shown on top */}
                        <div className="lg:hidden">
                            <TrendingLinksSection
                                trendingLinks={trendingLinks}
                                auth={auth}
                                handleBookmark={handleBookmark}
                                bookmarkingLinks={bookmarkingLinks}
                                mobile={true}
                            />
                        </div>

                        {/* Desktop sidebar - left */}
                        <aside className="hidden lg:block lg:w-80 lg:shrink-0">
                            <TrendingLinksSection
                                trendingLinks={trendingLinks}
                                auth={auth}
                                handleBookmark={handleBookmark}
                                bookmarkingLinks={bookmarkingLinks}
                                mobile={false}
                            />
                        </aside>

                        {/* Main feed - right */}
                        <main className="flex flex-col gap-3 lg:min-w-0 lg:flex-1">
                            <ul
                                className={`flex flex-col divide-y divide-[#19140014] dark:divide-[#3E3E3A] ${isFirstLoad.current ? 'opacity-100 transition-opacity duration-750 starting:opacity-0' : ''} `}
                            >
                                {sortedStatuses.length === 0 && (
                                    <li className="py-10 text-center text-sm text-[#3e3e3a] dark:text-[#a3a3a3]">
                                        No statuses yet. Be the first to share an update.
                                    </li>
                                )}

                                {sortedStatuses.map((s) => (
                                    <li key={s.id} className="py-3">
                                        <div className="flex items-start gap-4">
                                            <Avatar className="size-10 rounded-full">
                                                <AvatarImage src={s.user.avatar} alt={s.user.name} />
                                                <AvatarFallback asChild>
                                                    <div className="relative flex items-center justify-center">
                                                        <span className="absolute text-2xl font-bold text-white uppercase">
                                                            {s.user.name.substring(0, 1)}
                                                        </span>
                                                        <img src={s.user.avatar_fallback} alt={s.user.name} />
                                                    </div>
                                                </AvatarFallback>
                                            </Avatar>
                                            <div className="min-w-0 flex-1">
                                                <div className="mb-1 flex items-center justify-between gap-2 text-sm">
                                                    {s.user.github_username ? (
                                                        <a
                                                            href={`https://github.com/${s.user.github_username}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="truncate font-medium text-[#1b1b18] transition-colors hover:text-blue-600 dark:text-[#EDEDEC] dark:hover:text-blue-400"
                                                        >
                                                            {s.user.name}
                                                        </a>
                                                    ) : (
                                                        <span className="truncate font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{s.user.name}</span>
                                                    )}
                                                    <div className="flex items-center gap-2">
                                                        <TimeStamp iso={s.created_at} />

                                                        {auth.user && s.link && (
                                                            <button
                                                                onClick={() => handleBookmark(s.link!.id)}
                                                                disabled={
                                                                    bookmarkingLinks.has(s.link.id) || auth.bookmarked_link_ids.includes(s.link.id)
                                                                }
                                                                className={`flex items-center justify-center rounded-md p-1 disabled:cursor-not-allowed disabled:opacity-80 ${
                                                                    auth.bookmarked_link_ids.includes(s.link.id)
                                                                        ? 'text-blue-600 dark:text-blue-400'
                                                                        : 'text-[#3e3e3a]/60 hover:text-[#1b1b18] dark:text-[#a3a3a3]/60 dark:hover:text-[#EDEDEC]'
                                                                }`}
                                                                title={
                                                                    auth.bookmarked_link_ids.includes(s.link.id)
                                                                        ? `Bookmarked: ${s.link.title || s.link.url}`
                                                                        : `Bookmark: ${s.link.title || s.link.url}`
                                                                }
                                                            >
                                                                {bookmarkingLinks.has(s.link.id) ? (
                                                                    <div className="size-6 animate-spin rounded-full border border-current border-t-transparent" />
                                                                ) : auth.bookmarked_link_ids.includes(s.link.id) ? (
                                                                    <BookmarkCheck className="size-6" />
                                                                ) : (
                                                                    <Bookmark className="size-6" />
                                                                )}
                                                            </button>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex flex-col gap-y-2 text-[15px] leading-relaxed wrap-break-word text-[#1b1b18] dark:text-[#EDEDEC]">
                                                    {s.status && <SafeTextWithBreaks text={s.status} className="wrap-break-word" />}
                                                    {s.link && (
                                                        <div className="flex items-center gap-x-1">
                                                            <LinkIcon className="h-3 w-3" />
                                                            <a
                                                                href={s.link.url}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="inline-flex items-center gap-1 break-all text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                                                            >
                                                                {s.link.url}
                                                            </a>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </main>
                    </div>
                </div>
                <BottomAuthNav />
            </div>
        </>
    );
}

function TimeStamp({ iso }: { iso: string }) {
    const dt = new Date(iso);
    const now = new Date();
    const diffMs = now.getTime() - dt.getTime();
    const diffHours = diffMs / (1000 * 60 * 60);

    const timeFormatter = new Intl.DateTimeFormat(undefined, {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
    const dateFormatter = new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
    const dateFormatterNoYear = new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
    });

    const title = `${timeFormatter.format(dt)} · ${dateFormatter.format(dt)}`;

    let display: string;
    if (diffHours < 12) {
        const diffMinutes = Math.floor(diffMs / (1000 * 60));
        if (diffMinutes < 1) {
            display = '1m';
        } else if (diffMinutes < 60) {
            display = `${diffMinutes}m`;
        } else {
            const hours = Math.floor(diffMinutes / 60);
            display = `${hours}h`;
        }
    } else {
        const includeYear = dt.getFullYear() !== now.getFullYear();
        display = includeYear ? dateFormatter.format(dt) : dateFormatterNoYear.format(dt);
    }

    return (
        <time className="text-xs text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80" dateTime={iso} title={title}>
            {display}
        </time>
    );
}

function TrendingLinksSection({
    trendingLinks,
    auth,
    handleBookmark,
    bookmarkingLinks,
    mobile,
}: {
    trendingLinks: TrendingLink[];
    auth: SharedData['auth'];
    handleBookmark: (linkId: number) => void;
    bookmarkingLinks: Set<number>;
    mobile: boolean;
}) {
    if (trendingLinks.length === 0) {
        return (
            <div>
                <div className={`rounded-lg border border-[#19140014] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] ${mobile ? 'mb-6' : ''}`}>
                    <div className="mb-3 flex items-center gap-2">
                        <TrendingUp className="size-4 text-[#3e3e3a] dark:text-[#a3a3a3]" />
                        <h2 className="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Trending Today</h2>
                    </div>
                    <p className="text-xs text-[#3e3e3a] dark:text-[#a3a3a3]">No trending links yet today. Be the first to share something!</p>
                </div>
                <span className="block px-1 py-1 text-xs/4">Locket is your social link sharing read-later app for developers</span>
            </div>
        );
    }

    return (
        <div className={mobile ? 'mb-3' : ''}>
            <div className="rounded-lg border border-[#19140014] bg-white dark:border-[#3E3E3A] dark:bg-[#0a0a0a]">
                <div className="border-b border-[#19140014] p-4 dark:border-[#3E3E3A]">
                    <div className="flex items-center gap-2">
                        <TrendingUp className="size-4 text-[#3e3e3a] dark:text-[#a3a3a3]" />
                        <h2 className="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Trending Today</h2>
                    </div>
                </div>
                <div className={`${mobile ? 'flex gap-3 overflow-x-scroll p-4' : 'divide-y divide-[#19140014] dark:divide-[#3E3E3A]'}`}>
                    {trendingLinks.map((link) => (
                        <div
                            key={link.id}
                            className={`${mobile ? 'w-64 flex-shrink-0 rounded border border-[#19140014] p-3 dark:border-[#3E3E3A]' : 'p-4'}`}
                        >
                            <div className="mb-2 flex items-start justify-between gap-2">
                                <div className="min-w-0 flex-1">
                                    <a href={link.url} target="_blank" rel="noopener noreferrer" className="group block">
                                        <div className="flex items-start gap-1">
                                            <h3 className="line-clamp-2 text-sm font-medium text-[#1b1b18] transition-colors group-hover:text-blue-600 dark:text-[#EDEDEC] dark:group-hover:text-blue-400">
                                                {link.title}
                                            </h3>
                                            <ExternalLink className="mt-0.5 size-3 flex-shrink-0 text-[#3e3e3a]/60 dark:text-[#a3a3a3]/60" />
                                        </div>
                                        <p className="mt-1 truncate text-xs text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80">{new URL(link.url).hostname}</p>
                                    </a>
                                </div>

                                <div className="flex flex-shrink-0 items-center">
                                    {auth.user && (
                                        <button
                                            onClick={() => handleBookmark(link.id)}
                                            disabled={bookmarkingLinks.has(link.id) || auth.bookmarked_link_ids.includes(link.id)}
                                            className={`flex items-center justify-center rounded p-1 disabled:cursor-not-allowed disabled:opacity-50 ${
                                                auth.bookmarked_link_ids.includes(link.id)
                                                    ? 'text-blue-600 dark:text-blue-400'
                                                    : 'text-[#3e3e3a]/60 hover:text-[#1b1b18] dark:text-[#a3a3a3]/60 dark:hover:text-[#EDEDEC]'
                                            }`}
                                            title={
                                                auth.bookmarked_link_ids.includes(link.id)
                                                    ? `Bookmarked: ${link.title || link.url}`
                                                    : `Bookmark: ${link.title || link.url}`
                                            }
                                        >
                                            {bookmarkingLinks.has(link.id) ? (
                                                <div className="size-4 animate-spin rounded-full border border-current border-t-transparent" />
                                            ) : auth.bookmarked_link_ids.includes(link.id) ? (
                                                <BookmarkCheck className="size-4" />
                                            ) : (
                                                <Bookmark className="size-4" />
                                            )}
                                        </button>
                                    )}
                                    <span className="text-xs text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80">{link.bookmark_count}</span>
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <span
                                    className={`inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ${
                                        link.category === 'read'
                                            ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400'
                                            : link.category === 'watch'
                                              ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400'
                                              : link.category === 'reference'
                                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400'
                                                : 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400'
                                    }`}
                                >
                                    {link.category}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
            <span className="block px-1 py-1 text-xs/4">Locket is your social link sharing read-later app for developers</span>
        </div>
    );
}
