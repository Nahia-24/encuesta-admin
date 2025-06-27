@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Crear Encuesta</title>
    <link rel="stylesheet" href="{{ url('css/blade.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Crear Encuesta</h2>
    </div>

    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('surveys.create') }}">
                    @csrf

                    <!-- Título -->
                    <div class="mt-3">
                        <x-base.form-label for="title">Título de la Encuesta</x-base.form-label>
                        <x-base.form-input id="title" name="title" type="text" value="{{ old('title') }}"
                            class="w-full {{ $errors->has('title') ? 'border-red-500' : '' }}" />
                        @error('title')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="mt-3">
                        <x-base.form-label for="description">Descripción</x-base.form-label>
                        <textarea name="description" id="description" rows="4"
                            class="form-control w-full {{ $errors->has('description') ? 'border-red-500' : '' }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div class="mt-3">
                        <x-base.form-label for="status">Estado</x-base.form-label>
                        <x-base.tom-select name="status" id="status"
                            class="w-full {{ $errors->has('status') ? 'border-red-500' : '' }}">
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Cerrado</option>
                        </x-base.tom-select>
                        @error('status')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preguntas dinámicas -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Preguntas</h3>
                        <div id="questions-container" class="space-y-4"></div>

                        <x-base.button class="mt-3" type="button" variant="outline-secondary" onclick="addQuestion()">
                            Añadir Pregunta
                        </x-base.button>
                    </div>

                    <div class="mt-5 text-right">
                        <x-base.button class="mr-1 w-24" type="button" variant="outline-secondary"
                            onclick="window.location='{{ route('surveys.index') }}'">
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
        let questionIndex = 0;

        function addQuestion() {
            const container = document.getElementById('questions-container');

            const html = `
                <div class="box p-4 border border-gray-200 rounded" id="question-${questionIndex}">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-medium">Pregunta ${questionIndex + 1}</h4>
                        <button type="button" class="text-red-500" onclick="removeQuestion(${questionIndex})">Eliminar</button>
                    </div>

                    <div class="mt-2">
                        <x-base.form-label>Texto de la pregunta</x-base.form-label>
                        <input type="text" name="questions[${questionIndex}][text]"
                            class="form-control w-full" required />
                    </div>

                    <div class="mt-2">
                        <x-base.form-label>Tipo de pregunta</x-base.form-label>
                        <select name="questions[${questionIndex}][type]" class="form-control w-full" onchange="handleTypeChange(this, ${questionIndex})">
                            <option value="text">Texto corto</option>
                            <option value="textarea">Texto largo</option>
                            <option value="radio">Selección única</option>
                            <option value="checkbox">Selección múltiple</option>
                            <option value="select">Lista desplegable</option>
                        </select>
                    </div>

                    <div class="mt-2 options-container hidden" id="options-${questionIndex}">
                        <x-base.form-label>Opciones</x-base.form-label>
                        <div class="space-y-2" id="options-list-${questionIndex}"></div>
                        <x-base.button type="button" variant="outline-primary" onclick="addOption(${questionIndex})">Añadir Opción</x-base.button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
            questionIndex++;
        }

        function removeQuestion(index) {
            const element = document.getElementById(`question-${index}`);
            if (element) element.remove();
        }

        function handleTypeChange(select, index) {
            const value = select.value;
            const optionsContainer = document.getElementById(`options-${index}`);
            if (['radio', 'checkbox', 'select'].includes(value)) {
                optionsContainer.classList.remove('hidden');
            } else {
                optionsContainer.classList.add('hidden');
            }
        }

        function addOption(questionIndex) {
            const list = document.getElementById(`options-list-${questionIndex}`);
            const count = list.children.length;
            const input = `
                <input type="text" name="questions[${questionIndex}][options][${count}]"
                    class="form-control w-full mb-1" placeholder="Opción ${count + 1}" required />
            `;
            list.insertAdjacentHTML('beforeend', input);
        }
    </script>
@endsection
