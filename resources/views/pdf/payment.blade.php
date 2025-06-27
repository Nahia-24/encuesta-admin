<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            font-size: 11px;
            color: #333;
        }

        .invoice-box {
            max-width: 720px;
            margin: auto;
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
            color: #008083;
        }

        h3 {
            margin-top: 20px;
            border-bottom: 1px solid #008083;
            padding-bottom: 5px;
            color: #008083;
        }

        .table-2col {
            width: 100%;
            table-layout: fixed;
            border-spacing: 10px;
        }

        .table-2col td {
            vertical-align: top;
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            padding: 12px;
            border-radius: 6px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .box {
            background: #f9f9f9;
            padding: 10px 15px;
            border: 1px dashed #ccc;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .proof {
            margin-top: 20px;
        }

        .proof img {
            max-width: 40%;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #aaa;
            margin-top: 30px;
        }
    </style>
</head>

<body>
        <h1>Detalles del Pago</h1>

        <table class="table-2col">
            <tr>
                <td>
                    <h3>Datos del Pagador</h3>
                    <p><span class="label">Nombre:</span> {{ $payment->payer_name }}</p>
                    <p><span class="label">Tipo Doc:</span> {{ $payment->payer_document_type }}</p>
                    <p><span class="label">Nro Doc:</span> {{ $payment->payer_document_number }}</p>
                    <p><span class="label">Valor:</span> ${{ $payment->amount }}</p>
                    <p><span class="label">Forma de Pago:</span> {{ $payment->payment_method }}</p>
                </td>
                <td>
                    <h3>Información del Evento</h3>
                    <p><span class="label">Evento:</span> {{ $payment->eventAssistant->event->name }}</p>
                    <p><span class="label">Fecha:</span> {{ $payment->eventAssistant->event->event_date }}</p>
                    <p><span class="label">Hora Inicio:</span> {{ $payment->eventAssistant->event->start_time }}</p>
                    <p><span class="label">Hora Fin:</span> {{ $payment->eventAssistant->event->end_time }}</p>
                    <p><span class="label">Ciudad:</span> {{ $payment->eventAssistant->event->city->name ?? 'N/A' }}
                    </p>
                    <p><span class="label">Capacidad:</span> {{ $payment->eventAssistant->event->capacity }}</p>
                </td>
            </tr>
        </table>

        <table class="table-2col">
            <tr>
                <td>
                    <h3>Información del Asistente</h3>

                    @foreach (json_decode($payment->eventAssistant->event->registration_parameters ?? '[]', true) as $field)
                        <p><span
                                class="label">{{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}:</span>
                            @if ($field === 'city_id')
                                {{ $payment->eventAssistant->user->city->name ?? 'N/A' }}
                            @else
                                {{ $payment->eventAssistant->user->$field }}
                            @endif
                        </p>
                    @endforeach
                    @foreach (json_decode($payment->eventAssistant->event->additionalParameters ?? '[]', true) as $parameter)
                        @php
                            $userParameter = $payment->eventAssistant->eventParameters
                                ->where('event_id', $payment->eventAssistant->event_id)
                                ->where('additional_parameter_id', $parameter['id'])
                                ->first();
                        @endphp
                        <p><span class="label">{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}:</span>
                            {{ $userParameter ? $userParameter->value : '-' }}</p>
                    @endforeach

                </td>
                <td>

                    <h3>Detalles del Ticket</h3>

                    <p><span class="label">Nombre:</span>
                        {{ $payment->eventAssistant->ticketType?->name ?? 'SIN REGISTRO' }}</p>
                    <p><span class="label">Características:</span>
                        @if (!empty($payment->eventAssistant?->ticketType?->features))
                            {{ implode(', ', array_map('trim', explode(',', $payment->eventAssistant->ticketType->features))) }}
                        @else
                            <em>No hay características</em>
                        @endif
                    </p>
                    <p><span class="label">Precio:</span>
                        ${{ $payment->eventAssistant->ticketType?->formattedPrice() }}</p>

                </td>
            </tr>
        </table>

        @if ($payment->payment_proof)
            <div class="proof">
                <h3>Comprobante de Pago</h3>
                <img src="{{ public_path('storage/' . $payment->payment_proof) }}" alt="Comprobante de pago">
            </div>
        @endif

        <div class="footer">
            Gracias por su compra — Evento gestionado por {{ config('app.name') }}
        </div>
</body>

</html>
