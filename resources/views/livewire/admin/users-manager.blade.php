<div>
    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <flux:heading size="xl" level="1">Usuarios de Administración</flux:heading>
            <flux:subheading>Gestiona los administradores con acceso.</flux:subheading>
        </div>
        <div class="mt-4 md:mt-0">
            <flux:modal.trigger name="user-modal">
                <flux:button variant="primary" wire:click="new" icon="plus">Nuevo Usuario</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    @if ($errors->has('deleteError'))
        <div class="mb-4">
            <flux:callout variant="danger" icon="exclamation-circle">
                {{ $errors->first('deleteError') }}
            </flux:callout>
        </div>
    @endif

    <div class="mt-8 overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
        <table class="w-full text-sm text-left align-middle text-zinc-600 dark:text-zinc-400">
            <thead class="bg-zinc-50 border-b border-zinc-200 dark:bg-zinc-800/50 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium text-left">Nombre</th>
                    <th scope="col" class="px-4 py-3 font-medium text-left">Correo</th>
                    <th scope="col" class="px-4 py-3 font-medium text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $user->name }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:modal.trigger name="user-modal">
                                <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $user->id }})">Editar</flux:button>
                            </flux:modal.trigger>
                            @if ($user->email !== 'lrmaldo@gmail.com' && $user->id !== auth()->id())
                                <flux:button variant="ghost" size="sm" class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/20" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="¿Seguro que deseas eliminar este usuario?">Eliminar</flux:button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <flux:modal name="user-modal" class="max-w-md w-full">
        <form wire:submit="save">
            <flux:heading size="lg">{{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}</flux:heading>
            
            <div class="mt-6 flex flex-col gap-4">
                <flux:input wire:model.blur="name" label="Nombre Completo" placeholder="Ej: Juan Pérez" required />
                <flux:input type="email" wire:model.blur="email" label="Correo Electrónico" placeholder="usuario@ejemplo.com" :disabled="$editingId && $email === 'lrmaldo@gmail.com'" required />
                <flux:input type="password" wire:model.blur="password" label="Contraseña" viewable="{{ true }}" :required="!$editingId" placeholder="{{ $editingId ? 'Dejar en blanco para mantener' : 'Al menos 8 caracteres' }}" />
            </div>

            <div class="mt-8 flex justify-end gap-x-3">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="resetForm">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
