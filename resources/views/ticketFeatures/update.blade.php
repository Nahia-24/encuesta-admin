@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    {{-- CSS de TomSelect --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Actualizar Ticket</h2>
    </div>

    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('ticketFeatures.update', ['id' => $ticket->id]) }}">
                    @csrf
                    @method('PUT')

                    <!-- Nombre del Ticket -->
                    <div class="mb-4">
                        <x-base.form-label class="mb-1 block" for="name">Nombre del Ticket</x-base.form-label>
                        <x-base.form-input class="w-full {{ $errors->has('name') ? 'border-red-500' : '' }}" id="name"
                            name="name" type="text" placeholder="Nombre del ticket"
                            value="{{ old('name', $ticket->name) }}" />
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Capacidades de los Tipos de Ticket asociados -->
                    @foreach ($ticket->ticketTypes as $type)
                        <div class="mb-4">
                            <x-base.form-label for="capacity_{{ $type->id }}">
                                Capacidad de {{ $type->name }}
                            </x-base.form-label>
                            <x-base.form-input id="capacity_{{ $type->id }}" name="capacities[{{ $type->id }}]"
                                type="number" value="{{ $type->capacity }}" readonly class="w-full" />
                        </div>
                    @endforeach

                    <!-- Características del Ticket -->
                    <div class="mb-4">
                        <x-base.form-label class="mb-1 block" for="characteristics">Características del
                            Ticket</x-base.form-label>
                        <select name="characteristics[]" id="characteristics" multiple class="w-full tom-select">
                            @foreach ($characteristics as $c)
                                <option value="{{ $c->id }}"
                                    {{ in_array($c->id, $ticket->characteristics->pluck('id')->toArray()) ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('characteristics')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipos de Ticket -->
                    <div class="mb-4">
                        <x-base.form-label class="mb-1 block" for="ticket_types">Tipos de Ticket</x-base.form-label>
                        <select name="ticket_types[]" id="ticket_types" multiple class="w-full tom-select">
                            @foreach ($ticketTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ in_array($type->id, $ticket->ticketTypes->pluck('id')->toArray()) ? 'selected' : '' }}>
                                    {{ $type->name }} - ${{ $type->price }}
                                </option>
                            @endforeach
                        </select>
                        @error('ticket_types')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="mt-5 text-right">
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
            new TomSelect("#characteristics", {
                plugins: ['remove_button'],
                persist: false,
                create: true,
                placeholder: "Selecciona o escribe nuevas características",
            });

            new TomSelect("#ticket_types", {
                plugins: ['remove_button'],
                persist: false,
                create: true,
                placeholder: "Selecciona o escribe nuevos tipos de ticket",
            });
        });
    </script>
@endsection
