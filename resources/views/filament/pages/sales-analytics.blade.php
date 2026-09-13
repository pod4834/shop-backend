<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-6 rounded-xl border border-gray-800 bg-gray-900/60 shadow-sm">
                <div class="text-xs font-bold text-gray-400 mb-1">本日の売上 (오늘 매출)</div>
                <div class="text-2xl font-black text-amber-400">
                    ¥{{ number_format($todaySales) }}
                </div>
            </div>

            <div class="p-6 rounded-xl border border-gray-800 bg-gray-900/60 shadow-sm">
                <div class="text-xs font-bold text-gray-400 mb-1">今月の売上 (이번 달 매출)</div>
                <div class="text-2xl font-black text-blue-400">
                    ¥{{ number_format($monthlySales) }}
                </div>
            </div>

            <div class="p-6 rounded-xl border border-gray-800 bg-gray-900/60 shadow-sm">
                <div class="text-xs font-bold text-gray-400 mb-1">累計売上 (총 누적 매출)</div>
                <div class="text-2xl font-black text-emerald-400">
                    ¥{{ number_format($totalSales) }}
                </div>
            </div>

            <div class="p-6 rounded-xl border border-gray-800 bg-gray-900/60 shadow-sm">
                <div class="text-xs font-bold text-gray-400 mb-1">総注文数 (총 주문 건수)</div>
                <div class="text-2xl font-black text-purple-400">
                    {{ number_format($totalOrdersCount) }} <span class="text-sm font-normal text-gray-400">件</span>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-xl border border-gray-800 bg-gray-900/60 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-gray-200 border-b border-gray-800 pb-3">
                最近の注文履歴 (최근 주문 내역)
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-gray-800/60 text-xs font-bold uppercase text-gray-400 border-b border-gray-800">
                        <tr>
                            <th class="p-3">注文ID</th>
                            <th class="p-3">ユーザーID</th>
                            <th class="p-3">Total CV</th>
                            <th class="p-3">Total PV</th>
                            <th class="p-3">金額</th>
                            <th class="p-3">ステータス</th>
                            <th class="p-3">注文日時</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-800/30">
                                <td class="p-3 font-bold text-amber-400">#{{ $order->id }}</td>
                                <td class="p-3">{{ $order->user_id }}</td>
                                <td class="p-3">{{ number_format($order->total_cv ?? 0) }}</td>
                                <td class="p-3">{{ number_format($order->total_pv ?? 0) }}</td>
                                <td class="p-3 font-bold text-white">¥{{ number_format($order->total_amount ?? 0) }}</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        {{ $order->status ?? 'pending' }}
                                    </span>
                                </td>
                                <td class="p-3 text-xs text-gray-400">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">注文データが存在しません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>