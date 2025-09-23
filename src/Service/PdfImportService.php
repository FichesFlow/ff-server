<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PdfImportService {

    public function parse(UploadedFile $file): ImportResult
    {
        $cards = [];
        $cardCount = 0;
        $config = new Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);
        $parser = new Parser([], $config);

        try {
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();

            if (empty($text)) {
                return new ImportResult([], 'No cards found');
            }

            $lines = array_map('trim', explode("\n", $text));
            array_push($lines, "");
            $sections = array_chunk($lines, 3);
        } catch (Exception $e) {
            return new ImportResult([], 'Failed to process PDF: ' . $e->getMessage());
        }

        foreach ($sections as $section) {
            $cardCount++;

            if (empty($section[0]) || empty($section[1])) {
                return new ImportResult([], 'Missing front or back for Card '.$cardCount);
            } elseif ($section[2]) {
                return new ImportResult([], 'There must be a line break between each card');
            }

            $cardDto = new CardDto();
            $cardDto->front = $section[0];
            $cardDto->back = $section[1];
            $cards[] = $cardDto;
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}