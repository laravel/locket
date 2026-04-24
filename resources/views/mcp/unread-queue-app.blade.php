<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            body { font-family: var(--font-sans, system-ui, sans-serif); }
        </style>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('queue', {
                    items: [],
                    message: '',
                    loading: true,
                    busyId: null,
                });
            });
        </script>
    </x-slot:head>

    <div class="flex flex-col h-screen">
        <div class="flex-shrink-0 px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
            <h1 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Unread queue</h1>
            <span x-data x-show="!$store.queue.loading" x-cloak x-text="$store.queue.items.length + ' item' + ($store.queue.items.length === 1 ? '' : 's')" class="text-[11px] text-neutral-400 dark:text-neutral-500"></span>
        </div>

        <div x-data x-show="$store.queue.loading" class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="i in 3">
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3 animate-pulse">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
                    <div class="mt-2 h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
                </div>
            </template>
        </div>

        <div x-data x-show="!$store.queue.loading && $store.queue.items.length === 0" x-cloak class="flex-1 flex items-center justify-center">
            <p class="text-xs text-neutral-400 dark:text-neutral-500" x-text="$store.queue.message || 'Your reading queue is empty.'"></p>
        </div>

        <div x-data x-show="!$store.queue.loading && $store.queue.items.length > 0" x-cloak class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="item in $store.queue.items" :key="item.user_link_id">
                <div
                    class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-3 transition-opacity"
                    :class="{ 'opacity-50': $store.queue.busyId === item.user_link_id }"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="#" @click.prevent="locket.openLink(item.url)" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline line-clamp-2 break-words" x-text="item.title || item.url"></a>
                            <p x-show="item.description" class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 break-words" x-text="item.description"></p>
                            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-neutral-400 dark:text-neutral-500">
                                <span
                                    x-text="item.category"
                                    :class="@js(\App\Enums\LinkCategory::badgeClassMap())[item.category] ?? ''"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium"
                                ></span>
                            </div>
                        </div>
                        <button
                            @click="locket.startReading(item.user_link_id)"
                            :disabled="$store.queue.busyId !== null"
                            class="flex-shrink-0 text-[11px] font-medium px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                        >Start reading</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script type="module">
    createMcpApp(async (app) => {
        function loadData(data) {
            Object.assign(Alpine.store('queue'), {
                items: data.items ?? [],
                message: data.message ?? '',
                loading: false,
                busyId: null,
            });
        }

        // Parse the tool result payload into our `{items, message}` shape.
        // Returns null when the result doesn't belong to this app so unrelated
        // tool calls (eg. get-recent-links) can't corrupt the store.
        function extractQueuePayload(toolResult) {
            if (toolResult.structuredContent && Array.isArray(toolResult.structuredContent.items)) {
                return toolResult.structuredContent;
            }

            const text = toolResult.content?.[0]?.text;
            if (typeof text !== 'string') return null;

            try {
                const data = JSON.parse(text);
                return Array.isArray(data?.items) ? data : null;
            } catch {
                return null;
            }
        }

        window.locket = {
            openLink(url) { app.openLink(url); },
            async startReading(userLinkId) {
                const item = Alpine.store('queue').items.find(i => i.user_link_id === userLinkId);
                Alpine.store('queue').busyId = userLinkId;

                try {
                    const result = await app.callServerTool('start-reading', { user_link_id: userLinkId });
                    const data = extractQueuePayload(result);
                    if (data) {
                        loadData(data);
                        if (item?.url) {
                            await app.sendMessage(`Please read and summarise this link for me: ${item.url}`);
                        }
                    } else {
                        Alpine.store('queue').busyId = null;
                        Alpine.store('queue').message = result.content?.[0]?.text ?? 'Could not start reading.';
                    }
                } catch (e) {
                    Alpine.store('queue').busyId = null;
                    Alpine.store('queue').message = e?.message ?? 'Could not start reading.';
                }
            },
        };

        app.onToolResult((params) => {
            // Host notifications wrap the tool response in `.result`; accept
            // the flat shape too for robustness.
            const toolResult = params.result ?? params;

            if (toolResult.isError) {
                if (!Alpine.store('queue').loading) return;
                loadData({
                    items: [],
                    message: toolResult.content?.[0]?.text ?? 'Something went wrong.',
                });
                return;
            }

            const data = extractQueuePayload(toolResult);
            if (data) loadData(data);
        });
    });
    </script>
</x-mcp::app>
