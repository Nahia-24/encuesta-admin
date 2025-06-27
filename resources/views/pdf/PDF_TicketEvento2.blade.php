<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Asistentes del Evento</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            font-size: 10px;
            color: #333;
        }

        .content {
            padding: 20px;
        }

        .card {
            background-color: white;
            margin: auto;
            max-width: 720px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 2px dashed #008083;
        }

        .header,
        .footer {
            background-color: #008083;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .section {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        h1,
        h2,
        h3 {
            margin: 5px 0;
        }

        p {
            margin: 4px 0;
        }

        .image-fit {
            width: 100%;
            max-width: 200px;
            height: auto;
            display: block;
            margin: 10px auto;
            border-radius: 6px;
        }

        ul {
            padding-left: 20px;
            margin: 6px 0;
        }

        li {
            margin: 2px 0;
        }

        .qr-code {
            text-align: center;
            padding: 15px;
        }

        .qr-code img {
            width: 130px;
            height: 130px;
        }

        .status-alert {
            padding: 8px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }

        .bg-success {
            background-color: #d4edda;
            color: #155724;
        }

        .bg-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .bg-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="card">
            <div class="header">
                <h1>Ticket del Evento</h1>
                <h2>{{ $eventAssistant->event->name }}</h2>
            </div>
            
            <div class="section" style="text-align: center;">
                <img src="{{ storage_path('app/public/' . $eventAssistant->event->header_image_path) }}"
                    alt="{{ $eventAssistant->event->name }}"
                    style="max-width: 200px; width: 100%; height: auto; border-radius: 6px;">
            </div>

            <div class="section">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td style="vertical-align: top; width: 50%; padding-right: 10px;">
                            <h3>Información del Evento</h3>
                            <p><strong>Nombre:</strong> {{ $eventAssistant->event->name }}</p>
                            <p><strong>Fecha:</strong> {{ $eventAssistant->event->event_date }}</p>
                            <p><strong>Hora de Inicio:</strong> {{ $eventAssistant->event->start_time }}</p>
                            <p><strong>Ciudad:</strong> {{ $eventAssistant->event->city->name ?? 'N/A' }}</p>
                            <p><strong>Dirección:</strong> {{ $eventAssistant->event->address }}</p>
                        </td>

                        <td style="vertical-align: top; width: 50%; padding-left: 10px;">
                            <h3>Tipo de Ticket</h3>
                            @if ($eventAssistant?->ticketType)
                                <p><strong>Tipo:</strong> {{ $eventAssistant->ticketType->name ?? 'N/A' }}</p>
                                <p><strong>Características:</strong>
                                    @php
                                        $characteristics = $eventAssistant->ticketType?->characteristics;
                                    @endphp
                                    @if ($characteristics && $characteristics->count())
                                        {{ $characteristics->pluck('name')->implode(', ') }}
                                    @else
                                        <em>No hay características</em>
                                    @endif
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="vertical-align: top; padding-right: 10px;">
                            <h3>Información del Asistente</h3>
                            @php
                                $selectedFields =
                                    json_decode($eventAssistant->event->registration_parameters, true) ?? [];
                                $additionalParameters =
                                    json_decode($eventAssistant->event->additionalParameters, true) ?? [];
                            @endphp
                            @foreach ($selectedFields as $field)
                                <p><strong>{{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}</strong>:
                                    {{ $eventAssistant->user->$field }}</p>
                            @endforeach
                            @foreach ($additionalParameters as $parameter)
                                @php
                                    $userParameter = $eventAssistant->eventParameters
                                        ->where('event_id', $eventAssistant->event_id)
                                        ->where('additional_parameter_id', $parameter['id'])
                                        ->first();
                                @endphp
                                <p><strong>{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}</strong>:
                                    {{ $userParameter ? $userParameter->value : '-' }}</p>
                            @endforeach
                            <p><strong>Fecha de Registro:</strong> {{ $eventAssistant->created_at->format('d/m/Y') }}
                            </p>
                        </td>

                        <td style="text-align: center; padding-left: 10px;">
                            <h3>Código QR</h3>
                            @if (!empty($qrCodeBase64))
                                <img src="{{ $qrCodeBase64 }}" alt="QR Code" width="130" height="130">
                            @else
                                <p>No hay código QR disponible.</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Estado del Pago -->
            <div class="section">
                <div
                    class="status-alert {{ $eventAssistant->is_paid ? 'bg-success' : ($eventAssistant->totalPayments() == 0 ? 'bg-danger' : 'bg-warning') }}">
                    ESTADO DEL PAGO:
                    {{ $eventAssistant->is_paid ? 'Pagado' : ($eventAssistant->totalPayments() == 0 ? 'No Pagado' : 'Pendiente') }}
                </div>
            </div>

            <div class="footer">
                <h3>Credencial Virtual</h3>
            </div>
        </div>
    </div>
</body>

</html>
