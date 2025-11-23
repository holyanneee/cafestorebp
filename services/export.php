<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../lib/XLSXWriter/xlsxwriter.class.php';
function toExcel(array $headers, array $sheets, $data, $filename = 'export.xlsx')
{
    // Create new Excel writer
    $writer = new XLSXWriter();

    foreach ($sheets as $sheetName) {

        // Add headers first
        if (isset($headers[$sheetName])) {
            $writer->writeSheetHeader($sheetName, $headers[$sheetName]);
        }

        // Add data rows
        if (isset($data[$sheetName])) {
            foreach ($data[$sheetName] as $row) {
                $writer->writeSheetRow($sheetName, $row);
            }
        }
    }

    // Save file
    $filePath = 'exports/' . $filename;

    // Make sure directory exists
    if (!is_dir('exports')) {
        mkdir('exports', 0777, true);
    }

    $writer->writeToFile($filePath);

    return $filePath; // return the file path so you can download it

}

function backupDatabase(PDO $conn, $databaseName, $outputFile = null)
{
    if (!$outputFile) {
        $outputFile = "backup_" . date("Y-m-d_H-i-s") . ".sql";
    }

    $sqlScript = "-- DATABASE BACKUP: $databaseName\n";
    $sqlScript .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {

        // Drop table
        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";

        // Create table
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sqlScript .= $createTable["Create Table"] . ";\n\n";

        // Insert data
        $rows = $conn->query("SELECT * FROM `$table`");

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(function ($value) {
                return isset($value) ? "'" . addslashes($value) . "'" : "NULL";
            }, array_values($row));

            $sqlScript .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }

        $sqlScript .= "\n\n";
    }

    // Save file
    file_put_contents($outputFile, $sqlScript);

    return [
        "status" => "success",
        "file" => $outputFile
    ];
}
?>