<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MarkdownImportService {

    public function parse(UploadedFile $file)
    {
        $cards = [];
        $cardCount = 0;

        if ($file = fopen($file->getPathname(), "r")) {
            $cardDto = new CardDto();
            $front = '';
            $back = '';

            while(!feof($file)) {
                $line = trim(fgets($file));

                if (str_starts_with($line, '## ')) { // New card
                    if (!empty($front) && empty($back)) { // Missing back side for previous card
                        fclose($file);
                        return new ImportResult([], 'Card '.$cardCount.' incomplete: missing back side or separator');
                    }

                    $cardCount++;
                    $front = trim(fgets($file));
                    $cardDto->front = $front;
                } elseif ($line == '---') { // Separator
                    $back = trim(fgets($file));

                    if (empty($front)) { // Missing front side for current card
                        fclose($file);
                        return new ImportResult([], 'Card '.$cardCount.' incomplete: missing front side or header');
                    } elseif (empty($back)) { // Missing back side for current card
                        fclose($file);
                        return new ImportResult([], 'Card '.$cardCount.' incomplete: missing back side or separator');
                    }

                    $cardDto->back = $back;
                    $cards[] = $cardDto;
                    $cardDto = new CardDto();
                    $front = '';
                    $back = '';
                }
            }

            fclose($file);

            if (!empty($front) && empty($back)) { // Missing back side for last card
                return new ImportResult([], 'Card '.$cardCount.' incomplete: missing back side or separator');
            }
        }

        if ($cardCount == 0) {
            return new ImportResult([], 'No cards found');
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}