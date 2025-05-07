<?php
namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class ExportImportHandler {
    public static function exportToExcel($data, $headers, $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        
        // Set data
        $row = 2;
        foreach ($data as $item) {
            $column = 'A';
            foreach ($headers as $key => $header) {
                $sheet->setCellValue($column . $row, $item[$key] ?? '');
                $column++;
            }
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', $column) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Save file
        $writer = new Xlsx($spreadsheet);
        $filepath = __DIR__ . '/../../exports/' . $filename . '.xlsx';
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer->save($filepath);
        
        return $filepath;
    }
    
    public static function importFromExcel($filepath, $mapping) {
        if (!file_exists($filepath)) {
            throw new \Exception('File not found');
        }
        
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($filepath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $data = [];
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Get headers
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headers[$col] = $sheet->getCell($col . '1')->getValue();
        }
        
        // Process data
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            foreach ($mapping as $dbField => $excelField) {
                $col = array_search($excelField, $headers);
                if ($col !== false) {
                    $rowData[$dbField] = $sheet->getCell($col . $row)->getValue();
                }
            }
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        
        return $data;
    }
    
    public static function exportToCSV($data, $headers, $filename) {
        $filepath = __DIR__ . '/../../exports/' . $filename . '.csv';
        
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $fp = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($fp, $headers);
        
        // Write data
        foreach ($data as $item) {
            $row = [];
            foreach ($headers as $key => $header) {
                $row[] = $item[$key] ?? '';
            }
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        return $filepath;
    }
    
    public static function importFromCSV($filepath, $mapping) {
        if (!file_exists($filepath)) {
            throw new \Exception('File not found');
        }
        
        $data = [];
        $fp = fopen($filepath, 'r');
        
        // Get headers
        $headers = fgetcsv($fp);
        
        // Process data
        while (($row = fgetcsv($fp)) !== false) {
            $rowData = [];
            foreach ($mapping as $dbField => $excelField) {
                $col = array_search($excelField, $headers);
                if ($col !== false) {
                    $rowData[$dbField] = $row[$col];
                }
            }
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        
        fclose($fp);
        
        return $data;
    }
} 