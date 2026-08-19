<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterRole = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function resetPassword(int|string $userId): void
    {
        $user = User::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($userId)
            ->firstOrFail();
        abort_unless($this->canManageUser($user), 403, 'Akun ini hanya dapat dikelola oleh Super Admin.');
        $temporaryPassword = Str::password(16);

        $user->update([
            'password' => $temporaryPassword,
        ]);

        session()->flash('message', "Password sementara untuk {$user->name} berhasil dibuat. Salin sekarang karena tidak akan ditampilkan lagi.");
        session()->flash('temporary_password', $temporaryPassword);
    }

    public bool $isEditModalOpen = false;

    public int|string|null $editingUserId = null;

    public string $editingUserName = '';

    /** @var array<int, string> */
    public array $selectedRoles = [];

    public function toggleActiveStatus(int|string $userId): void
    {
        $user = User::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($userId)
            ->firstOrFail();

        // Prevent deactivating yourself
        if (auth()->id() === $user->id) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');

            return;
        }

        abort_unless($this->canManageUser($user), 403, 'Akun ini hanya dapat dikelola oleh Super Admin.');

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Akun {$user->name} berhasil $status.");
    }

    public function editRoles(int|string $userId): void
    {
        $user = User::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($userId)
            ->firstOrFail();
        abort_unless($this->canManageUser($user), 403, 'Akun ini hanya dapat dikelola oleh Super Admin.');

        $this->editingUserId = $user->id;
        $this->editingUserName = $user->name;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->isEditModalOpen = true;
    }

    public function updateRoles(): void
    {
        if (! $this->editingUserId) {
            return;
        }

        $user = User::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($this->editingUserId)
            ->firstOrFail();
        abort_unless($this->canManageUser($user), 403, 'Akun ini hanya dapat dikelola oleh Super Admin.');

        $validated = validator(
            ['roles' => $this->selectedRoles],
            [
                'roles' => ['required', 'array', 'min:1'],
                'roles.*' => ['string', Rule::in($this->manageableRoleNames())],
            ],
            ['roles.min' => 'Pengguna harus memiliki minimal satu role aktif.'],
        )->validate();

        $user->syncRoles($validated['roles']);

        session()->flash('message', "Peran (Role) untuk {$user->name} berhasil diperbarui.");

        $this->closeEditModal();
    }

    public function closeEditModal(): void
    {
        $this->isEditModalOpen = false;
        $this->editingUserId = null;
        $this->editingUserName = '';
        $this->selectedRoles = [];
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $users = User::with('roles')
            ->where('school_id', $schoolId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterRole, function ($query) {
                $query->role($this->filterRole);
            })
            ->orderBy('name', 'asc')
            ->paginate(7);

        $roles = Role::query()
            ->whereIn('name', $this->manageableRoleNames())
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'availableRoles' => $roles,
        ]);
    }

    /** @return list<string> */
    private function manageableRoleNames(): array
    {
        $protectedRoles = ['Super Admin', 'Admin Sekolah'];

        return array_values(Role::query()
            ->when(
                ! auth()->user()->hasRole('Super Admin'),
                fn ($query) => $query->whereNotIn('name', $protectedRoles),
            )
            ->orderBy('name')
            ->pluck('name')
            ->map(static fn (string $name): string => $name)
            ->all());
    }

    private function canManageUser(User $user): bool
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return true;
        }

        return ! $user->hasAnyRole(['Super Admin', 'Admin Sekolah']);
    }
}
