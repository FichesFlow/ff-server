<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class TxtImportService {

    public function parse(UploadedFile $file): ImportResult
    {
        $cards = [];
        $cardCount = 1;

        if ($file = fopen($file->getPathname(), "r")) {
            $cardDTO = new CardDto();
            $hasCardHeader = false;

            while(!feof($file)) {
                $line = fgets($file);

                if (str_starts_with($line, 'Carte')) {
                    if ($hasCardHeader) { // Either the front or back is missing
                        if (empty($cardDTO->front)) {
                            return new ImportResult([], 'Missing front for Card '.$cardCount);
                        } elseif (empty($cardDTO->back)) {
                            return new ImportResult([], 'Missing back for Card '.$cardCount);
                        }
                    }

                    $hasCardHeader = true;
                } elseif (str_starts_with($line, 'front:')) { // Front side
                    if (!$hasCardHeader) {
                        fclose($file);
                        return new ImportResult([], 'Missing header for Card '.$cardCount);
                    }

                    $cardDTO->front = trim(substr($line, 6));
                } elseif (str_starts_with($line, 'back:')) { // Back side
                    if (!$hasCardHeader) {
                        fclose($file);
                        return new ImportResult([], 'Missing header for Card '.$cardCount);
                    }

                    $cardDTO->back = trim(substr($line, 5));

                    if (empty($cardDTO->front)) {
                        fclose($file);
                        return new ImportResult([], 'Missing front for Card '.$cardCount);
                    }

                    $cards[] = $cardDTO;
                    $cardCount++;
                    $cardDTO = new CardDto();
                    $hasCardHeader = false;
                }
            }

            fclose($file);

            if ($hasCardHeader) { // Either the front or back for the last card is missing
                if (empty($cardDTO->front)) {
                    return new ImportResult([], 'Missing front for Card '.$cardCount);
                } elseif (empty($cardDTO->back)) {
                    return new ImportResult([], 'Missing back for Card '.$cardCount);
                }
            }
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}