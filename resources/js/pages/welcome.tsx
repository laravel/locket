import { dashboard } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import BottomAuthNav from '@/components/bottom-auth-nav';
import AppLogoIcon from '@/components/app-logo-icon';
import SafeTextWithBreaks from '@/components/safe-text-with-breaks';
import { LayoutGrid, Bookmark, BookmarkCheck, TrendingUp, ExternalLink } from 'lucide-react';

type Status = {
    id: number;
    status: string;
    created_at: string;
    user: { name: string; avatar: string; avatar_fallback: string; };
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

        setBookmarkingLinks(prev => new Set(prev).add(linkId));

        router.post(`/links/${linkId}/bookmark`, {}, {
            onSuccess: () => {
                router.reload({ only: ['statuses'] });
            },
            onError: (errors) => {
                console.error('Failed to bookmark:', errors);
            },
            onFinish: () => {
                setBookmarkingLinks(prev => {
                    const next = new Set(prev);
                    next.delete(linkId);
                    return next;
                });
            }
        });
    };

    // Sort statuses by created_at in descending order (newest first)
    // Only 20 so this is fine
    const sortedStatuses = [...statuses].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );

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
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-3 pb-20 text-[#1b1b18] lg:justify-center lg:p-4 dark:bg-[#0a0a0a]">
                <div className="flex flex-col w-full items-center justify-center lg:grow">
                    <div className="z-20 py-2 relative w-full sticky bg-white bg-[#FDFDFC]/95 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/80 top-0">
                        <Link
                            href={dashboard()}
                            title="Dashboard"
                            className={`absolute top-3 right-0.5 rounded-md border border-[#19140035] p-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] ${isFirstLoad.current ? 'starting:opacity-0 opacity-100 transition-opacity duration-500' : ''}`}
                        >
                            <LayoutGrid className="size-5"/>
                        </Link>
                        <AppLogoIcon className="size-12 lg:size-18 mx-auto" />
                    </div>

                    <div className="flex w-full max-w-7xl flex-col lg:flex-row gap-6 grow">
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
                        <main className="flex flex-col gap-3 lg:flex-1 lg:min-w-0">
                            <ul className={`flex flex-col divide-y divide-[#19140014] dark:divide-[#3E3E3A] ${isFirstLoad.current ? 'starting:opacity-0 opacity-100 transition-opacity duration-750' : ''} `}>
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
                                                        <span className="absolute uppercase text-2xl font-bold text-white">{s.user.name.substring(0, 1)}</span>
                                                        <img src={s.user.avatar_fallback} alt={s.user.name} />
                                                    </div>
                                                </AvatarFallback>
                                            </Avatar>
                                            <div className="min-w-0 flex-1">
                                                <div className="mb-1 flex items-center justify-between gap-2 text-sm">
                                                    <span className="truncate font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{s.user.name}</span>
                                                    <div className="flex items-center gap-2">
                                                    <TimeStamp iso={s.created_at} />

                                                        {auth.user && s.link && (
                                                            <button
                                                                onClick={() => handleBookmark(s.link!.id)}
                                                                disabled={bookmarkingLinks.has(s.link.id) || auth.bookmarked_link_ids.includes(s.link.id)}
                                                                className={`flex items-center justify-center rounded-md p-1 disabled:cursor-not-allowed disabled:opacity-80 ${
                                                                    auth.bookmarked_link_ids.includes(s.link.id)
                                                                        ? 'text-blue-600 dark:text-blue-400'
                                                                        : 'text-[#3e3e3a]/60 hover:text-[#1b1b18] dark:text-[#a3a3a3]/60 dark:hover:text-[#EDEDEC]'
                                                                }`}
                                                                title={auth.bookmarked_link_ids.includes(s.link.id)
                                                                    ? `Bookmarked: ${s.link.title || s.link.url}`
                                                                    : `Bookmark: ${s.link.title || s.link.url}`}
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
                                                <SafeTextWithBreaks
                                                    text={s.status}
                                                    className="wrap-break-word text-[15px] leading-relaxed text-[#1b1b18] dark:text-[#EDEDEC]"
                                                />
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
        <time className="text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80 text-xs" dateTime={iso} title={title}>
            {display}
        </time>
    );
}

function TrendingLinksSection({
    trendingLinks,
    auth,
    handleBookmark,
    bookmarkingLinks,
    mobile
}: {
    trendingLinks: TrendingLink[];
    auth: SharedData['auth'];
    handleBookmark: (linkId: number) => void;
    bookmarkingLinks: Set<number>;
    mobile: boolean;
}) {
    if (trendingLinks.length === 0) {
        return (
            <div className={`rounded-lg border border-[#19140014] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] ${mobile ? 'mb-6' : ''}`}>
                <div className="mb-3 flex items-center gap-2">
                    <TrendingUp className="size-4 text-[#3e3e3a] dark:text-[#a3a3a3]" />
                    <h2 className="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Trending Today
                    </h2>
                </div>
                <p className="text-xs text-[#3e3e3a] dark:text-[#a3a3a3]">
                    No trending links yet today. Be the first to share something!
                </p>
            </div>
        );
    }

    return (
        <div className={`rounded-lg border border-[#19140014] bg-white dark:border-[#3E3E3A] dark:bg-[#0a0a0a] ${mobile ? 'mb-6' : ''}`}>
            <div className="p-4 border-b border-[#19140014] dark:border-[#3E3E3A]">
                <div className="flex items-center gap-2">
                    <TrendingUp className="size-4 text-[#3e3e3a] dark:text-[#a3a3a3]" />
                    <h2 className="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Trending Today
                    </h2>
                </div>
            </div>
            <div className={`${mobile ? 'flex gap-3 overflow-x-scroll p-4' : 'divide-y divide-[#19140014] dark:divide-[#3E3E3A]'}`}>
                {trendingLinks.map((link) => (
                    <div
                        key={link.id}
                        className={`${mobile
                            ? 'flex-shrink-0 w-64 rounded border border-[#19140014] p-3 dark:border-[#3E3E3A]'
                            : 'p-4'
                        }`}
                    >
                        <div className="flex items-start justify-between gap-2 mb-2">
                            <div className="min-w-0 flex-1">
                                <a
                                    href={link.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="block group"
                                >
                                    <div className="flex items-start gap-1">
                                        <h3 className="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {link.title}
                                        </h3>
                                        <ExternalLink className="size-3 text-[#3e3e3a]/60 dark:text-[#a3a3a3]/60 flex-shrink-0 mt-0.5" />
                                    </div>
                                    <p className="text-xs text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80 truncate mt-1">
                                        {new URL(link.url).hostname}
                                    </p>
                                </a>
                            </div>

                            <div className="flex items-center flex-shrink-0">
                                {auth.user && (
                                    <button
                                        onClick={() => handleBookmark(link.id)}
                                        disabled={bookmarkingLinks.has(link.id) || auth.bookmarked_link_ids.includes(link.id)}
                                        className={`flex items-center justify-center rounded p-1 disabled:cursor-not-allowed disabled:opacity-50 ${
                                            auth.bookmarked_link_ids.includes(link.id)
                                                ? 'text-blue-600 dark:text-blue-400'
                                                : 'text-[#3e3e3a]/60 hover:text-[#1b1b18] dark:text-[#a3a3a3]/60 dark:hover:text-[#EDEDEC]'
                                        }`}
                                        title={auth.bookmarked_link_ids.includes(link.id)
                                            ? `Bookmarked: ${link.title || link.url}`
                                            : `Bookmark: ${link.title || link.url}`}
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
                                <span className="text-xs text-[#3e3e3a]/80 dark:text-[#a3a3a3]/80">
                                    {link.bookmark_count}
                                </span>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <span className={`inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ${
                                link.category === 'read' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' :
                                link.category === 'watch' ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' :
                                link.category === 'reference' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' :
                                'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400'
                            }`}>
                                {link.category}
                            </span>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
