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
        $cardCount = 0;
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType !== 'text/plain') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 1048576) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        if ($file = fopen($pathName, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                $firstWord = strtok($line, " ");

                switch ($firstWord) {
                    case 'Carte': // New card
                        $cardCount++;
                        break;
                    case 'front:': // Front side
                        $front = trim(substr($line, 6, strlen($line)));
                        break;
                    case 'back:': // Back side
                        $back = trim(substr($line, 5, strlen($line)));
                        $cards[] = ['front' => $front, 'back' => $back];
                        break;
                    default: // Skip other lines
                }
            }

            fclose($file);
        }

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

        if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
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

        if ($fileType !== 'text/plain' && $fileType !== 'text/markdown') {
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
                        return $this->json(['message' => 'Incomplete card: missing back side or separator'], 422);
                    }

                    $cardCount++;
                    $front = trim(fgets($file));
                } elseif ($line == '---') { // Separator
                    $back = trim(fgets($file));

                    if (empty($front)) { // Missing front side for current card
                        fclose($file);
                        return $this->json(['message' => 'Incomplete card: missing front side or header'], 422);
                    } elseif (empty($back)) { // Missing back side for current card
                        fclose($file);
                        return $this->json(['message' => 'Incomplete card: missing back side or separator'], 422);
                    }

                    $cards[] = ['front' => $front, 'back' => $back];
                    $front = '';
                    $back = '';
                }
            }

            fclose($file);

            if (!empty($front) && empty($back)) { // Missing back side for last card
                return $this->json(['message' => 'Incomplete card: missing back side or separator'], 422);
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
}
