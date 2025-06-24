@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    {{-- CSS de TomSelect --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Crear Ticket</h2>
    </div>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('ticketFeatures.store') }}">
                    @csrf

                    <!-- Nombre del Ticket -->
                    <div class="intro-y mt-4">
                        <x-base.form-label for="name">Ticket</x-base.form-label>
                        <x-base.form-input class="w-full {{ $errors->has('name') ? 'border-red-500' : '' }}" id="name"
                            name="name" type="text" placeholder="Nombre del ticket" value="{{ old('name') }}" />
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Capacidad del Ticket -->
                    <div class="intro-y mt-4">
                        <x-base.form-label for="capacity">Capacidad del ticket</x-base.form-label>
                        <x-base.form-input class="w-full {{ $errors->has('capacity') ? 'border-red-500' : '' }}"
                            id="capacity" name="capacity" type="number" min="1" placeholder="Capacidad del ticket"
                            value="{{ old('capacity') }}" />
                        @error('capacity')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Características del Ticket -->
                    <div class="intro-y mt-4">
                        <x-base.form-label class="mb-1 block" for="characteristics">Características del
                            ticket</x-base.form-label>
                        <select name="characteristics[]" id="characteristics" multiple class="w-full tom-select">
                            @foreach ($characteristics as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('characteristics')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipos de Ticket -->
                    <div class="intro-y mt-4">
                        <x-base.form-label class="mb-1 block" for="ticket_types">Tipos de Ticket</x-base.form-label>
                        <select name="ticket_types[]" id="ticket_types" multiple class="w-full tom-select">
                            @foreach ($ticketTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} - ${{ $type->price }}</option>
                            @endforeach
                        </select>
                        @error('ticket_types')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="mt-6 text-right">
                        <x-base.button class="mr-1 w-24" type="button" variant="outline-secondary"
                            onclick="window.location='{{ url()->previous() }}'">
                            Cancelar
                        </x-base.button>
                        <x-base.button class="w-24" type="submit" variant="primary">
                            Guardar
                        </x-base.button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('subscript')
    {{-- JS de TomSelect --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("✅ Subscript cargado correctamente.");

            new TomSelect("#characteristics", {
                plugins: ['remove_button'],
                persist: false,
                create: true, // Permitir ingresar nuevas características
                placeholder: "Selecciona o escribe nuevas características",
            });

            new TomSelect("#ticket_types", {
                plugins: ['remove_button'],
                persist: false,
                create: true, // Permitir ingresar nuevos tipos de ticket
                placeholder: "Selecciona o escribe nuevos tipos de ticket",
            });

        });
    </script>
@endsection
