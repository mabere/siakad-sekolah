<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public ?TemporaryUploadedFile $photo = null;

    public ?string $currentPhotoUrl = null;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->currentPhotoUrl = $user->avatar_url;
    }

    public function saveProfile(): void
    {
        /** @var User $user */
        $user = User::find(auth()->id());

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $avatarPath = $user->getRawOriginal('avatar_url');

        if ($this->photo) {
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            $avatarPath = $this->photo->store('avatars', 'public');
        }

        $user->update([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'avatar_url' => $avatarPath,
        ]);

        // Sync name and email with linked Teacher or Student
        if ($user->teacher) {
            $user->teacher->update([
                'name' => trim($this->name),
                'email' => trim($this->email),
            ]);
        }

        if ($user->student) {
            $user->student->update([
                'name' => trim($this->name),
                'email' => trim($this->email),
            ]);
        }

        $this->currentPhotoUrl = $user->avatar_url;
        $this->photo = null;

        session()->flash('profile_success', 'Profil Anda berhasil diperbarui.');
    }

    public function deletePhoto(): void
    {
        /** @var User $user */
        $user = User::find(auth()->id());

        $avatarPath = $user->getRawOriginal('avatar_url');

        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
            Storage::disk('public')->delete($avatarPath);
        }

        $user->update([
            'avatar_url' => null,
        ]);

        $this->currentPhotoUrl = $user->avatar_url;
        $this->photo = null;

        session()->flash('profile_success', 'Foto profil berhasil dihapus.');
    }

    public function updatePassword(): void
    {
        /** @var User $user */
        $user = User::find(auth()->id());

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Kata sandi saat ini tidak cocok.');

            return;
        }

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password_success', 'Kata sandi Anda berhasil diperbarui.');
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $layout = 'components.layouts.app';
        if ($user->hasRole('Guru')) {
            $layout = 'components.layouts.teacher';
        } elseif ($user->hasRole('Siswa')) {
            $layout = 'components.layouts.student';
        }

        return view('livewire.profile.edit', [
            'user' => $user,
        ])->layout($layout);
    }
}
