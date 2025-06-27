<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>PDF del Asistente</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 40px;
        }

        h1,
        h2 {
            color: #333;
            margin-bottom: 10px;
        }

        p {
            margin: 5px 0 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            width: 30%;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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

        .qr {
            margin-top: 20px;
            text-align: center;
        }

        .qr img {
            width: 130px;
            height: 130px;
        }

        hr {
            margin: 30px 0;
        }
    </style>
</head>

<body>

    <h1>{{ $evento->name }}</h1>
    <p><strong>Fecha del evento:</strong> {{ $evento->event_date }}</p>

    @if ($asistente->is_paid)
        <div class="alert bg-success">ESTADO DEL PAGO DEL TICKET: Pagado</div>
    @elseif ($asistente->totalPayments() == 0)
        <div class="alert bg-danger">ESTADO DE LA BOLETA DEL TICKET: No Pagado</div>
    @else
        <div class="alert bg-warning">ESTADO DE LA BOLETA DEL TICKET: Pendiente</div>
    @endif

    <hr>

    <h2>Información del Asistente</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <td>{{ $asistente->user->name }}</td>
        </tr>
        <tr>
            <th>Correo</th>
            <td>{{ $asistente->user->email }}</td>
        </tr>
        <tr>
            <th>Teléfono</th>
            <td>{{ $asistente->user->phone }}</td>
        </tr>
        <tr>
            <th>Tipo de Ticket</th>
            <td>{{ $asistente->ticketType?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Estado de Entrada</th>
            <td>{{ $asistente->has_entered ? 'Entrada' : 'No Entrada' }}</td>
        </tr>
    </table>

    <h3 style="text-align: center;">Código QR</h3>

    @if ($qrBase64)
        <img src="{{ $qrBase64 }}" alt="Código QR"
            style="width: 200px; height: 200px; display: block; margin: 0 auto;">
    @else
        <p class="text-center">QR no disponible</p>
    @endif


</body>

</html>
