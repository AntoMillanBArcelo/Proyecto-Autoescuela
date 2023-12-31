<?php

if( $_SESSION['user']->getRol()!= 'admin' && $_SESSION['user']->getRol()!= 'profesor')
{
    header("Location: ?menu=inicio");
    exit();
} 


 /* ----------Crear Preguntas----------- */
if (isset($_POST['create'])) 
{
    $enunciado = $_POST['enunciado'];
    $opcion1 = $_POST['opcion1'];
    $opcion2 = $_POST['opcion2'];
    $opcion3 = $_POST['opcion3'];
    $correcta = $_POST['correcta'];
    $url = ''; 

    //Verifica si se ha enviado un archivo a través de un formulario
    if (isset($_FILES['url']) && $_FILES['url']['error'] === UPLOAD_ERR_OK) 
    {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["url"]["name"]);
        
        if (move_uploaded_file($_FILES["url"]["tmp_name"], $target_file)) 
        {
            $url = $target_file;
        } 
        else 
        {
            echo "Error al subir la foto.";
        }
    }

    $stmt = $con->prepare("INSERT INTO pregunta (enunciado, respuesta1, respuesta2, respuesta3, correcta, url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$enunciado, $opcion1, $opcion2, $opcion3, $correcta, $url]);
}



/* ----------Mostrar Todas las Preguntas----------- */
$preguntas = $con->query("SELECT pregunta.*, dificultad.nombre as dificultad_nombre, categoria.nombre as categoria_nombre
                          FROM pregunta 
                          LEFT JOIN dificultad ON pregunta.dificultad_id = dificultad.id
                          LEFT JOIN categoria ON pregunta.categoria_id = categoria.id;")->fetchAll(PDO::FETCH_ASSOC);



echo "<table class='user'>";
echo "<tr>
        <th>ID</th>
        <th>Enunciado</th>
        <th>Respuesta 1</th>
        <th>Respuesta 2</th>
        <th>Respuesta 3</th>
        <th>Correcta</th>
        <th>Dificultad</th>
        <th>Categoria</th>
        <th>Acciones</th>
      </tr>";

    foreach ($preguntas as $pregunta) 
    {
        echo "<tr>";
        echo "<td>" . $pregunta['id'] . "</td>";
        echo "<td>" . $pregunta['enunciado'] . "</td>";
        echo "<td>" . $pregunta['respuesta1'] . "</td>";
        echo "<td>" . $pregunta['respuesta2'] . "</td>";
        echo "<td>" . $pregunta['respuesta3'] . "</td>";
        echo "<td>" . $pregunta['correcta'] . "</td>";
        echo "<td>" . $pregunta['dificultad_nombre'] . "</td>";
        echo "<td>" . $pregunta['categoria_nombre'] . "</td>";
        echo "<td>
                <form method='POST'>
                    <input type='hidden' name='preguntaBorrar' value='" . $pregunta['id'] . "'>
                    <button type='submit' name='borra'>Eliminar</button>
                </form>
              </td>";
        echo "</tr>";
    }
    
    
echo '</table>';


/* ----------Borra Preguntas----------- */
if (isset($_POST['borra']) && isset($_POST['preguntaBorrar'])) 
{
    $id = $_POST['preguntaBorrar'];

    $stmtDeletePregunta = $con->prepare("DELETE FROM pregunta WHERE id = ?");
    $stmtDeletePregunta->execute([$id]);
    

}


/* ----------Actualiza Preguntas----------- */
if (isset($_POST['updatePregunta'])) {
    $url = isset($_POST['url']) ? $_POST['url'] : '';
    $id = $_POST['id'];
    $enunciado = $_POST['enunciado'];
    $respuesta1 = $_POST['respuesta1'];
    $respuesta2 = $_POST['respuesta2'];
    $respuesta3 = $_POST['respuesta3'];
    $correcta = $_POST['correcta'];
    $tipoUrl = $_POST['tipoUrl'];
    $dificultad = $_POST['dificultad'];
    $categoria = $_POST['categoria']; // Obtener la categoría seleccionada

    $stmt = $con->prepare("UPDATE `pregunta` 
        SET `enunciado` = ?, `respuesta1` = ?, `respuesta2` = ?, `respuesta3` = ?, 
            `correcta` = ?, `url` = ?, `tipoUrl` = ?, `dificultad_id` = ?, `categoria_id` = ?
        WHERE `pregunta`.`id` = ?");

    $stmt->execute([$enunciado, $respuesta1, $respuesta2, $respuesta3, $correcta, $url, $tipoUrl, $dificultad, $categoria, $id]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="./css/styleCRUD.css">
</head>
<body>
    <div class="body">
    <!-- Select de Administracion de Preguntas -->
        <select id="formSelector">
            <option value="showCreate">Mostrar Crear Pregunta</option>
            <option value="showEdit">Mostrar Editar Pregunta</option>
            <option value="hideForm">Ocultar Formulario</option>
        </select>   
        
        <!-- Formulario que crea preguntas -->
        <form method="POST" enctype="multipart/form-data" id="createForm" class="hidden-form">
            <input type="text" name="enunciado" placeholder="Enunciado">
            <input type="text" name="opcion1" placeholder="Opción 1">
            <input type="text" name="opcion2" placeholder="Opción 2">
            <input type="text" name="opcion3" placeholder="Opción 3">
            <br>
            <label for="correcta">Selecciona la opción correcta:</label>
            <select name="correcta">
                <option value="1">Opción 1</option>
                <option value="2">Opción 2</option>
                <option value="3">Opción 3</option>
            </select>
            <br>
            <input type="file" name="url" id="foto" accept="image/*">
            <br>
            <button type="submit" name="create">Crear pregunta</button>
        </form>

        <!-- Formulario que edita preguntas -->
        <form method="POST" id="editForm" class="hidden-form">

            <label for="id">Seleccione la ID a Editar:</label>
            <select name="id">
                <?php
                    // Obtener todas las IDs de la base de datos
                    $ids = $con->query("SELECT id FROM pregunta;")->fetchAll(PDO::FETCH_COLUMN);

                    // Crear opciones para el select
                    foreach ($ids as $id) 
                    {
                        echo "<option value='$id'>$id</option>";
                    }
                ?>
            </select>

            <br>

            <input type="text" name="enunciado" placeholder="Nuevo enunciado">
            <input type="text" name="respuesta1" placeholder="respuesta1">
            <input type="text" name="respuesta2" placeholder="respuesta2">
            <input type="text" name="respuesta3" placeholder="respuesta3">

            <br>

            <label for="correcta">Seleccione la respuesta correcta:</label>

            <br>

            <select name="correcta">
                <option value="1">Opción 1</option>
                <option value="2">Opción 2</option>
                <option value="3">Opción 3</option>
            </select>

            <br>

            <label for="tipoUrl">Seleccione el tipo de URL:</label>

            <br>

            <select name="tipoUrl">
                <option value="imagen">Imagen</option>
                <option value="video">Video</option>
            </select>

            <br>

            <label for="dificultad">Seleccione la dificultad:</label>

            <br>

            <select name="dificultad" required>
                <?php
                    $dificultades = $con->query("SELECT * FROM dificultad;")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($dificultades as $dificultad) 
                    {
                        echo "<option value='" . $dificultad['id'] . "'>" . $dificultad['nombre'] . "</option>";
                    }
                ?>
            </select>

            <br>

            <label for="categoria">Seleccione la categoría:</label>
            <select name="categoria">
                <?php
                $categorias = $con->query("SELECT id, nombre FROM categoria;")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categorias as $categoria) 
                {
                    echo "<option value='{$categoria['id']}'>{$categoria['nombre']}</option>";
                }
                ?>
            </select>

            <br>

            <button type="submit" name="updatePregunta">Editar Pregunta</button>
            <button type="submit" name="updatePregunta">Editar Pregunta</button>
        </form>

        <button class="btnPreg"><a href="?menu=crudExamenes">VER EXAMENES</a></button>

        <!-- JavaScript que permite ocultar y mostrar formularios -->
        <script>
            document.getElementById('formSelector').addEventListener('change', function () 
            {
                var createForm = document.getElementById('createForm');
                var editForm = document.getElementById('editForm');

                if (this.value === 'showCreate') 
                {
                    createForm.classList.remove('hidden-form');
                    editForm.classList.add('hidden-form');
                } 
                else if (this.value === 'showEdit') 
                {
                    createForm.classList.add('hidden-form');
                    editForm.classList.remove('hidden-form');
                } 
                else 
                {
                    createForm.classList.add('hidden-form');
                    editForm.classList.add('hidden-form');
                }
            });
        </script>
        </div>
</body>
</html>