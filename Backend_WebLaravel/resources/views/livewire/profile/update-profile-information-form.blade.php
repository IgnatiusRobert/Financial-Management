<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $avatar;
    public $avatarUrl;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
        $this->avatarUrl = Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
            $this->avatarUrl = Storage::url($path);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('notify', type: 'success', message: 'Profil berhasil diperbarui!');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
        $this->dispatch('notify', type: 'success', message: 'Tautan verifikasi telah dikirim.');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Perbarui informasi profil akun dan alamat email Anda.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <!-- Avatar Upload -->
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif ($avatarUrl)
                    <img src="{{ $avatarUrl }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <x-heroicon-o-user class="w-10 h-10" />
                    </div>
                @endif
            </div>
            <div>
                <x-input-label for="avatar" value="Foto Profil (Avatar)" />
                <input wire:model="avatar" type="file" id="avatar" class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/10 dark:file:text-brand-400 dark:hover:file:bg-brand-500/20 cursor-pointer" accept="image/*">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="phone" value="Nomor Telepon" />
            <x-text-input wire:model="phone" id="phone" name="phone" type="tel" class="mt-1 block w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300" autocomplete="tel" placeholder="0812xxxxxxxx" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        Email Anda belum diverifikasi.

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-500 focus:bg-brand-500 active:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Simpan
            </button>

            <x-action-message class="me-3 text-brand-600 dark:text-brand-400" on="profile-updated">
                Tersimpan.
            </x-action-message>
        </div>
    </form>
</section>
