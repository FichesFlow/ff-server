<?php

namespace App\Service;

use App\Dto\CardDto;
use App\Service\ImportResult;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocxImportService {

    public function parse(UploadedFile $file): ImportResult
    {
        $cards = [];
        $cardDto = null;
        $front = '';
        $back = '';
        $cardCount = 0;
        $phpWord = IOFactory::load($file->getPathname());

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof TextRun) {
                    if ($element->getParagraphStyle()->getStyleName() == 'Titre1') { // TextRun is a heading
                        if ($cardDto != null && empty($back)) { // Current card back missing
                            return new ImportResult([], 'Missing back for Card '.$cardCount);
                        }

                        $cardCount++;
                        $cardDto = new CardDto;
                        $front = $element->getText();
                    } else { // TextRun is a paragraph
                        if (empty($front)) {
                            return new ImportResult([], 'Missing front for Card '.$cardCount+1);
                        }

                        $back = $element->getText();

                        if (empty($back)) {
                            return new ImportResult([], 'Missing back for Card '.$cardCount);
                        }
    
                        $cardDto->front = $front;
                        $cardDto->back = $back;
                        $cards[] = $cardDto;
                        $cardDto = null;
                        $front = '';
                        $back = '';
                    }
                }
            }

            if ($cardDto != null && empty($back)) { // Last card back missing
                return new ImportResult([], 'Missing back for Card '.$cardCount);
            }
        }

        if ($cardCount == 0) {
            return new ImportResult([], 'No cards found');
        }

        $importResult = new ImportResult($cards);

        return $importResult;
    }
}