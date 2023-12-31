<?php
class Autocargador
{
    public static function autocargar()
    {

        spl_autoload_register(function($clase){
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAutoescuela/';
            $directorios = [
                'api',
                'clases',
                'css',
                'db',
                'diseño',
                'helper',
                'js',
                'vistas',
                'plantilla',
                'repositorio',
                'servidor',
                'cargador',
                'img'
            ];

            foreach ($directorios as $directorio) {
                $archivo = $baseDir . $directorio . '/' . $clase . '.php';
                if (file_exists($archivo)) {
                    require_once $archivo;
                    return;
                }
            }  

        });
    }
}
    
Autocargador::autocargar();
