<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            body { font-family: var(--font-sans, system-ui, sans-serif); }
        </style>
        <script>
            // Stash the MCP SDK `app` handle on the window so Alpine store
            // methods (defined below at alpine:init) can call it without a
            // closure dependency on createMcpApp's async callback.
            window.mcp = null;

            document.addEventListener('alpine:init', () => {
                Alpine.store('trending', {
                    links: [],
                    message: '',
                    loading: true,
                    bookmarked: {},
                    busyId: null,
                    error: '',

                    openLink(url) {
                        console.log('[locket] openLink', url);
                        window.mcp?.openLink(url);
                    },

                    async summarize(url) {
                        console.log('[locket] summarize', url);
                        if (!window.mcp) return;
                        await window.mcp.sendMessage(`Please read and summarise this link for me: ${url}`);
                    },

                    async bookmark(link) {
                        console.log('[locket] bookmark clicked', link);

                        if (!window.mcp) {
                            this.error = 'MCP app not initialised yet. Reload the iframe.';
                            return;
                        }

                        this.busyId = link.id;
                        this.error = '';

                        try {
                            const result = await window.mcp.callServerTool('add-link', { url: link.url });
                            console.log('[locket] add-link result', result);

                            if (result?.isError) {
                                this.error = result.content?.[0]?.text ?? 'Could not bookmark link.';
                            } else {
                                this.bookmarked = { ...this.bookmarked, [link.id]: true };
                            }
                        } catch (e) {
                            console.error('[locket] bookmark failed', e);
                            this.error = e?.message ?? 'Could not bookmark link.';
                        } finally {
                            this.busyId = null;
                        }
                    },
                });
            });
        </script>
    </x-slot:head>

    <div class="flex flex-col h-screen">
        <div class="flex-shrink-0 px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
            <h1 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Trending today</h1>
            <span x-data x-show="!$store.trending.loading" x-cloak x-text="$store.trending.links.length + ' link' + ($store.trending.links.length === 1 ? '' : 's')" class="text-[11px] text-neutral-400 dark:text-neutral-500"></span>
        </div>

        <div x-data x-show="$store.trending.error" x-cloak class="flex-shrink-0 px-4 py-2 bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-900 text-[11px] text-red-700 dark:text-red-300 flex items-center justify-between gap-3">
            <span x-text="$store.trending.error"></span>
            <button @click="$store.trending.error = ''" class="flex-shrink-0 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200">✕</button>
        </div>

        <div x-data x-show="$store.trending.loading" class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="i in 3">
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3 animate-pulse">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
                    <div class="mt-2 h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
                </div>
            </template>
        </div>

        <div x-data x-show="!$store.trending.loading && $store.trending.links.length === 0" x-cloak class="flex-1 flex items-center justify-center">
            <p class="text-xs text-neutral-400 dark:text-neutral-500" x-text="$store.trending.message || 'No trending links today.'"></p>
        </div>

        <div x-data x-show="!$store.trending.loading && $store.trending.links.length > 0" x-cloak class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="link in $store.trending.links" :key="link.id">
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="#" @click.prevent="$store.trending.openLink(link.url)" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline line-clamp-2 break-words" x-text="link.title || link.url"></a>
                            <p x-show="link.description" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 break-words" x-text="link.description"></p>
                            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-neutral-400 dark:text-neutral-500">
                                <span
                                    x-text="link.category"
                                    :class="@js(\App\Enums\LinkCategory::badgeClassMap())[link.category] ?? ''"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium"
                                ></span>
                                <span>&middot;</span>
                                <span x-text="link.bookmark_count + ' ' + (link.bookmark_count === 1 ? 'bookmark' : 'bookmarks')"></span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex flex-col items-end gap-1">
                            <button
                                type="button"
                                @click="$store.trending.bookmark(link)"
                                :disabled="$store.trending.busyId === link.id || $store.trending.bookmarked[link.id]"
                                x-text="$store.trending.bookmarked[link.id] ? '✓ Saved' : ($store.trending.busyId === link.id ? 'Saving…' : 'Bookmark')"
                                class="text-[11px] font-medium px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:bg-emerald-600 disabled:cursor-default transition-colors"
                            ></button>
                            <button
                                type="button"
                                @click="$store.trending.summarize(link.url)"
                                class="text-[11px] font-medium text-neutral-500 hover:text-blue-500 dark:text-neutral-400 dark:hover:text-blue-400 transition-colors"
                            >Summarise</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script type="module">
    createMcpApp(async (app) => {
        window.mcp = app;
        console.log('[locket] MCP app ready');

        function loadData(data) {
            Object.assign(Alpine.store('trending'), {
                links: data.links ?? [],
                message: data.message ?? '',
                loading: false,
                busyId: null,
            });
        }

        function extractTrendingPayload(toolResult) {
            if (toolResult.structuredContent && Array.isArray(toolResult.structuredContent.links)) {
                return toolResult.structuredContent;
            }

            const text = toolResult.content?.[0]?.text;
            if (typeof text !== 'string') return null;

            try {
                const data = JSON.parse(text);
                return Array.isArray(data?.links) ? data : null;
            } catch {
                return null;
            }
        }

        app.onToolResult((params) => {
            const toolResult = params.result ?? params;

            if (toolResult.isError) {
                if (!Alpine.store('trending').loading) return;
                loadData({
                    links: [],
                    message: toolResult.content?.[0]?.text ?? 'Something went wrong.',
                });
                return;
            }

            const data = extractTrendingPayload(toolResult);
            if (data) loadData(data);
        });
    });
    </script>
</x-mcp::app>
