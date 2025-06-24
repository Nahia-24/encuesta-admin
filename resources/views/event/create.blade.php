@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Eventos - Crear</title>
    <link rel="stylesheet" href="{{ url('css/blade.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Crear Evento</h2>
    </div>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('event.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
                        <!-- Nombre del Evento -->
                        <div>
                            <x-base.form-label for="name">Nombre del Evento</x-base.form-label>
                            <x-base.form-input class="w-full {{ $errors->has('name') ? 'border-red-500' : '' }}"
                                id="name" name="name" type="text" placeholder="Nombre del Evento"
                                value="{{ old('name') }}" />
                            @error('name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Capacidad total -->
                        <div>
                            <x-base.form-label for="capacity">Capacidad Total</x-base.form-label>
                            <x-base.form-input class="w-full {{ $errors->has('capacity') ? 'border-red-500' : '' }}"
                                id="capacity" name="capacity" type="number" placeholder="Capacidad total del evento"
                                value="{{ old('capacity') }}" />
                            @error('capacity')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3 box">
                        <x-base.form-label class="m-2">Fechas Evento</x-base.form-label>
                        <div class="grid-cols-2 gap-2 sm:grid">
                            <!-- Fecha del Evento -->
                            <div class="m-2">
                                <x-base.form-label for="event_date">Fecha Inicial del Evento</x-base.form-label>
                                <x-base.form-input class="w-full {{ $errors->has('event_date') ? 'border-red-500' : '' }}"
                                    id="event_date" name="event_date" type="date" value="{{ old('event_date') }}" />
                                @error('event_date')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha Final del Evento -->
                            <div class="m-2">
                                <x-base.form-label for="event_date_end">Fecha Final del Evento</x-base.form-label>
                                <x-base.form-input
                                    class="w-full {{ $errors->has('event_date_end') ? 'border-red-500' : '' }}"
                                    id="event_date_end" name="event_date_end" type="date"
                                    value="{{ old('event_date_end') }}" />
                                @error('event_date_end')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row row_horaIni-Fin">
                        <!-- Hora de Inicio -->
                        <div class="col mt-3 col_horaIni">
                            <x-base.form-label for="start_time">Hora de Inicio</x-base.form-label>
                            <x-base.form-input class="w-full {{ $errors->has('start_time') ? 'border-red-500' : '' }}"
                                id="start_time" name="start_time" type="time" value="{{ old('start_time') }}" />
                            @error('start_time')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hora de Fin -->
                        <div class="col mt-3 col_horaFin">
                            <x-base.form-label for="end_time">Hora de Fin</x-base.form-label>
                            <x-base.form-input class="w-full {{ $errors->has('end_time') ? 'border-red-500' : '' }}"
                                id="end_time" name="end_time" type="time" value="{{ old('end_time') }}" />
                            @error('end_time')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row row_dptocity">
                        <!-- Departamento -->
                        <div class="col mt-3 col_depto">
                            <x-base.form-label for="department_id">Departamento</x-base.form-label>
                            <x-base.tom-select class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}"
                                id="department_id" name="department_id" onchange="filterCities()">
                                <option></option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->code_dane }} - {{ $department->name }}</option>
                                @endforeach
                            </x-base.tom-select>
                            @error('department_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div class="col mt-3 col_city">
                            <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                            <x-base.tom-select class="w-full {{ $errors->has('city_id') ? 'border-red-500' : '' }}"
                                id="city_id" name="city_id">
                                <option></option>
                            </x-base.tom-select>
                            @error('city_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipos de entradas -->
                    <div class="mt-3">
                        <x-base.form-label>Tipos de Entradas</x-base.form-label>

                        <div id="ticket-types-container"></div>

                        <x-base.button class="mt-3" type="button" variant="outline-secondary" onclick="addTicketType()">
                            Añadir Tipo de Entrada
                        </x-base.button>

                        {{-- Mostrar errores por ticket --}}
                        @php
                            $ticketErrors = $errors->getBag('default')->getMessages();
                        @endphp

                        @foreach ($ticketErrors as $key => $messages)
                            @if (Str::startsWith($key, 'ticketTypes.') && Str::contains($key, 'characteristics'))
                                @foreach ($messages as $message)
                                    <div class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    <!-- Descripción del Evento -->
                    <div class="mt-3">
                        <x-base.form-label for="description">Descripción del Evento</x-base.form-label>
                        <textarea class="w-full form-control {{ $errors->has('description') ? 'border-red-500' : '' }}" id="description"
                            name="description" placeholder="Descripción del Evento" rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dirección del Evento -->
                    <div class="mt-3">
                        <x-base.form-label for="address">Dirección del Evento</x-base.form-label>
                        <x-base.form-input class="w-full {{ $errors->has('address') ? 'border-red-500' : '' }}"
                            id="address" name="address" type="text" placeholder="Direccion del evento"
                            value="{{ old('address') }}" />
                        @error('address')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado por defecto-->
                    <div class="mt-3">
                        <x-base.form-label for="department_id">Estado por defecto</x-base.form-label>
                        <x-base.tom-select class="w-full {{ $errors->has('status') ? 'border-red-500' : '' }}"
                            id="status" name="status">
                            <option></option>
                            @foreach (config('statusEvento') as $label => $valor)
                                <option value="{{ $valor }}" {{ old('status') == $valor ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </x-base.tom-select>
                        @error('status')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Imagen del Encabezado -->
                    <div class="mt-3">
                        <x-base.form-label for="header_image_path">Imagen del Encabezado</x-base.form-label>
                        <input class="w-full form-control {{ $errors->has('header_image_path') ? 'border-red-500' : '' }}"
                            id="header_image_path" name="header_image_path" type="file" accept="image/*" />
                        @error('header_image_path')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3 box">
                        <x-base.form-label class="m-2">Colores Representativos</x-base.form-label>
                        <div class="grid-cols-2 gap-2 sm:grid">
                            <!-- color_one -->
                            <div class="m-2">
                                <x-base.form-label for="color_one">Color Primario</x-base.form-label>
                                <x-base.form-input class="w-full {{ $errors->has('color_one') ? 'border-red-500' : '' }}"
                                    id="color_one" name="color_one" type="color" placeholder="Direccion del evento"
                                    value="{{ old('color_one', $event->color_one ?? '#FFFFFF') }}" />
                                @error('color_one')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- two -->
                            <div class="m-2">
                                <x-base.form-label for="color_two">Color Secundario</x-base.form-label>
                                <x-base.form-input class="w-full {{ $errors->has('color_two') ? 'border-red-500' : '' }}"
                                    id="color_two" name="color_two" type="color" placeholder="Direccion del evento"
                                    value="{{ old('color_one', $event->color_two ?? '#FFFFFF') }}" />
                                @error('color_two')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Campos Adicionales -->
                    <div class="mt-3">
                        <x-base.form-label>Campos Adicionales</x-base.form-label>
                        <div id="dynamic-fields-container"></div>
                        <x-base.button class="mt-3" type="button" variant="outline-secondary"
                            onclick="addDynamicField()">
                            Añadir Campo
                        </x-base.button>

                        @error('additionalFields')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror

                        @foreach ($errors->get('additionalFields.*') as $fieldIndex => $errorMessages)
                            @foreach ($errorMessages as $errorMessage)
                                <div class="text-red-500 text-sm mt-1">
                                    {{ 'Campo adicional ' . ($loop->parent->index + 1) . ': ' . $errorMessage }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>

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

    <script>
        let fieldIndex = 0;
        let ticketTypeIndex = 0;
        const characteristicOptions = @json($characteristics);

        function addDynamicField() {
            const container = document.getElementById('dynamic-fields-container');
            const fieldId = `additional_field_${fieldIndex}`;
            const fieldHtml = `
                <div class="flex items-center mt-2" id="${fieldId}_wrapper">
                    <input
                        type="text"
                        name="additionalFields[${fieldIndex}][label]"
                        placeholder="Etiqueta"
                        class="form-control w-1/3 mr-2"
                    />
                    <input
                        type="text"
                        name="additionalFields[${fieldIndex}][value]"
                        placeholder="Valor"
                        class="form-control w-1/3 mr-2"
                    />
                    <x-base.button
                        type="button"
                        variant="outline-danger"
                        onclick="removeDynamicField('${fieldId}')"
                    >
                        Eliminar
                    </x-base.button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', fieldHtml);
            fieldIndex++;
        }

        function removeDynamicField(fieldId) {
            document.getElementById(`${fieldId}_wrapper`).remove();
        }


        function addTicketType() {
            const container = document.getElementById('ticket-types-container');
            const ticketTypeId = `ticket_type_${ticketTypeIndex}`;

            // Construir opciones de características
            const characteristicOptionsHtml = characteristicOptions.map(characteristic => {
                return `<option value="${characteristic.id}">${characteristic.name}</option>`;
            }).join('');

            const fieldHtml = `
    <div id="${ticketTypeId}_wrapper" class="grid grid-cols-12 gap-4 items-end mt-3">
        <div class="col-span-12 md:col-span-3">
            <label for="${ticketTypeId}_name" class="form-label">Nombre del Tipo</label>
            <input
                id="${ticketTypeId}_name"
                name="ticketTypes[${ticketTypeIndex}][name]"
                type="text"
                placeholder="Ej: Entrada VIP"
                class="form-control w-full"
                required
            />
        </div>
        <div class="col-span-6 md:col-span-2">
            <label for="${ticketTypeId}_capacity" class="form-label">Capacidad</label>
            <input
                id="${ticketTypeId}_capacity"
                name="ticketTypes[${ticketTypeIndex}][capacity]"
                type="number"
                placeholder="100"
                class="form-control w-full"
                min="1"
                required
            />
        </div>
        <div class="col-span-6 md:col-span-2">
            <label for="${ticketTypeId}_price" class="form-label">Precio</label>
            <input
                id="${ticketTypeId}_price"
                name="ticketTypes[${ticketTypeIndex}][price]"
                type="number"
                step="0.01"
                placeholder="25000"
                class="form-control w-full"
                min="0"
                required
            />
        </div>
        <div class="col-span-11 md:col-span-4">
            <label for="${ticketTypeId}_characteristics" class="form-label">Características</label>
            <select
                id="${ticketTypeId}_characteristics"
                name="ticketTypes[${ticketTypeIndex}][characteristics][]"
                multiple
                class="tom-select w-full"
            >
                ${characteristicOptionsHtml}
            </select>
        </div>
        <button
            type="button"
            onclick="removeTicketType('${ticketTypeId}')"
            class="text-red-500 hover:text-red-700 mt-[-4px]"
            title="Eliminar"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 3h6a1 1 0 011 1v1H8V4a1 1 0 011-1z" />
            </svg>
        </button> 
    </div>`;

            container.insertAdjacentHTML('beforeend', fieldHtml);

            // Inicializa Tom Select
            new TomSelect(`#${ticketTypeId}_characteristics`, {
                plugins: ['remove_button'],
                maxItems: null,
            });

            ticketTypeIndex++;
        }

        function removeTicketType(id) {
            const element = document.getElementById(`${id}_wrapper`);
            if (element) element.remove();
        }

        function updateCityOptions(cities) {
            var citySelect = document.querySelector('#city_id').tomselect;

            // Verifica si 'cities' es un array
            if (!Array.isArray(cities)) {
                console.error('Expected an array of cities but got:', cities);
                return;
            }
            // Limpia las opciones actuales del select de ciudades
            citySelect.clearOptions();
            citySelect.addOption({
                value: '',
                text: 'Seleccione una ciudad'
            });

            // Agrega nuevas opciones dinámicamente
            cities.forEach(city => {
                citySelect.addOption({
                    value: city.id,
                    text: city.name
                });
            });

            @if (old('city_id'))
                console.log("se va a asiganr " + {{ old('city_id') }});
                citySelect.setValue('{{ old('city_id') }}');
            @endif

            // Refresca la lista de opciones para que se muestren correctamente en la interfaz
            citySelect.refreshOptions(false);
        }

        function filterCities() {
            var departmentId = document.getElementById('department_id').value;
            var citySelect = document.getElementById('city_id');

            // Limpia el select de ciudades
            citySelect.innerHTML = '<option></option>';

            if (departmentId) {
                fetch('/cities/' + departmentId)
                    .then(response => response.json())
                    .then(data => {
                        // Verifica si 'data.cities' existe y es un array
                        if (Array.isArray(data.cities)) {
                            updateCityOptions(data.cities);
                        } else {
                            console.error('Invalid data format:', data);
                        }
                    })
                    .catch(error => console.error('Error fetching cities:', error));
            }
        }
        filterCities();
    </script>
@endsection
