<?php
// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "data_name";

$PRIMARY = 'PRIMARY';
$ENGINE = 'ENGINE';
$CONSTRAINT = 'CONSTRAINT';
// Crear conexión
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    die("Conexión fallida: " . $e->getMessage());
}

// Crear un archivo SQL
$sql_file = "$dbname.sql";
$handle = fopen($sql_file, 'w');

// Escribir encabezado SQL
fwrite($handle, "-- MySQL dump\n");
fwrite($handle, "-- Host: $servername\n");
fwrite($handle, "-- Database: $dbname\n\n");
fwrite($handle, "-- --------------------------------------------------------\n\n");

// Obtener todas las tablas
$result = $conn->query("SHOW TABLES");

// Recorrer cada tabla y exportar su estructura y datos
while ($row = $result->fetch_row()) {
    $tabla = "";
    $table_name = $row[0];
    fwrite($handle, "-- --------------------------------------------------------\n\n");
    // Exportar estructura de la tabla
    fwrite($handle, "-- Estructura de la tabla $table_name\n\n");

    $result2 = $conn->query('SHOW CREATE TABLE ' . $table_name);
    $row2 = $result2->fetch_row();


    $v1 = explode($PRIMARY, $row2[1]);
    $v2 = explode($ENGINE, $row2[1]);
    $v3 = explode($PRIMARY, $row2[1]);
    $v4 = explode(',', $v3[1]);
    if (count($v4) > 1) {
        $tabla = $v1[0] . $PRIMARY . " " . $v4[0] . " )" . $ENGINE . $v2[1] . ";\n\n";
    } else {
        $tabla = $v1[0] . $PRIMARY . " " . $v4[0] . ";\n\n";
    }
    fwrite($handle, $tabla . "\n\n");

    fwrite($handle, "-- --------------------------------------------------------\n\n");

    // Exportar datos de la tabla
    fwrite($handle, "-- Volcando datos para la tabla $table_name\n\n");
    $result2 = $conn->query('SELECT * FROM ' . $table_name);
    $fields_num = $result2->field_count;

    while ($row = $result2->fetch_row()) {
        $line = "INSERT INTO $table_name VALUES (";
        for ($i = 0; $i < $fields_num; $i++) {
            $line .= "'" . $conn->real_escape_string($row[$i]) . "'";
            if ($i < $fields_num - 1) {
                $line .= ", ";
            }
        }
        $line .= ");\n";
        fwrite($handle, $line);
    }

    fwrite($handle, "\n");
}


// Obtener todas las tablas
$result = $conn->query("SHOW TABLES");

// Recorrer cada tabla y exportar su estructura y datos
while ($row = $result->fetch_row()) {
    $ALTER = "";
    $ALTERFINAL = "";

    fwrite($handle, "-- --------------------------------------------------------\n\n");
    
    $table_name = $row[0];
    // Exportar estructura de la tabla
    fwrite($handle, "-- Indices de la tabla $table_name\n\n");

    $result2 = $conn->query('SHOW CREATE TABLE ' . $table_name);
    $row2 = $result2->fetch_row();

    $v5 = explode($CONSTRAINT, $row2[1]);

    if (count($v5) > 1) {
        $ALTER = "ALTER TABLE `" . $row2[0] . "` ";
        for ($i = 1; $i < count($v5); $i++) {
            if ($i < (count($v5) - 1)) {
                $ALTER = $ALTER . "ADD " . $CONSTRAINT . $v5[$i];
            } else {
                $v6 = explode($ENGINE, $v5[$i]);
                $ALTER = $ALTER . "ADD " . $CONSTRAINT . $v6[0];
            }
        }
        $ALTERFINAL = "";

        $v7 = explode(')', $ALTER);
        for ($i = 0; $i < count($v7); $i++) {
            if ($i < (count($v7) - 2)) {
                $ALTERFINAL = $ALTERFINAL . $v7[$i].")";
            } else {
                $ALTERFINAL = $ALTERFINAL . $v7[$i];
            }
        }
        fwrite($handle, $ALTERFINAL . ";\n\n");
    }
}


fclose($handle);
$conn->close();

echo "La base de datos se ha exportado correctamente a $sql_file";
