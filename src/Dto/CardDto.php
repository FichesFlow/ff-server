<?php


namespace App\Dto;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    normalizationContext: ['groups' => ['import:read']]
)]
final class CardDto
{
    #[Groups(['import:read'])]
    public string $front;


    #[Groups(['import:read'])]
    public string $back;

}