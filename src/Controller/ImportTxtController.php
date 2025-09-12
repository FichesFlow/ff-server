<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportTxtController extends AbstractController
{
    #[Route('api/import/text', name: 'api_import_text', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importText(Request $request): JsonResponse
    {
        $cards = [];
        $cardCount = 0;
        $front = '';
        $back = '';
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType !== 'text/plain' || $fileSize > 1048576) {
            return $this->json(['message' => 'Invalid file type or size'], 422);
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
                        $front = trim(substr($line, 7, strlen($line)));
                        break;
                    case 'back:': // Back side
                        $back = trim(substr($line, 6, strlen($line)));

                        $cards[] = ['front' => $front, 'back' => $back];
                        $front = '';
                        $back = '';
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
}