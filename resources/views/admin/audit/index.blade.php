@extends('layouts.admin')

@section('title', 'Riwayat & Log Audit')

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Riwayat &amp; Log Audit</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Aktivitas penting Admin/System yang tercatat melalui panel ini.</p>
    </div>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Log audit">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-2 gap-2 lg:grid-cols-6">
            <label class="block text-xs font-bold text-pk-brown-soft">Tanggal
                <input type="date" name="tanggal" value="{{ $filters['tanggal'] }}" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="block text-xs font-bold text-pk-brown-soft">User
                <input type="search" name="user" value="{{ $filters['user'] }}" placeholder="Nama user…" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="block text-xs font-bold text-pk-brown-soft">Modul
                <select name="modul" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" @selected($filters['modul'] === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-bold text-pk-brown-soft">Status
                <select name="status" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </label>
            <div class="col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">Filter</button>
                <a href="{{ route('admin.audit.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
            </div>
        </form>

        @if (empty($rows))
            <div class="mt-4">
                <x-admin.empty-state title="Belum ada aktivitas tercatat"
                    hint="Log terisi otomatis setiap admin mengubah menu, stok, atau meja melalui panel ini." />
            </div>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Waktu</th>
                            <th class="pb-2 pr-3">User</th>
                            <th class="pb-2 pr-3">Modul</th>
                            <th class="pb-2 pr-3">Aktivitas</th>
                            <th class="pb-2 pr-3">Detail</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="whitespace-nowrap py-2.5 pr-3 text-xs text-pk-brown-soft">{{ $r['time'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3 font-bold text-pk-brown">{{ $r['user'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3 font-mono text-xs">{{ $r['modul'] }}</td>
                                <td class="py-2.5 pr-3">{{ $r['aktivitas'] }}</td>
                                <td class="max-w-[240px] truncate py-2.5 pr-3 text-xs text-pk-brown-soft" title="{{ $r['detail'] }}">{{ $r['detail'] ?? '-' }}</td>
                                <td class="whitespace-nowrap py-2.5 text-right"><x-admin.badge :status="$r['status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
