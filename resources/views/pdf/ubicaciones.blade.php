@if($subtitle === 'informes' || $subtitle === 'informe')
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <style>
      *::after, *::before, * {
        box-sizing: border-box;
      }
      :root {
        color-scheme: light dark;
        --institucional-m-cerezo: rgb(255, 88, 95);
        --institucional-m-blanco: rgb(238, 238, 238);
        --institucional-m-gris-claro: rgb(213, 214, 210);
        --institucional-m-gris-oscuro: rgb(116, 118, 120);
      }
      body, html {
        position: relative;
        font-family: 'Open Sans', sans-serif;
        margin: 0;
        padding: 0;
      }
      h1 {
        font-family: 'Playfair Display', serif;
      }
      .logo {
        display: block;
        margin: 0 auto;
        max-width: 200px;
        height: auto;
      }
      .header {
        background-color: var(--institucional-m-cerezo);
        padding: 10px 0;
        margin: auto 0;
        width: 100%;
        position: relative;
      }
      .title {
        font-family: 'Playfair Display', serif;
        float: right;
        margin-right: 30%;
        margin-top: 5%;
        color: white;
        text-align: center;
        position: fixed;
      }
      /* Estilos del informe */
      .report-container {
        margin: 35px 40px 0 80px;
        position: relative;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
      }
      th {
        background-color: #f2f2f2;
        color: var(--institucional-m-gris-oscuro);
      }
      .user-info {
        margin-top: 20px;
        font-weight: bold;
        padding: 0px 0px 80px 0px;
      }
      .description-cell {
        padding-top: 0;
      }
      .description-row {
        font-style: italic;
        color: #555;
      }
      .text {
        text-align: start;
        margin-top: 20px;
        margin-bottom: 30px;
        text-indent: 25px;
        line-height: 1.5;
      }
      /* Nuevos estilos para firmas y sellos con float */
      .signatures-section {
        margin-top: 100px;
        clear: both; /* Evita que el contenido posterior se mueva al lado de los floats */
      }
      .signature-block {
        width: 45%;
        text-align: center;
        float: left; /* Flota los bloques a la izquierda */
        margin-right: 5%;
      }
      .signature-block:last-child {
        margin-right: 0;
      }
      .signature-line {
        border-top: 1px solid black;
        margin-top: 50px;
      }
      .signature-text {
        line-height: 0.5;
      }
      .seal {
        margin-top: 10px;
        width: 100px;
        height: auto;
      }
      .footer {
        position: relative;
        background-color: var(--institucional-m-cerezo);
        color: white;
        text-align: center;
        padding: 10px 0;
        position: absolute;
        bottom: 0;
        clear: both;
        left: 0;
        right: 0;
        width: 100%;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <div class="header">
      <img class="logo" src="{{public_path('/img/logo2.png')}}" alt="Logo de la empresa">
      <div class="title">
        <h1>{{$title}}</h1>
        <p>Fecha: {{$date}}</p>
      </div>
    </div>

    <main class="report-container">
      <div class="user-info">
        <p>Telecomunicaciones Movilnet C.A</p>
        <p>{{ $usuario->rol->nombre ?? '________________________' }}</p>
      </div>
      <p class="text">A continuación, se detalla la información completa del inventario con sus ubicaciones con fines de seguimiento y auditoría interna. 
        Este reporte incluye datos críticos sobre la existencia de productos, sus movimientos (entradas y salidas) y su descripción actual en el sistema, 
        lo cual es fundamental para una correcta gestión de activos.</p>
      @if($ubicaciones->isEmpty())
        <p>No se encontraron registros de inventario con sus ubicaciones.</p>
      @else
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Productos</th>
              <th>Origen</th>
              <th>Destino</th>
              <th>Piso</th>
              <th>Region</th>
              <th>Capital</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ubicaciones as $ubicacion)
              <tr>
                <td>{{ $ubicacion->id }}</td>
                <td>
                  @if($ubicacion->productos->isNotEmpty())
                    @foreach($ubicacion->productos as $producto)
                      {{ $producto->nombre?? '______________________' }}
                    @endforeach
                  @else
                    N/A
                  @endif
                </td>
                <td>{{ $ubicacion->origen ?? '' }}</td>
                <td>{{ $ubicacion->destino ?? '' }}</td>
                <td>{{ $ubicacion->piso ?? '' }}</td>
                <td>{{ $ubicacion->region ?? '' }}</td>
                <td>{{ $ubicacion->capital ?? '' }}</td>
              </tr>
              <tr class="description-row">
                <td colspan="6" class="description-cell">
                  <b>Descripción:</b> {{ $ubicacion->descripcion->observacion?? '' }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

      <div class="signatures-section">
        <div class="signature-block">
          <div class="signature-line"></div>
          <p class="signature-text">Firma y Sello</p>
          <p class="signature-text">{{ $usuario->rol->nombre ?? ''}}</p>
          <p class="signature-text">{{ $usuario->nombre?? '______________________' }} {{ $usuario->apellido?? '' }}</p>
          <p class="signature-text">C.I: {{ $usuario->cedula?? '______________________' }}</p>
        </div>
        <div class="signature-block">
          <div class="signature-line"></div>
          <p class="signature-text">Firma y Sello</p>
        </div>
      </div>
    </main>

    <div class="footer">
      Telecomunicaciones Movilnet C.A. RIF: G-20016137-0. Todos los derechos reservados.
    </div>
  </body>
  </html>
@endif