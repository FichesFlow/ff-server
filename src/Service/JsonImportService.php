<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class JsonImportService {

    public function parse(UploadedFile $file)
    {
        $cards = [];
        $cardCount = 0;

        $jsonContent = file_get_contents($file->getPathname());
        $data = json_decode($jsonContent);

        if (json_last_error() != JSON_ERROR_NONE) {
            return new ImportResult([], 'Invalid JSON format');
        } elseif (gettype($data) != 'array') {
            return new ImportResult([], 'Invalid JSON structure');
        }

        foreach ($data as $item) {
            $cardDto = new CardDto();
            $cardCount++;

            if (gettype($item) != 'object') {
                return new ImportResult([],  'Invalid structure for Card '.$cardCount);
            } elseif (!property_exists($item, 'front')) {
                return new ImportResult([], 'Missing front for Card '.$cardCount);
            } elseif (!property_exists($item, 'back')) {
                return new ImportResult([], 'Missing back for Card '.$cardCount);
            }

            $cardDto->front = trim($item->front);
            $cardDto->back = trim($item->back);
            $cards[] = $cardDto;
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}