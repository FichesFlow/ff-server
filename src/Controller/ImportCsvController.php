<?php

namespace App\Controller;

use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportCsvController extends AbstractController
{
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

        if (($fileType !== 'text/plain' && $fileType !== 'text/csv') || $fileSize > 1048576) {
            return $this->json(['message' => 'Invalid file type or size'], 422);
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
}