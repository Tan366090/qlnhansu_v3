<?php

namespace App\Libraries;

use League\Csv\Writer;
use Dompdf\Dompdf;
use Dompdf\Options;

class Export
{
    public function employees()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('employees e');
        
        $builder->select('e.*, up.*, d.name as department_name');
        $builder->join('user_profiles up', 'e.id = up.user_id');
        $builder->join('departments d', 'up.department_id = d.id');
        
        return $builder->get()->getResultArray();
    }

    public function departments()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('departments');
        
        return $builder->get()->getResultArray();
    }

    public function projects()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('projects p');
        
        $builder->select('p.*, COUNT(pr.id) as resource_count');
        $builder->join('project_resources pr', 'p.id = pr.project_id', 'left');
        $builder->groupBy('p.id');
        
        return $builder->get()->getResultArray();
    }

    public function download($data, $type, $format = 'csv')
    {
        $filename = "{$type}_" . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    private function exportCSV($data, $type)
    {
        $filename = "{$type}_" . date('Y-m-d') . '.csv';
        
        $csv = Writer::createFromString('');
        
        // Write headers
        if (!empty($data)) {
            $csv->insertOne(array_keys($data[0]));
        }
        
        // Write data
        $csv->insertAll($data);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $csv->getContent();
        exit;
    }

    private function exportPDF($data, $type)
    {
        $filename = "{$type}_" . date('Y-m-d') . '.pdf';
        
        $html = view('exports/pdf_template', [
            'data' => $data,
            'type' => $type
        ]);
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $dompdf->output();
        exit;
    }
} 