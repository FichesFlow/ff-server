<?php

namespace App\Controller;

use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportController extends AbstractController
{
    #[Route('api/import/text', name: 'api_import_text', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importText(Request $request): JsonResponse
    {
        $cards = [];
        $cardCount = 1;
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType != 'text/plain') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 1048576) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        if ($file = fopen($pathName, "r")) {
            $currentCard = ['front' => '', 'back' => ''];
            $hasCardHeader = false;

            while(!feof($file)) {
                $line = fgets($file);

                if (str_starts_with($line, 'Carte')) {
                    if ($hasCardHeader) { // Either the front or back is missing
                        if (empty($currentCard['front'])) {
                            return $this->json(['message' => 'Missing front for Card '.$cardCount], 422);
                        } elseif (empty($currentCard['back'])) {
                            return $this->json(['message' => 'Missing back for Card '.$cardCount], 422);
                        }
                    }

                    $hasCardHeader = true;
                } elseif (str_starts_with($line, 'front:')) { // Front side
                    if (!$hasCardHeader) {
                        fclose($file);
                        return $this->json(['message' => 'Missing header for Card '.$cardCount], 422);
                    }

                    $currentCard['front'] = trim(substr($line, 6));
                } elseif (str_starts_with($line, 'back:')) { // Back side
                    if (!$hasCardHeader) {
                        fclose($file);
                        return $this->json(['message' => 'Missing header for Card '.$cardCount], 422);
                    }

                    $currentCard['back'] = trim(substr($line, 5));

                    if (empty($currentCard['front'])) {
                        fclose($file);
                        return $this->json(['message' => 'Missing front for Card '.$cardCount], 422);
                    }

                    $cards[] = $currentCard;
                    $cardCount++;
                    $currentCard = ['front' => '', 'back' => ''];
                    $hasCardHeader = false;
                }
            }

            fclose($file);

            if ($hasCardHeader) { // Either the front or back for the last card is missing
                if (empty($currentCard['front'])) {
                    return $this->json(['message' => 'Missing front for Card '.$cardCount], 422);
                } elseif (empty($currentCard['back'])) {
                    return $this->json(['message' => 'Missing back for Card '.$cardCount], 422);
                }
            }
        }

        $cardCount--;

        return $this->json([
            'cards' => $cards,
            'cardCount' => $cardCount
        ]);
    }

    #[Route('api/import/csv', name: 'api_import_csv', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importCsv(Request $request): JsonResponse
    {
        $cards = [];
        $cardCount = 0;
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType != 'text/plain' && $fileType != 'text/csv') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 1048576) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        $csv = Reader::createFromPath($pathName, 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        if (count($headers) != 2 || (!in_array('front', $headers) || !in_array('back', $headers))) {
            return $this->json(['message' => 'Invalid CSV headers'], 422);
        }

        $rows = $csv->getRecords();

        foreach ($rows as $row) {
            $cardCount++;
            $front = trim($row['front']);
            $back = trim($row['back']);

            if (empty($front)) {
                return $this->json(['message' => 'Missing front for Card '.$cardCount], 422);
            } elseif (empty($back)) {
                return $this->json(['message' => 'Missing back for Card '.$cardCount], 422);
            }

            $cards[] = ['front' => $front, 'back' => $back];
        }

        return $this->json([
            'cards' => $cards,
            'cardCount' => $cardCount
        ]);
    }

    #[Route('api/import/md', name: 'api_import_md', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importMd(Request $request): JsonResponse
    {
        $cards = [];
        $cardCount = 0;
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType != 'text/plain' && $fileType != 'text/markdown') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 1048576) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        if ($file = fopen($pathName, "r")) {
            $front = '';
            $back = '';

            while(!feof($file)) {
                $line = trim(fgets($file));

                if (str_starts_with($line, '## ')) { // New card
                    if (!empty($front) && empty($back)) { // Missing back side for previous card
                        fclose($file);
                        return $this->json(['message' => 'Card '.$cardCount.' incomplete: missing back side or separator'], 422);
                    }

                    $cardCount++;
                    $front = trim(fgets($file));
                } elseif ($line == '---') { // Separator
                    $back = trim(fgets($file));

                    if (empty($front)) { // Missing front side for current card
                        fclose($file);
                        return $this->json(['message' => 'Card '.$cardCount.' incomplete: missing front side or header'], 422);
                    } elseif (empty($back)) { // Missing back side for current card
                        fclose($file);
                        return $this->json(['message' => 'Card '.$cardCount.' incomplete: missing back side or separator'], 422);
                    }

                    $cards[] = ['front' => $front, 'back' => $back];
                    $front = '';
                    $back = '';
                }
            }

            fclose($file);

            if (!empty($front) && empty($back)) { // Missing back side for last card
                return $this->json(['message' => 'Card '.$cardCount.' incomplete: missing back side or separator'], 422);
            }
        }

        if ($cardCount == 0) {
            return $this->json(['message' => 'No cards found'], 422);
        }

        return $this->json([
            'cards' => $cards,
            'cardCount' => $cardCount
        ]);
    }

    #[Route('api/import/json', name: 'api_import_json', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importJson(Request $request): JsonResponse
    {
        $cardCount = 0;
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType != 'text/plain' && $fileType != 'application/json' && $fileType != 'text/json') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 1048576) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        $jsonContent = file_get_contents($pathName);
        $data = json_decode($jsonContent);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $this->json(['message' => 'Invalid JSON format'], 422);
        } elseif (gettype($data) != 'array') {
            return $this->json(['message' => 'Invalid JSON structure'], 422);
        }

        foreach ($data as $item) {
            $cardCount++;

            if (gettype($item) != 'object') {
                return $this->json(['message' => 'Invalid structure for Card '.$cardCount], 422);
            } elseif (!property_exists($item, 'front')) {
                return $this->json(['message' => 'Missing front for Card '.$cardCount], 422);
            } elseif (!property_exists($item, 'back')) {
                return $this->json(['message' => 'Missing back for Card '.$cardCount], 422);
            }
        }

        return $this->json([
            'cards' => $data,
            'cardCount' => count($data)
        ]);
    }
}
