<?php

namespace App\Controller;

use App\Service\ImportResponse;
use App\Service\XlsxImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportXlsxController extends AbstractController
{
    public function __construct(
        private XlsxImportService $xlsxImportService
    ) {}

    #[Route('api/import/xlsx', name: 'api_import_xlsx', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importXlsx(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        finfo_close($finfo);

        if ($fileType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && $fileType != 'application/vnd.ms-excel') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 2097152) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }

        $importResult = $this->xlsxImportService->parse($file);

        if ($importResult->hasError()) {
            return $this->json(['message' => $importResult->errorMessage], 422);
        }
        
        $importResponse = new ImportResponse($importResult->cards);
        return $this->json($importResponse);
    }
}
