<x-filament-panels::page>
    <div x-data="{
        selected: 'received',
        title: 'ご予約受付完了メール',
        subject: '【CELLVIA】ご予約を承りました',
        activeTab: 'preview',
        templates: {
            received: { title: 'ご予約受付完了メール', badge: '予約システム', color: 'info', subject: '【CELLVIA】ご予約を承りました' },
            confirmed: { title: 'ご予約確定メール', badge: '予約システム', color: 'success', subject: '【CELLVIA】ご予約が確定いたしました' },
            order: { title: 'ご注文完了メール', badge: 'SHOP EC', color: 'purple', subject: '【CELLVIA】ご注文ありがとうございます' },
            shipping: { title: '商品発送完了メール', badge: 'SHOP EC', color: 'purple', subject: '【CELLVIA】商品を発送いたしました' }
        },
        select(key) {
            this.selected = key;
            this.title = this.templates[key].title;
            this.subject = this.templates[key].subject;
        }
    }">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <template x-for="(item, key) in templates" :key="key">
                <div 
                    @click="select(key)"
                    :class="selected === key 
                        ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/40 shadow-lg' 
                        : 'border-gray-800 bg-gray-900 hover:border-gray-700 hover:bg-gray-900/80 shadow-md'"
                    class="rounded-2xl border p-5 cursor-pointer transition-all duration-200 flex flex-col justify-between h-48 relative overflow-hidden group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h4 class="font-bold text-sm text-white group-hover:text-amber-400 transition-colors leading-snug" x-text="item.title"></h4>
                            <span 
                                :class="{
                                    'bg-blue-500/20 text-blue-400 border-blue-500/30': item.color === 'info',
                                    'bg-emerald-500/20 text-emerald-400 border-emerald-500/30': item.color === 'success',
                                    'bg-purple-500/20 text-purple-400 border-purple-500/30': item.color === 'purple'
                                }"
                                class="text-[10px] px-2 py-0.5 rounded-md font-bold border whitespace-nowrap"
                                x-text="item.badge"
                            ></span>
                        </div>
                        
                        <div class="bg-gray-950/70 rounded-lg p-2.5 border border-gray-800/80 mb-3">
                            <p class="text-[10px] text-gray-500 font-bold mb-0.5">件名プレビュー</p>
                            <p class="text-xs text-gray-300 font-medium truncate" x-text="item.subject"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-800/80">
                        <span 
                            class="text-xs font-bold transition-colors"
                            :class="selected === key ? 'text-amber-400' : 'text-gray-500'"
                            x-text="selected === key ? '✓ 選択中' : '選択して編集'"
                        ></span>
                        <x-filament::icon 
                            icon="heroicon-m-arrow-right-circle" 
                            class="w-5 h-5 transition-transform"
                            ::class="selected === key ? 'text-amber-400 translate-x-1' : 'text-gray-600 group-hover:text-gray-400'"
                        />
                    </div>
                </div>
            </template>
        </div>

        <x-filament::section icon="heroicon-o-document-text">
            <x-slot name="heading">
                <div class="flex items-center gap-3">
                    <span x-text="title" class="text-white font-bold"></span>
                    <x-filament::badge color="warning">ワークスペース</x-filament::badge>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex items-center bg-gray-950 p-1 rounded-lg border border-gray-800">
                    <button 
                        type="button" 
                        @click="activeTab = 'preview'" 
                        :class="activeTab === 'preview' ? 'bg-amber-500 text-gray-950 font-bold' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1"
                    >
                        <x-filament::icon icon="heroicon-m-eye" class="w-3.5 h-3.5" />
                        ビュー
                    </button>
                    <button 
                        type="button" 
                        @click="activeTab = 'edit'" 
                        :class="activeTab === 'edit' ? 'bg-amber-500 text-gray-950 font-bold' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1"
                    >
                        <x-filament::icon icon="heroicon-m-pencil-square" class="w-3.5 h-3.5" />
                        編集
                    </button>
                </div>
            </x-slot>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400">件名 (메일 제목)</label>
                    <input 
                        type="text" 
                        x-model="subject"
                        class="w-full bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
                    />
                </div>

                <div x-show="activeTab === 'preview'" class="bg-white rounded-xl overflow-hidden border border-gray-800 h-[520px]">
                    <iframe :src="'/preview-email/' + selected" class="w-full h-full border-0"></iframe>
                </div>

                <div x-show="activeTab === 'edit'" class="space-y-3" style="display: none;">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-400">HTML テンプレートコード</label>
                        <textarea 
                            rows="16" 
                            class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3 text-xs font-mono text-gray-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none leading-relaxed"
                            placeholder="HTML テンプレートコードを入力してください..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <x-filament::button color="gray" size="sm">キャンセル</x-filament::button>
                        <x-filament::button color="warning" icon="heroicon-m-check" size="sm">設定を保存</x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>