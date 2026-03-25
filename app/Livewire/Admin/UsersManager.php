<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UsersManager extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'password' => $this->editingId ? 'nullable|string|min:8' : 'required|string|min:8',
        ];
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->resetErrorBag();
    }

    public function new(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            if ($user->email === 'lrmaldo@gmail.com') {
                $data['email'] = 'lrmaldo@gmail.com'; // No permitir cambiar este correo.
            }

            $user->name = $data['name'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();
        } else {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Password is cast to hashed in model
            ]);
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->email === 'lrmaldo@gmail.com') {
            $this->addError('deleteError', 'El usuario lrmaldo@gmail.com no puede ser eliminado.');

            return;
        }

        if ($user->id === auth()->id()) {
            $this->addError('deleteError', 'No puedes eliminar tu propia cuenta.');

            return;
        }

        $user->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.users-manager', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
