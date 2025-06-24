@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Buscar Asistente</title>
@endsection

@section('subcontent')
    <div class="flex justify-between items-center mt-10">
        <h2 class="mr-auto text-lg font-medium">Buscar Asistente para el Evento: <b>{{ $event->name }}</b></h2>
    </div>

    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="GET" action="{{ route('eventAssistant.specificSearch.upload', $event->id) }}">
                    @csrf
                    @php
                        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @if (in_array('name', $selectedFields))
                            <div>
                                <x-base.form-label for="name">Nombres</x-base.form-label>
                                <x-base.form-input id="name" name="name" class="w-full" type="text"
                                    value="{{ old('name') }}" />
                            </div>
                        @endif

                        @if (in_array('lastname', $selectedFields))
                            <div>
                                <x-base.form-label for="lastname">Apellidos</x-base.form-label>
                                <x-base.form-input id="lastname" name="lastname" class="w-full" type="text"
                                    value="{{ old('lastname') }}" />
                            </div>
                        @endif

                        @if (in_array('type_document', $selectedFields))
                            <div>
                                <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                                <x-base.tom-select id="type_document" name="type_document" class="w-full">
                                    <option value=""></option>
                                    <option value="CC" {{ old('type_document') == 'CC' ? 'selected' : '' }}>Cédula de
                                        Ciudadanía</option>
                                    <option value="TI" {{ old('type_document') == 'TI' ? 'selected' : '' }}>Tarjeta de
                                        Identidad</option>
                                    <option value="CE" {{ old('type_document') == 'CE' ? 'selected' : '' }}>Cédula de
                                        Extranjería</option>
                                    <option value="PAS" {{ old('type_document') == 'PAS' ? 'selected' : '' }}>Pasaporte
                                    </option>
                                </x-base.tom-select>
                            </div>
                        @endif

                        @if (in_array('document_number', $selectedFields))
                            <div>
                                <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                                <x-base.form-input id="document_number" name="document_number" class="w-full" type="text"
                                    value="{{ old('document_number') }}" />
                            </div>
                        @endif

                        @if (in_array('birth_date', $selectedFields))
                            <div>
                                <x-base.form-label for="birth_date">Fecha de Nacimiento</x-base.form-label>
                                <x-base.form-input id="birth_date" name="birth_date" class="w-full" type="date"
                                    value="{{ old('birth_date') }}" />
                            </div>
                        @endif

                        @if (in_array('phone', $selectedFields))
                            <div>
                                <x-base.form-label for="phone">Teléfono</x-base.form-label>
                                <x-base.form-input id="phone" name="phone" class="w-full" type="text"
                                    value="{{ old('phone') }}" />
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if (in_array('email', $selectedFields))
                            <div>
                                <x-base.form-label for="email">Email</x-base.form-label>
                                <x-base.form-input id="email" name="email" class="w-full" type="email"
                                    value="{{ old('email') }}" />
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">

                        @if (in_array('city_id', $selectedFields))
                            <div>
                                <x-base.form-label for="department_id">Departamento</x-base.form-label>
                                <x-base.tom-select id="department_id" name="department_id" class="w-full"
                                    onchange="filterCities()">
                                    <option value=""></option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </x-base.tom-select>
                            </div>

                            <div>
                                <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                                <x-base.tom-select id="city_id" name="city_id" class="w-full">
                                    <option value=""></option>
                                </x-base.tom-select>
                            </div>
                        @endif
                    </div>

                    <!-- Campos Adicionales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        @foreach ($additionalParameters as $parameter)
                            @php
                                $type = $parameter['type'] ?? 'text';
                                $name = $parameter['name'] ?? '';
                                $label = $parameter['label'] ?? '';
                                $options = $parameter['options'] ?? [];
                            @endphp

                            <div>
                                <x-base.form-label for="{{ $name }}">{{ $label }}</x-base.form-label>
                                @if ($type == 'select')
                                    <x-base.tom-select id="{{ $name }}" name="{{ $name }}" class="w-full">
                                        <option value=""></option>
                                        @foreach ($options as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ old($name) == $key ? 'selected' : '' }}>{{ $value }}
                                            </option>
                                        @endforeach
                                    </x-base.tom-select>
                                @else
                                    <x-base.form-input id="{{ $name }}" name="{{ $name }}" class="w-full"
                                        type="{{ $type }}" value="{{ old($name) }}" />
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Botón -->
                    <div class="mt-5 text-right flex justify-end gap-3">
                        <x-base.button class="w-40 justify-center" type="button" variant="outline-secondary"
                            onclick="window.location='{{ url()->previous() }}'">Cancelar</x-base.button>
                        <x-base.button class="w-32 px-4 py-3" type="submit" variant="primary">Buscar</x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
