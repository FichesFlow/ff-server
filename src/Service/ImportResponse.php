<?php

namespace App\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\ImportCsvController;
use App\Controller\ImportDocxController;
use App\Controller\ImportJsonController;
use App\Controller\ImportMarkdownController;
use App\Controller\ImportTxtController;
use App\Controller\ImportXlsxController;
use App\Dto\CardDto;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/import/txt',
            controller: ImportTxtController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_txt'
        ),
        new Post(
            uriTemplate: '/import/csv',
            controller: ImportCsvController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_csv'
        ),
        new Post(
            uriTemplate: '/import/md',
            controller: ImportMarkdownController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_md'
        ),
        new Post(
            uriTemplate: '/import/json',
            controller: ImportJsonController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_json'
        ),
        new Post(
            uriTemplate: '/import/docx',
            controller: ImportDocxController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_docx'
        ),
        new Post(
            uriTemplate: '/import/xlsx',
            controller: ImportXlsxController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: ImportResponse::class,
            name: 'import_xlsx'
        )
    ],
    normalizationContext: ['groups' => ['import:read']]
)]
final class ImportResponse {

    /**
     * @var CardDto[]
     */
    #[Groups(['import:read'])]
    public array $cards;

    #[Groups(['import:read'])]
    public int $cardCount;

    /**
     * @param CardDto[]
     */
    public function __construct(array $cards)
    {
        $this->cards = $cards;
        $this->cardCount = count($cards);
    }
}