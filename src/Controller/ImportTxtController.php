<?php

namespace App\Controller;

use App\Service\ImportResponse;
use App\Service\TxtImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportTxtController extends AbstractController
{
    public function __construct(
        private TxtImportService $txtImportService
    ) {}

    #[Route('api/import/text', name: 'api_import_text', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importText(Request $request): JsonResponse
    {
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

        $importResult = $this->txtImportService->parse($file);

        if ($importResult->hasError()) {
            return $this->json(['message' => $importResult->errorMessage], 422);
        }
        
        $importResponse = new ImportResponse($importResult->cards);
        return $this->json($importResponse);
    }
}
