<?php
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api','except' => ['ingresar']],function ($routes) {
    #############################################################
    #########               Inicio Sesión               #########
    #############################################################
    Route::post('ingresar', [App\Http\Controllers\AuthController::class, 'ingresar'])->name('ingresar');
    Route::get('perfil', [App\Http\Controllers\AuthController::class, 'perfil'])->name('perfil');
    Route::post('salir', [App\Http\Controllers\AuthController::class, 'salir'])->name('salir');
    Route::patch('refrescar',[App\Http\Controllers\AuthController::class, 'refrescar'])->name('usuarrefrescariolistar');
    Route::patch('perfil/contraseña',[App\Http\Controllers\AuthController::class, 'refrescarContraseña'])->name('refrescarContraseña');
    //Route::post('correo/verificacion', [App\Http\Controllers\AuthController::class, 'enviarVerificacionCorreo']->name('VerificacionCorreo'));
    //Route::get('correo/verificar/{id?}', [App\Http\Controllers\AuthController::class, 'verificar'])->name('verificar');
});

Route::middleware(['AsegurarToken'])->group(function () {
    #############################################################
    #########                  Archivos                 #########
    #############################################################
    //recursos
    Route::post('importar', [App\Http\Controllers\Recursos\RecursosController::class, 'importar'])->name('importarrecursos');
    Route::get('exportar/{id?}', [App\Http\Controllers\Recursos\RecursosController::class, 'exportar'])->name('exportarrecursos');
    Route::get('pdf/{id?}/{docs?}', [App\Http\Controllers\Recursos\RecursosController::class, 'generatepdf'])->name('pdfrecursos');
    Route::get('organigrama', [App\Http\Controllers\Recursos\RecursosController::class, 'organigrama'])->name('organigramarecursos');
    #############################################################
    #########                   Usuario                 #########
    #############################################################
    Route::get('cedulausuarios/{id?}', [App\Http\Controllers\Recursos\RecursosController::class, 'verificarCedula'])->name('cedulausuarioseliminar');
});
Route::middleware(['AsegurarToken'])->group(function () {
    #############################################################
    #########               Administrativo              #########
    #########                   Usuario                 #########
    #############################################################
    Route::get('usuarios', [App\Http\Controllers\UsuariosController::class, 'index'])->name('usuariolistar');
    Route::post('usuarios', [App\Http\Controllers\UsuariosController::class, 'store'])->name('usuarioguardar');
    Route::get('usuarios/{id?}', [App\Http\Controllers\UsuariosController::class, 'show'])->name('usuariomostrar');
    Route::put('usuarios/{id?}', [App\Http\Controllers\UsuariosController::class, 'update'])->name('usuarioactualizar');
    Route::delete('usuarios/{id?}', [App\Http\Controllers\UsuariosController::class, 'destroy'])->name('usuarioeliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('usuarioimportar', [App\Http\Controllers\UsuariosController::class, 'importar'])->name('usuarioimportar');
    Route::get('usuarioexportar/{id?}', [App\Http\Controllers\UsuariosController::class, 'exportar'])->name('usuarioexportar');
    Route::get('usuariopdf/{id?}/{docs?}', [App\Http\Controllers\UsuariosController::class, 'generatepdf'])->name('usuariopdf');
    #############################################################
    #########                    Role                   #########
    #############################################################
    Route::get('rol', [App\Http\Controllers\RolesController::class, 'index'])->name('rollistar');
    Route::post('rol', [App\Http\Controllers\RolesController::class, 'store'])->name('rolguardar');
    Route::get('rol/{id?}', [App\Http\Controllers\RolesController::class, 'show'])->name('rolmostrar');
    Route::put('rol/{id?}', [App\Http\Controllers\RolesController::class, 'update'])->name('rolactualizar');
    Route::delete('rol/{id?}', [App\Http\Controllers\RolesController::class, 'destroy'])->name('roleliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('rolimportar', [App\Http\Controllers\RolesController::class, 'importar'])->name('rolimportar');
    Route::get('rolexportar/{id?}', [App\Http\Controllers\RolesController::class, 'exportar'])->name('rolexportar');
    Route::get('rolpdf/{id?}/{docs?}', [App\Http\Controllers\RolesController::class, 'generatepdf'])->name('rolpdf');
    #############################################################
    #########                   Estatus                 #########
    #############################################################
    Route::get('estatus', [App\Http\Controllers\EstatusController::class, 'index'])->name('estatuslistar');
    Route::post('estatus', [App\Http\Controllers\EstatusController::class, 'store'])->name('estatusguardar');
    Route::get('estatus/{id?}', [App\Http\Controllers\EstatusController::class, 'show'])->name('estatusmostrar');
    Route::put('estatus/{id?}', [App\Http\Controllers\EstatusController::class, 'update'])->name('estatusactualizar');
    Route::delete('estatus/{id?}', [App\Http\Controllers\EstatusController::class, 'destroy'])->name('estatuseliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('estatusimportar', [App\Http\Controllers\EstatusController::class, 'importar'])->name('estatusimportar');
    Route::get('estatusexportar/{id?}', [App\Http\Controllers\EstatusController::class, 'exportar'])->name('estatusexportar');
    Route::get('estatuspdf/{id?}/{docs?}', [App\Http\Controllers\EstatusController::class, 'generatepdf'])->name('estatuspdf');
    #############################################################
    #########               Inventarios                 #########
    #########                productos                  #########
    #############################################################
    Route::get('productos', [App\Http\Controllers\Inventario\ProductosController::class, 'index'])->name('productoslistar');
    Route::post('productos', [App\Http\Controllers\Inventario\ProductosController::class, 'store'])->name('productosguardar');
    Route::get('productos/{id?}', [App\Http\Controllers\Inventario\ProductosController::class, 'show'])->name('productosmostrar');
    Route::put('productos/{id?}', [App\Http\Controllers\Inventario\ProductosController::class, 'update'])->name('productosactualizar');
    Route::delete('productos/{id?}', [App\Http\Controllers\Inventario\ProductosController::class, 'destroy'])->name('productoseliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('productosimportar', [App\Http\Controllers\Inventario\ProductosController::class, 'importar'])->name('productosimportar');
    Route::get('productosexportar/{id?}', [App\Http\Controllers\Inventario\ProductosController::class, 'exportar'])->name('productosexportar');
    Route::get('productospdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\ProductosController::class, 'generatepdf'])->name('productospdf');
    #############################################################
    #########                  ubicacion                #########
    #############################################################
    Route::get('ubicacion', [App\Http\Controllers\Inventario\UbicacionController::class, 'index'])->name('ubicacionlistar');
    Route::post('ubicacion', [App\Http\Controllers\Inventario\UbicacionController::class, 'store'])->name('ubicacionguardar');
    Route::get('ubicacion/{id?}', [App\Http\Controllers\Inventario\UbicacionController::class, 'show'])->name('ubicacionmostrar');
    Route::put('ubicacion/{id?}', [App\Http\Controllers\Inventario\UbicacionController::class, 'update'])->name('ubicacionactualizar');
    Route::delete('ubicacion/{id?}', [App\Http\Controllers\Inventario\UbicacionController::class, 'destroy'])->name('ubicacioneliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('ubicacionimportar', [App\Http\Controllers\Inventario\UbicacionController::class, 'importar'])->name('ubicacionimportar');
    Route::get('ubicacionexportar/{id?}', [App\Http\Controllers\Inventario\UbicacionController::class, 'exportar'])->name('ubicacionexportar');
    Route::get('ubicacionpdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\UbicacionController::class, 'generatepdf'])->name('ubicacionpdf');
    #############################################################
    #########                 descripcion               #########
    #############################################################
    Route::get('descripcion', [App\Http\Controllers\Inventario\DescripcionController::class, 'index'])->name('descripcionlistar');
    Route::post('descripcion', [App\Http\Controllers\Inventario\DescripcionController::class, 'store'])->name('descripcionguardar');
    Route::get('descripcion/{id?}', [App\Http\Controllers\Inventario\DescripcionController::class, 'show'])->name('descripcionmostrar');
    Route::put('descripcion/{id?}', [App\Http\Controllers\Inventario\DescripcionController::class, 'update'])->name('descripcionactualizar');
    Route::delete('descripcion/{id?}', [App\Http\Controllers\Inventario\DescripcionController::class, 'destroy'])->name('descripcioneliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('descripcionimportar', [App\Http\Controllers\Inventario\DescripcionController::class, 'importar'])->name('descripcionimportar');
    Route::get('descripcionexportar/{id?}', [App\Http\Controllers\Inventario\DescripcionController::class, 'exportar'])->name('descripcionexportar');
    Route::get('descripcionpdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\DescripcionController::class, 'generatepdf'])->name('descripcionpdf');
    #############################################################
    #########                 Inventarios               #########
    #############################################################
    Route::get('inventarios', [App\Http\Controllers\Inventario\InventariosController::class, 'index'])->name('inventarioslistar');
    Route::post('inventarios', [App\Http\Controllers\Inventario\InventariosController::class, 'store'])->name('inventariosguardar');
    Route::get('inventarios/{id?}', [App\Http\Controllers\Inventario\InventariosController::class, 'show'])->name('inventariosmostrar');
    Route::put('inventarios/{id?}', [App\Http\Controllers\Inventario\InventariosController::class, 'update'])->name('inventariosactualizar');
    Route::delete('inventarios/{id?}', [App\Http\Controllers\Inventario\InventariosController::class, 'destroy'])->name('inventarioseliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('inventariosimportar', [App\Http\Controllers\Inventario\InventariosController::class, 'importar'])->name('inventariosimportar');
    Route::get('inventariosexportar/{id?}', [App\Http\Controllers\Inventario\InventariosController::class, 'exportar'])->name('inventariosexportar');
    Route::get('inventariospdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\InventariosController::class, 'generatepdf'])->name('inventariospdf');
    #############################################################
    #########                 Perifericos               #########
    #############################################################
    Route::get('perifericos', [App\Http\Controllers\Inventario\PerifericosController::class, 'index'])->name('perifericoslistar');
    Route::post('perifericos', [App\Http\Controllers\Inventario\PerifericosController::class, 'store'])->name('perifericosguardar');
    Route::get('perifericos/{id?}', [App\Http\Controllers\Inventario\PerifericosController::class, 'show'])->name('perifericosmostrar');
    Route::put('perifericos/{id?}', [App\Http\Controllers\Inventario\PerifericosController::class, 'update'])->name('perifericosactualizar');
    Route::delete('perifericos/{id?}', [App\Http\Controllers\Inventario\PerifericosController::class, 'destroy'])->name('perifericoseliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('perifericosimportar', [App\Http\Controllers\Inventario\PerifericosController::class, 'importar'])->name('perifericosimportar');
    Route::get('perifericosexportar/{id?}', [App\Http\Controllers\Inventario\PerifericosController::class, 'exportar'])->name('perifericosexportar');
    Route::get('perifericospdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\PerifericosController::class, 'generatepdf'])->name('perifericospdf');
    #############################################################
    #########                Evaluaciones               #########
    #############################################################
    Route::get('evaluaciones', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'index'])->name('evaluacioneslistar');
    Route::post('evaluaciones', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'store'])->name('evaluacionesguardar');
    Route::get('evaluaciones/{id?}', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'show'])->name('evaluacionesmostrar');
    Route::put('evaluaciones/{id?}', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'update'])->name('evaluacionesactualizar');
    Route::delete('evaluaciones/{id?}', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'destroy'])->name('evaluacioneseliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('evaluacionesimportar', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'importar'])->name('evaluacionesimportar');
    Route::get('evaluacionesexportar/{id?}', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'exportar'])->name('evaluacionesexportar');
    Route::get('evaluacionespdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\EvaluacionesController::class, 'generatepdf'])->name('evaluacionespdf');
    #############################################################
    #########                 asignacion                #########
    #############################################################
    Route::get('asignacion', [App\Http\Controllers\Inventario\AsignacionController::class, 'index'])->name('asignacionlistar');
    Route::post('asignacion', [App\Http\Controllers\Inventario\AsignacionController::class, 'store'])->name('asignacionguardar');
    Route::get('asignacion/{id?}', [App\Http\Controllers\Inventario\AsignacionController::class, 'show'])->name('asignacionmostrar');
    Route::put('asignacion/{id?}', [App\Http\Controllers\Inventario\AsignacionController::class, 'update'])->name('asignacionactualizar');
    Route::delete('asignacion/{id?}', [App\Http\Controllers\Inventario\AsignacionController::class, 'destroy'])->name('asignacioneliminar');
    /////////////////////////////////////////////////////////////
    #########                  Archivos                 #########
    /////////////////////////////////////////////////////////////
    Route::post('asignacionimportar', [App\Http\Controllers\Inventario\AsignacionController::class, 'importar'])->name('asignacionimportar');
    Route::get('asignacionexportar/{id?}', [App\Http\Controllers\Inventario\AsignacionController::class, 'exportar'])->name('asignacionexportar');
    Route::get('asignacionpdf/{id?}/{docs?}', [App\Http\Controllers\Inventario\AsignacionController::class, 'generatepdf'])->name('asignacionpdf');
});
