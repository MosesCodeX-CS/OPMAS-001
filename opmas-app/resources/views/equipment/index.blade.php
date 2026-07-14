@extends('layouts.app')
@section('title', 'Equipment')

@section('content')
<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">Plant Equipment</h1>
        <p class="text-sm text-[#6B7A90] mt-1">Status registry, maintenance log, and hardware service intervals.</p>
    </div>
    @if(auth()->user()->isSystemAdmin())
        <button onclick="openAddModal()" class="px-4 py-2 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5 self-start md:self-auto">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Add Equipment</span>
        </button>
    @endif
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="kijabe-card p-5 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Total Equipment</p>
        <p class="text-3xl font-bold text-[#1A2A3A] font-mono">{{ $equipment->count() }}</p>
    </div>
    <div class="kijabe-card p-5 border-l-4 border-l-emerald-500 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Online</p>
        <p class="text-3xl font-bold text-emerald-600 font-mono">{{ $statusCounts['ONLINE'] ?? 0 }}</p>
    </div>
    <div class="kijabe-card p-5 border-l-4 border-l-red-600 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Fault</p>
        <p class="text-3xl font-bold text-red-600 font-mono">{{ $statusCounts['FAULT'] ?? 0 }}</p>
    </div>
    <div class="kijabe-card p-5 border-l-4 border-l-amber-500 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Maintenance Due</p>
        <p class="text-3xl font-bold text-amber-600 font-mono">{{ $upcomingService->count() }}</p>
    </div>
</div>

<!-- Equipment Grid Layout -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($equipment as $item)
        <div class="kijabe-card p-6 bg-white relative flex flex-col justify-between group hover:scale-[1.01] transition-all duration-300">
            <div>
                <!-- Badge and Code -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-mono text-[#2B8AC6] font-bold uppercase tracking-wider bg-[#2B8AC6]/10 px-2.5 py-1 rounded-lg">{{ $item->code }}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded tracking-wide uppercase
                        {{ $item->status === 'ONLINE' ? 'bg-emerald-100 text-emerald-800' : ($item->status === 'FAULT' ? 'bg-rose-100 text-rose-800' : ($item->status === 'MAINTENANCE' ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-800')) }}">
                        {{ $item->status }}
                    </span>
                </div>

                <h3 class="text-lg font-bold text-[#1B3A6B] mb-2">{{ $item->name }}</h3>
                
                @if($item->notes)
                    <p class="text-xs text-[#6B7A90] line-clamp-3 mb-4 leading-relaxed bg-[#F4F6F9] p-3 rounded-lg border border-[#DDE3EE]">{{ $item->notes }}</p>
                @else
                    <p class="text-xs text-gray-400 italic mb-4">No notes recorded.</p>
                @endif
            </div>

            <div>
                <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-[#6B7A90]">Last Service Date:</span>
                        <span class="text-[#1A2A3A] font-semibold">{{ $item->last_service?->format('Y-m-d') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7A90]">Next Service Interval:</span>
                        <span class="text-[#1A2A3A] font-semibold">{{ $item->next_service?->format('Y-m-d') ?? '—' }}</span>
                    </div>
                </div>

                <!-- Admin Action Triggers -->
                @if(!auth()->user()->isUser())
                    <div class="flex justify-end gap-2 mt-5 border-t border-gray-100 pt-4">
                        <button onclick="openEditModal({{ json_encode($item) }})" class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-[#1A2A3A] text-xs font-semibold flex items-center gap-1 transition shadow-sm bg-white">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Edit</span>
                        </button>

                        @if(auth()->user()->isSystemAdmin())
                            <form action="{{ route('equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this equipment? This action is permanent.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 text-xs font-semibold flex items-center gap-1 transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-xl p-12 text-center bg-white border border-[#DDE3EE] shadow-sm">
            <div class="w-12 h-12 rounded-full bg-[#2B8AC6]/10 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="cpu" class="w-6 h-6 text-[#2B8AC6]"></i>
            </div>
            <h3 class="text-sm font-semibold text-[#1A2A3A]">No Equipment Configured</h3>
            <p class="text-xs text-[#6B7A90] mt-1">Please register equipment to track plant status.</p>
        </div>
    @endforelse
</div>

<!-- Modals -->

<!-- Add Equipment Modal -->
@if(auth()->user()->isSystemAdmin())
    <div id="addModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl w-full max-w-lg p-8 border border-gray-200 relative shadow-2xl mx-4">
            <button onclick="closeAddModal()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold text-[#1B3A6B] mb-6">Register New Equipment</h2>
            
            <form action="{{ route('equipment.store') }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Code *</label>
                    <input type="text" name="code" required placeholder="e.g. COMP-02" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Secondary Compressor" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Initial Status *</label>
                    <select name="status" class="w-full rounded-lg px-4 py-2.5 kijabe-input bg-white">
                        <option value="ONLINE">ONLINE</option>
                        <option value="OFFLINE">OFFLINE</option>
                        <option value="MAINTENANCE">MAINTENANCE</option>
                        <option value="FAULT">FAULT</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Last Service</label>
                        <input type="date" name="last_service" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Next Service</label>
                        <input type="date" name="next_service" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Hardware details or notes" class="w-full rounded-lg px-4 py-2.5 kijabe-input"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-xs font-semibold text-white">Create Equipment</button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Edit Equipment Modal -->
@if(!auth()->user()->isUser())
    <div id="editModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl w-full max-w-lg p-8 border border-gray-200 relative shadow-2xl mx-4">
            <button onclick="closeEditModal()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <h2 class="text-xl font-bold text-[#1B3A6B] mb-6">Modify Equipment Logs</h2>
            
            <form id="editForm" method="POST" class="space-y-4 text-sm">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Code</label>
                    <input type="text" id="edit_code" name="code" required class="w-full rounded-lg px-4 py-2.5 kijabe-input disabled:opacity-50" {{ auth()->user()->isSystemAdmin() ? '' : 'disabled' }}>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Name</label>
                    <input type="text" id="edit_name" name="name" required class="w-full rounded-lg px-4 py-2.5 kijabe-input disabled:opacity-50" {{ auth()->user()->isSystemAdmin() ? '' : 'disabled' }}>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Status *</label>
                    <select id="edit_status" name="status" class="w-full rounded-lg px-4 py-2.5 kijabe-input bg-white">
                        <option value="ONLINE">ONLINE</option>
                        <option value="OFFLINE">OFFLINE</option>
                        <option value="MAINTENANCE">MAINTENANCE</option>
                        <option value="FAULT">FAULT</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Last Service</label>
                        <input type="date" id="edit_last_service" name="last_service" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Next Service</label>
                        <input type="date" id="edit_next_service" name="next_service" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Notes</label>
                    <textarea id="edit_notes" name="notes" rows="3" class="w-full rounded-lg px-4 py-2.5 kijabe-input"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-xs font-semibold text-white">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function openEditModal(equipment) {
    const editForm = document.getElementById('editForm');
    editForm.action = `/equipment/${equipment.id}`;

    document.getElementById('edit_code').value = equipment.code;
    document.getElementById('edit_name').value = equipment.name;
    document.getElementById('edit_status').value = equipment.status;
    
    if (equipment.last_service) {
        document.getElementById('edit_last_service').value = equipment.last_service.split('T')[0];
    } else {
        document.getElementById('edit_last_service').value = '';
    }
    
    if (equipment.next_service) {
        document.getElementById('edit_next_service').value = equipment.next_service.split('T')[0];
    } else {
        document.getElementById('edit_next_service').value = '';
    }

    document.getElementById('edit_notes').value = equipment.notes || '';

    document.getElementById('editModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection