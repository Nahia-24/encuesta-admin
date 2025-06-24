@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Asistentes del Evento</title>
@endsection

@section('subcontent')
    <div class="flex justify-between items-center mt-10">
        <h2 class="mr-auto text-lg font-medium">Crear Asistente para el Evento: <b>{{ $event->name }}</b></h2>
    </div>

    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('eventAssistant.singleCreate.upload', $event->id) }}">
                    @csrf
                    @php
                        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
                    @endphp

                    <div class="mt-3">
                        <x-base.form-label for="id_ticket">Ticket</x-base.form-label>
                        <x-base.tom-select class="w-full {{ $errors->has('id_ticket') ? 'border-red-500' : '' }}"
                            id="id_ticket" name="id_ticket">
                            <option></option>
                            @foreach ($ticketTypes as $ticket)
                                <option value="{{ $ticket->id }}" {{ old('id_ticket') == $ticket->id ? 'selected' : '' }}>
                                    {{ $ticket->name }}</option>
                            @endforeach
                        </x-base.tom-select>
                        @error('id_ticket')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @if (in_array('name', $selectedFields))
                            <div>
                                <x-base.form-label for="name">Nombre</x-base.form-label>
                                <x-base.form-input id="name" class="w-full" type="text" name="name" placeholder="Nombre" value="{{ old('name') }}" />
                                @error('name')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('lastname', $selectedFields))
                            <div>
                                <x-base.form-label for="lastname">Apellido</x-base.form-label>
                                <x-base.form-input id="lastname" class="w-full" type="text" name="lastname" placeholder="Apellidos" value="{{ old('lastname') }}" />
                                @error('lastname')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('type_document', $selectedFields))
                            <div class="mt-3">
                                <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('type_document') ? 'border-red-500' : '' }}" id="type_document" name="type_document">
                                    <option value=""></option>
                                    <option value="CC" {{ old('type_document') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                    <option value="TI" {{ old('type_document') == 'TI' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                                    <option value="CE" {{ old('type_document') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                    <option value="PAS" {{ old('type_document') == 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                                </x-base.tom-select>
                                @error('type_document')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('document_number', $selectedFields))
                            <div class="mt-3">
                                <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                                <x-base.form-input class="w-full {{ $errors->has('document_number') ? 'border-red-500' : '' }}" id="document_number" name="document_number" type="text" placeholder="Número de Documento" value="{{ old('document_number') }}" />
                                @error('document_number')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if (in_array('birth_date', $selectedFields))
                            <div class="mt-3">
                                <x-base.form-label for="birth_date">Fecha Nacimiento</x-base.form-label>
                                <x-base.form-input class="w-full {{ $errors->has('birth_date') ? 'border-red-500' : '' }}" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" onchange="checkAge()" />
                                @error('birth_date')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-3" id="guardian-section" style="display:none;">
                                <button type="button" onclick="showGuardianSelect()" class="bg-blue-500 text-white px-4 py-2 rounded">
                                    Asignar Acudiente
                                </button>
                            </div>

                            <div class="mt-3" id="guardian-select-section" style="display:none;">
                                <x-base.form-label for="guardian_id">Acudientes disponibles</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('guardian_id') ? 'border-red-500' : '' }}" id="guardian_id" name="guardian_id">
                                    <option></option>
                                    @foreach ($guardians as $guardian)
                                        <option value="{{ $guardian->user->id }}" {{ old('guardian_id') == $guardian->user->id ? 'selected' : '' }}>
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
                                <x-base.form-input class="w-full {{ $errors->has('phone') ? 'border-red-500' : '' }}" id="phone" name="phone" type="text" placeholder="Teléfono" value="{{ old('phone') }}" />
                                @error('phone')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if (in_array('email', $selectedFields))
                            <x-base.form-label for="email">Email</x-base.form-label>
                            <x-base.form-input id="email" class="intro-x mt-3 block min-w-full px-4 py-3 xl:min-w-[350px]" type="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required />
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @if (in_array('city_id', $selectedFields))
                            <div class="mt-3">
                                <x-base.form-label for="department_id">Departamento</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}" id="department_id" name="department_id">
                                    <option></option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->code_dane }} - {{ $department->name }}</option>
                                    @endforeach
                                </x-base.tom-select>
                                @error('department_id')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-3">
                                <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('city_id') ? 'border-red-500' : '' }}" id="city_id" name="city_id">
                                    <option></option>
                                </x-base.tom-select>
                                @error('city_id')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @foreach ($additionalParameters as $parameter)
                            @php
                                $type = $parameter['type'] ?? 'text';
                                $name = $parameter['name'] ?? '';
                                $label = $parameter['label'] ?? '';
                                $options = $parameter['options'] ?? [];
                            @endphp

                            <div class="mt-3">
                                @if ($type == 'select')
                                    <x-base.form-label for="{{ $name }}">{{ $label }}</x-base.form-label>
                                    <x-base.tom-select class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}" id="{{ $name }}" name="{{ $name }}">
                                        <option value=""></option>
                                        @foreach ($options as $key => $value)
                                            <option value="{{ $key }}" {{ old($name) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </x-base.tom-select>
                                @else
                                    <x-base.form-label for="{{ $name }}">{{ $label }}</x-base.form-label>
                                    <x-base.form-input class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" placeholder="{{ $label }}" value="{{ old($name) }}" />
                                @endif
                                @error($name)
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 text-right flex justify-end gap-3">
                        <x-base.button class="w-40 justify-center" type="button" variant="outline-secondary" onclick="window.location='{{ url()->previous() }}'">Cancelar</x-base.button>
                        <x-base.button class="w-32 px-4 py-3" type="submit" variant="primary">Registrar</x-base.button>
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
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--;
                if (age < 18) {
                    document.getElementById('guardian-section').style.display = 'block';
                } else {
                    document.getElementById('guardian-section').style.display = 'none';
                    document.getElementById('guardian-select-section').style.display = 'none';
                }
            }
        }

        function showGuardianSelect() {
            alert("Recuerda que para asignar un acudiente, solo van a poder ser asignados los que ya están creados en el evento y tengan un número de documento.");
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

        @if (in_array('city_id', $selectedFields))
        function updateCityOptions(cities) {
            const citySelectElement = document.querySelector('#city_id');
            if (!citySelectElement || !citySelectElement.tomselect) return;
            const citySelect = citySelectElement.tomselect;
            citySelect.clearOptions();
            cities.forEach(city => {
                citySelect.addOption({ value: city.id, text: city.name });
            });
            @if (old('city_id'))
                citySelect.setValue('{{ old('city_id') }}');
            @endif
            citySelect.refreshOptions(false);
        }

        function filterCities() {
            const departmentId = document.getElementById('department_id').value;
            if (!departmentId) return;
            fetch('/city/' + departmentId)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data.cities)) updateCityOptions(data.cities);
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            filterCities();
            document.getElementById('department_id')?.addEventListener('change', filterCities);
        });
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const documentNumberInput = document.getElementById('document_number');
            const emailInput = document.getElementById('email');
            const fetchData = async (url, data) => {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data),
                    });
                    const result = await response.json();
                    return result.data;
                } catch (error) {
                    console.error('Error:', error);
                }
            };
            const populateFields = (userData) => {
                if (userData) {
                    for (const key in userData) {
                        const inputField = document.getElementById(key);
                        if (inputField) inputField.value = userData[key] || '';
                    }
                }
            };
            if (documentNumberInput) {
                documentNumberInput.addEventListener('blur', async function() {
                    const documentNumber = documentNumberInput.value.trim();
                    if (documentNumber) {
                        const userData = await fetchData('{{ route('checkRecord') }}', { document_number: documentNumber });
                        populateFields(userData);
                    }
                });
            }
            if (emailInput) {
                emailInput.addEventListener('blur', async function() {
                    const email = emailInput.value.trim();
                    if (email) {
                        const userData = await fetchData('{{ route('checkRecord') }}', { email: email });
                        populateFields(userData);
                    }
                });
            }
        });
    </script>
@endsection
