<x-layouts.app title="Detail Member">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('members.index') }}" class="hover:text-indigo-600">Member</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">{{ $member->name }}</span>
    </nav>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="text-center mb-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                <h3 class="font-bold text-gray-900 text-lg">{{ $member->name }}</h3>
                <p class="text-sm text-gray-500">{{ $member->phone_number }}</p>
                @if($member->email)<p class="text-sm text-gray-400">{{ $member->email }}</p>@endif
            </div>
            <div class="bg-amber-50 rounded-xl p-4 text-center">
                <p class="text-xs text-amber-600 font-medium mb-1">Saldo Poin</p>
                <p class="text-2xl font-bold text-amber-700">{{ number_format($member->points_balance, 0) }}</p>
            </div>
        </div>
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Riwayat Poin</h3>
                @if($member->points->count())
                    <div class="space-y-2">
                        @foreach($member->points as $p)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div>
                                    <p class="text-sm text-gray-700">{{ $p->notes ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $p->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <span class="text-sm font-semibold {{ $p->type === 'earn' ? 'text-emerald-600' : 'text-red-600' }}">{{ $p->type === 'earn' ? '+' : '-' }}{{ number_format($p->points, 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">Belum ada riwayat poin</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
