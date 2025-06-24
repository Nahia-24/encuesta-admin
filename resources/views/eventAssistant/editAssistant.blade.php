@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Editar Asistente del Evento</title>
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Editar Asistente para el Evento: <b>{{ $event->name }}</b></h2>
    </div>

    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('eventAssistant.update', $eventAssistant->id) }}">
                    @csrf
                    @method('PUT')
                    @php
                        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
                        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
                    @endphp

                    <div class="mt-3">
                        <x-base.form-label for="id_ticket">Ticket</x-base.form-label>
                        <x-base.tom-select class="w-full {{ $errors->has('id_ticket') ? 'border-red-500' : '' }}"
                            id="id_ticket" name="id_ticket">
                            <option></option>
                            @foreach ($ticketTypes as $ticket)
                                <option value="{{ $ticket->id }}"
                                    {{ $eventAssistant->ticketType?->id == $ticket?->id ? 'selected' : '' }}>
                                    {{ $ticket->name }}</option>
                            @endforeach
                        </x-base.tom-select>
                        @error('id_ticket')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @foreach ($selectedFields as $field)
                            @includeIf('partials.fields-edit-' . $field, [
                                'eventAssistant' => $eventAssistant,
                                'errors' => $errors,
                                'guardians' => $guardians,
                                'event' => $event,
                            ])
                        @endforeach
                    </div>

                    <!-- INICIO: Grilla de dos columnas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @if (in_array('name', $selectedFields))
                            <div>
                                <x-base.form-label for="name">Nombres</x-base.form-label>
                                <x-base.form-input id="name" class="w-full" type="text" name="name"
                                    placeholder="Nombre" value="{{ old('name', $eventAssistant->user->name) }}" />
                                @error('name')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        @if (in_array('lastname', $selectedFields))
                            <div>
                                <x-base.form-label for="lastname">Apellidos</x-base.form-label>
                                <x-base.form-input id="lastname" class="w-full" type="text" name="lastname"
                                    placeholder="Apellidos"
                                    value="{{ old('lastname', $eventAssistant->user->lastname) }}" />
                                @error('lastname')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('type_document', $selectedFields))
                            <!-- Type Document -->
                            <div class="mt-3">
                                <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                                <x-base.tom-select
                                    class="w-full {{ $errors->has('type_document') ? 'border-red-500' : '' }}"
                                    id="type_document" name="type_document">
                                    <option value=""></option>
                                    <option value="CC"
                                        {{ old('type_document', $eventAssistant->user->type_document) == 'CC' ? 'selected' : '' }}>
                                        Cédula de Ciudadanía</option>
                                    <option value="TI"
                                        {{ old('type_document', $eventAssistant->user->type_document) == 'TI' ? 'selected' : '' }}>
                                        Tarjeta de Identidad</option>
                                    <option value="CE"
                                        {{ old('type_document', $eventAssistant->user->type_document) == 'CE' ? 'selected' : '' }}>
                                        Cédula de Extranjería</option>
                                    <option value="PAS"
                                        {{ old('type_document', $eventAssistant->user->type_document) == 'PAS' ? 'selected' : '' }}>
                                        Pasaporte</option>
                                </x-base.tom-select>
                                @error('type_document')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('document_number', $selectedFields))
                            <!-- Document Number -->
                            <div class="mt-3">
                                <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                                <x-base.form-input id="document_number" name="document_number" type="text"
                                    value="{{ old('document_number', $eventAssistant->user->document_number) }}" />

                                @error('document_number')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        @if (in_array('birth_date', $selectedFields))
                            <!-- Fecha Cumpleaños -->
                            <div class="mt-3">
                                <x-base.form-label for="birth_date">Fecha Nacimiento</x-base.form-label>
                                <x-base.form-input id="birth_date" name="birth_date" type="date"
                                    value="{{ old('birth_date', $eventAssistant->user->birth_date) }}" />

                                @error('birth_date')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Botón Asignar Acudiente -->
                            <div class="mt-3" id="guardian-section" style="display:none;">
                                <button type="button" onclick="showGuardianSelect()"
                                    class="bg-blue-500 text-white px-4 py-2 rounded">
                                    Asignar Acudiente
                                </button>
                            </div>
                            <!-- Select Acudiente -->
                            <div class="mt-3" id="guardian-select-section" style="display:none;">
                                <x-base.form-label for="guardian_id">Acudientes disponibles</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('guardian_id') ? 'border-red-500' : '' }}"
                                    id="guardian_id" name="guardian_id" onchange="filterCities()">
                                    <option></option>
                                    @foreach ($guardians as $guardian)
                                        <option value="{{ $guardian->user->id }}"
                                            {{ old('guardian_id') == $guardian->user->id ? 'selected' : '' }}>
                                            {{ $guardian->user->name }} - {{ $guardian->user->document_number }}</option>
                                    @endforeach
                                </x-base.tom-select>
                                @error('guardian_id')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        @if (in_array('phone', $selectedFields))
                            <div class="mt-3">
                                <x-base.form-label for="phone">Teléfono</x-base.form-label>
                                <x-base.form-input id="phone" name="phone" type="text"
                                    value="{{ old('phone', $eventAssistant->user->phone) }}" />

                                @error('phone')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                    <!-- FIN: Grilla de dos columnas -->

                    <div class="mt-3">
                        <x-base.form-label for="email">Email</x-base.form-label>
                        <x-base.form-input id="email" class="w-full mt-4 px-4 py-3" type="email" name="email"
                            placeholder="Correo Electrónico" value="{{ old('email', $eventAssistant->user->email) }}"
                            required />
                        @error('email')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    @php
                        $selectedCityId = old('city_id', $eventAssistant->user->city_id ?? null);
                        $selectedDepartmentId = old('department_id', $eventAssistant->user->department_id ?? null);
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- Departamento -->
                        <div class="mt-3">
                            <x-base.form-label for="department_id">Departamento</x-base.form-label>
                            <x-base.tom-select class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}"
                                id="department_id" name="department_id">
                                <option></option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->code_dane }} - {{ $department->name }}
                                    </option>
                                @endforeach
                            </x-base.tom-select>
                            @error('department_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div class="mt-3">
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

                    @foreach ($additionalParameters as $parameter)
                        @include('partials.additional-field-edit', [
                            'parameter' => $parameter,
                            'errors' => $errors,
                            'userEventParameter' => $userEventParameter,
                        ])
                    @endforeach

                    <div class="mt-5 text-right flex justify-end gap-3">
                        <x-base.button class="w-40 justify-center" type="button" variant="outline-secondary"
                            onclick="window.location='{{ url()->previous() }}'">Cancelar</x-base.button>
                        <x-base.button class="w-40 justify-center" type="submit" variant="primary">Guardar
                            Cambios</x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function checkAge() {
            const birthDate = document.getElementById('birth_date').value;
            if (birthDate) {
                const today = new Date();
                const birth = new Date(birthDate);
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                    age--;
                }

                if (age < 18) {
                    document.getElementById('guardian-section').style.display = 'block';
                } else {
                    document.getElementById('guardian-section').style.display = 'none';
                    document.getElementById('guardian-select-section').style.display = 'none';
                }
            }
        }

        function showGuardianSelect() {
            alert(
                "Recuerda que para asignar un acudiente, solo van a poder ser asignados los que ya están creados en el evento y tengan un tipo de documento."
            );
            document.getElementById('guardian-select-section').style.display = 'block';

            fetch('/event-assistants?event_id={{ $event->id }}')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('guardian_id');
                    select.innerHTML = '';
                    data.forEach(assistant => {
                        if (assistant.document_number !== null) {
                            const option = document.createElement('option');
                            option.value = assistant.id;
                            option.text = assistant.name + ' ' + assistant.lastname;
                            select.appendChild(option);
                        }
                    });
                });
        }
        @php
            $cityIdToSelect = old('city_id', $eventAssistant->user->city_id ?? null);
        @endphp

            <
            script >
            @if (in_array('city_id', $selectedFields))
                function updateCityOptions(cities) {
                    var citySelect = document.querySelector('#city_id').tomselect;

                    if (!Array.isArray(cities)) {
                        console.error('Expected an array of cities but got:', cities);
                        return;
                    }

                    citySelect.clearOptions();

                    cities.forEach(city => {
                        citySelect.addOption({
                            value: city.id,
                            text: city.name
                        });
                    });

                    citySelect.refreshOptions(false);

                    @if ($cityIdToSelect)
                        citySelect.setValue({{ $cityIdToSelect }});
                    @endif
                }

                function filterCities() {
                    var departmentId = document.getElementById('department_id').value;
                    var citySelect = document.getElementById('city_id');

                    citySelect.innerHTML = '<option></option>';

                    if (departmentId) {
                        fetch('/cities/' + departmentId)
                            .then(response => response.json())
                            .then(data => {
                                if (Array.isArray(data.cities)) {
                                    updateCityOptions(data.cities);
                                } else {
                                    console.error('Invalid data format:', data);
                                }
                            })
                            .catch(error => console.error('Error fetching cities:', error));
                    }
                }

                document.addEventListener("DOMContentLoaded", function() {
                    filterCities();
                });
            @endif
        }
    </script>
@endsection
