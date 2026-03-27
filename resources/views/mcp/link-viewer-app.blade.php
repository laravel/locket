<x-mcp::app>
    <x-slot:head>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: ['selector', '[data-theme="dark"]'] }
        </script>
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: var(--font-sans, system-ui, sans-serif); }
        </style>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('feed', {
                    links: [],
                    message: '',
                    loading: true,
                });
            });
        </script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    </x-slot:head>

    <div class="flex flex-col h-screen">
        {{-- Header --}}
        <div class="flex-shrink-0 px-4 py-3 border-b border-neutral-200 dark:border-neutral-800">
            <h1 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Link Viewer</h1>
        </div>

        {{-- Loading skeleton --}}
        <div x-data x-show="$store.feed.loading" class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="i in 4">
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3 animate-pulse">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
                    <div class="mt-2 h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
                    <div class="mt-2 h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-1/2"></div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div x-data x-show="!$store.feed.loading && $store.feed.links.length === 0" x-cloak class="flex-1 flex items-center justify-center">
            <p class="text-xs text-neutral-400 dark:text-neutral-500" x-text="$store.feed.message || 'No links found.'"></p>
        </div>

        {{-- Links --}}
        <div x-data x-show="!$store.feed.loading && $store.feed.links.length > 0" x-cloak class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="link in $store.feed.links" :key="link.id">
                <div class="group rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3 hover:border-neutral-300 dark:hover:border-neutral-700 transition-colors">
                    <a href="#" @click.prevent="locket.openLink(link.url)" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline line-clamp-2 break-words" x-text="link.title || 'Untitled'"></a>

                    <p x-show="link.description" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 break-words" x-text="link.description"></p>

                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-[11px] text-neutral-400 dark:text-neutral-500">
                            <span
                                x-text="link.category"
                                :class="{
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': link.category === 'read',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': link.category === 'reference',
                                    'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300': link.category === 'watch',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': !['read', 'reference', 'watch'].includes(link.category),
                                }"
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium"
                            ></span>

                            {{-- Recent links: submitted_by + created_at --}}
                            <template x-if="link.submitted_by">
                                <span class="contents">
                                    <span>&middot;</span>
                                    <span x-text="link.submitted_by"></span>
                                    <span>&middot;</span>
                                    <span x-text="link.created_at"></span>
                                </span>
                            </template>

                            {{-- Trending links: bookmark count --}}
                            <template x-if="link.bookmark_count !== undefined">
                                <span class="contents">
                                    <span>&middot;</span>
                                    <span x-text="link.bookmark_count + ' ' + (link.bookmark_count === 1 ? 'bookmark' : 'bookmarks')"></span>
                                </span>
                            </template>
                        </div>
                        <button @click="locket.summarize(link.url)" class="text-[11px] font-medium text-neutral-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors">Summarize</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script type="module">
    createMcpApp(async (app) => {
        window.locket = {
            async summarize(url) {
                await app.sendMessage([{ type: 'text', text: `Please read and summarise the content at this URL: ${url}` }]);
            },
            openLink(url) { app.openLink(url); },
        };

        function loadData(data) {
            Alpine.store('feed').links = data.links ?? [];
            Alpine.store('feed').message = data.message ?? '';
            Alpine.store('feed').loading = false;
        }

        app.onToolResult((result) => {
            if (result.isError) {
                loadData({ links: [], message: result.content?.[0]?.text ?? 'Something went wrong.' });
                return;
            }
            const data = result.structuredContent ?? JSON.parse(result.content?.[0]?.text ?? '{}');
            loadData(data);
        });
    });
    </script>
</x-mcp::app>
