<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class XlsxImportService {

    public function parse(UploadedFile $file): ImportResult
    {
        $cards = [];
        $cardCount = 0;

        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $header = array_map('strtolower', $sheet->rangeToArray('A1:D1')[0]);

        if (!in_array('front', $header) || !in_array('back', $header)) {
            return new ImportResult([], 'Incorrect header');
        }


        foreach ($sheet->getRowIterator(2) as $row) {
            $cardCount++;
            $cells = $sheet->rangeToArray('A'.$row->getRowIndex().':D'.$row->getRowIndex())[0];

            if (empty($cells[0])) {
                return new ImportResult([], 'Missing front for Card '.$cardCount);
            } elseif (empty($cells[1])) {
                return new ImportResult([], 'Missing back for Card '.$cardCount);
            }

            $cardDto = new CardDto();
            $cardDto->front = $cells[0];
            $cardDto->back = $cells[1];
            $cardDto->hint = is_null($cells[2]) ? '' : $cells[2];
            $cardDto->tags = empty($cells[3]) ? [] : explode(",", $cells[3]);
            $cards[] = $cardDto;
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}
