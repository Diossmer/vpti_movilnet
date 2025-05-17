<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        /* Estilos CSS para el PDF */
        body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        }

        table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        }

        th, td {
        padding: 10px;
        border: 1px solid #ddd;
        }

        th {
        background-color: #f2f2f2;
        }

        .footer {
        background-color: #333;
        color: #fff;
        padding: 10px;
        text-align: center;
        display: flex;
        justify-content: space-between;
        align-items: center;
        }

        .logo {
        font-weight: bold;
        }

        .address, .week {
        font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Fecha: {{ $date }}</p>

    <table>
    <thead>
      <tr>
        <th>N°</th>
        <th>NOMBRE Y APELLIDO</th>
        <th>Lunes:</th>
        <th>Martes:</th>
        <th>Miércoles:</th>
        <th>Jueves:</th>
        <th>Viernes:</th>
      </tr>
    </thead>
    <tbody>
    @foreach ($asistencias as $asistencia)
        <p></p>
        <tr>
            <td>{{$asistencia->usuario->id}}</td>
            <td>{{$asistencia->usuario->nombre}}{{$asistencia->usuario->apellido}}<br>C.I: {{$asistencia->usuario->cedula}}</td>
            <td>
                @if ($asistencia->lunes)
                    Hora entrada:{{$asistencia->hora_entrada}}<br>Hora salida:<br>Firma
                    @else
                    Hora entrada:<br>Hora salida:<br>Firma
                @endif
            </td>
            <td>
                @if ($asistencia->martes)
                    Hora entrada:{{$asistencia->hora_entrada}}<br>Hora salida:<br>Firma
                    @else
                    Hora entrada:<br>Hora salida:<br>Firma
                @endif
            </td>
            <td>
                @if ($asistencia->miercoles)
                    Hora entrada:{{$asistencia->hora_entrada}}<br>Hora salida:<br>Firma
                    @else
                    Hora entrada:<br>Hora salida:<br>Firma
                @endif
            </td>
            <td>
                @if ($asistencia->jueves)
                    Hora entrada:{{$asistencia->hora_entrada}}<br>Hora salida:<br>Firma
                    @else
                    Hora entrada:<br>Hora salida:<br>Firma
                @endif
            </td>
            <td>
                @if ($asistencia->viernes)
                    Hora entrada:{{$asistencia->hora_entrada}}<br>Hora salida:<br>Firma
                    @else
                    Hora entrada:<br>Hora salida:<br>Firma
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
  </table>

  <div class="footer">
    <div class="logo">EL SISTEMA</div>
    <div class="address">Dirección de Lutheria</div>
    <div class="week">Semana:</div>
  </div>

</body>
</html>
