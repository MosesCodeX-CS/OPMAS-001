@extends('layouts.app')
@section('title', 'User Accounts')

@section('content')
<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">User Administration</h1>
        <p class="text-sm text-[#6B7A90] mt-1">Manage personnel login credentials, assign dashboard roles, and audit security permissions.</p>
    </div>
    <button onclick="openAddModal()" class="px-4 py-2 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5 self-start md:self-auto">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Add User Account</span>
    </button>
</div>

<!-- Users Table List -->
<div class="kijabe-card overflow-hidden bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-[#6B7A90] bg-gray-50">
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Created At</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#1A2A3A]">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-semibold flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#2B8AC6]/10 text-[#2B8AC6] flex items-center justify-center font-bold text-xs uppercase">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span>{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <span class="text-[9px] bg-gray-150 text-[#6B7A90] px-2 py-0.5 rounded font-mono uppercase font-bold">You</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-[#6B7A90] font-mono text-xs">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded text-white uppercase tracking-wider
                                {{ $user->isSystemAdmin() ? 'role-badge-system_admin' : ($user->isAdmin() ? 'role-badge-admin' : 'role-badge-user') }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-[#6B7A90] font-mono text-xs">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="openEditModal({{ json_encode($user) }})" class="p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-[#1A2A3A] transition shadow-sm" title="Edit account">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user account? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 transition" title="Delete account">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-[#6B7A90]">No users found in database.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $users->links() }}
</div>

<!-- Modals -->

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl w-full max-w-md p-8 border border-gray-200 relative shadow-2xl mx-4">
        <button onclick="closeAddModal()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h2 class="text-xl font-bold text-[#1B3A6B] mb-6">Create New User</h2>
        
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Full Name *</label>
                <input type="text" name="name" required placeholder="John Doe" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Email Address *</label>
                <input type="email" name="email" required placeholder="john@opmas.local" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Access Role *</label>
                <select name="role" class="w-full rounded-lg px-4 py-2.5 kijabe-input bg-white">
                    <option value="user">Normal User (View Only)</option>
                    <option value="admin">Admin (Moderate Control)</option>
                    <option value="system_admin">System Admin (Full Control)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Password *</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Confirm Password *</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            
            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-500 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-xs font-semibold text-white">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl w-full max-w-md p-8 border border-gray-200 relative shadow-2xl mx-4">
        <button onclick="closeEditModal()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h2 class="text-xl font-bold text-[#1B3A6B] mb-6">Modify User Account</h2>
        
        <form id="editForm" method="POST" class="space-y-4 text-sm">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Full Name *</label>
                <input type="text" id="edit_name" name="name" required class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Email Address *</label>
                <input type="email" id="edit_email" name="email" required class="w-full rounded-lg px-4 py-2.5 kijabe-input">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7A90] mb-1.5 uppercase">Access Role *</label>
                <select id="edit_role" name="role" class="w-full rounded-lg px-4 py-2.5 kijabe-input bg-white">
                    <option value="user">Normal User (View Only)</option>
                    <option value="admin">Admin (Moderate Control)</option>
                    <option value="system_admin">System Admin (Full Control)</option>
                </select>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <p class="text-[10px] text-[#6B7A90] font-bold uppercase mb-3">Change Password (Leave blank to keep current)</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">New Password</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full rounded-lg px-4 py-2.5 kijabe-input">
                    </div>
                </div>
            </div>
            
            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-500 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-xs font-semibold text-white">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeAddModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}

function openEditModal(user) {
    const editForm = document.getElementById('editForm');
    editForm.action = `/users/${user.id}`;
    
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    
    document.getElementById('editUserModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeEditModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}
</script>
@endsection
