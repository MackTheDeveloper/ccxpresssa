<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;




use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ExportFromArray implements FromArray, ShouldAutoSize, WithStyles, WithDefaultStyles, WithColumnWidths
{
    protected $data;

    public function columnWidths(): array
    {
        return [
            'A' => 70,
            'B' => 15,
           // 'D' => 15,
            'E' => 15,
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {


        // Or return the styles array
        return [
            'font' => [
                'name'   => 'Arial',
                'size' => '8'
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setTitle('Open Invoices');
        $sheet->getRowDimension('1')->setRowHeight(15);
        $sheet->getRowDimension('2')->setRowHeight(15);
        $sheet->getStyle('A')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('B5:G5')->getFont()->setSize(9)->setBold(true);
        $sheet->getStyle('B5:G5')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1')->getFont()->setSize('14')->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize('14')->setBold(true);
        $sheet->getStyle('A3')->getFont()->setSize('10')->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A')->getFont()->setBold(true);
        $sheet->getStyle('F')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('B:D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        /* $sheet->getStyle('B2')
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];

        $sheet->getStyle('A3', 'B6')->applyFromArray($styleArray);

        return [
            // Style the first row as bold text.
            //  'A1'    => ['font' => ['bold' => true]],
            'A1'    => ['color' => ['rgb' => '808080']],
        ]; */
    }

    public function __construct(array $invoices)
    {
        $this->data = $invoices;
    }

    public function array(): array
    {
        //pre($this->data);
        return $this->data;
    }
}
