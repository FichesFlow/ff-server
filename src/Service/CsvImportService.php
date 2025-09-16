<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CsvImportService {

    public function parse(UploadedFile $file): ImportResult
    {
        $cards = [];
        $cardCount = 0;

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        if (count($headers) != 2 || (!in_array('front', $headers) || !in_array('back', $headers))) {
            return new ImportResult([], 'Invalid CSV headers');
        }

        $rows = $csv->getRecords();

        foreach ($rows as $row) {
            $cardDto = new CardDto();
            $cardCount++;
            $front = trim($row['front']);
            $back = trim($row['back']);

            if (empty($front)) {
                return new ImportResult([], 'Missing front for Card '.$cardCount);
            } elseif (empty($back)) {
                return new ImportResult([], 'Missing back for Card '.$cardCount);
            }

            $cardDto->front = $front;
            $cardDto->back = $back;
            $cards[] = $cardDto;
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}