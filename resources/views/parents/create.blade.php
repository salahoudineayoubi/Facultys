<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajouter un parent d\'étudiant(e)') }}
        </h2>
    </x-slot>

    <div class="py-2 px-12">
        @livewire('create-parent')
    </div>
</x-app-layout>
