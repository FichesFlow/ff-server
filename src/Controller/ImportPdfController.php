<?php

namespace App\Controller;

use App\Service\ImportResponse;
use App\Service\PdfImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportPdfController extends AbstractController
{
    public function __construct(
        private PdfImportService $pdfImportService
    ) {}

    #[Route('api/import/pdf', name: 'api_import_pdf', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function importPdf(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $pathName = $file->getPathname();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $pathName);
        $fileSize = $file->getSize();
        $content = file_get_contents($pathName);
        finfo_close($finfo);

        if ($fileType != 'application/pdf') {
            return $this->json(['message' => 'Invalid file type'], 422);
        } elseif ($fileSize > 5242880) {
            return $this->json(['message' => 'File size exceeds limit'], 413);
        }
        
        // Security check
        if (preg_match('/\/JavaScript|\/JS|\/AA|\/Launch|\/Action/i', $content)) { // Check for JavaScript/executable content (common PDF exploit vectors)
            return $this->json(['message' => 'File security check failed'], 422);
        } elseif (preg_match('/\/EmbeddedFile|\/FileAttachment/i', $content)) { // Check for embedded files
            return $this->json(['message' => 'File security check failed'], 422);
        }

        try {
            $importResult = $this->pdfImportService->parse($file);

            if ($importResult->hasError()) {
                return $this->json(['message' => $importResult->errorMessage], 422);
            }
        
            $importResponse = new ImportResponse($importResult->cards);
            return $this->json($importResponse);
        } catch (Exception $e) {
            return $this->json(['message' => 'Failed to process PDF: ' . $e->getMessage()], 500);
        }
    }
}