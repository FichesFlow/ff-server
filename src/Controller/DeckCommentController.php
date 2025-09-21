<?php

namespace App\Controller;

use App\Entity\DeckComment;
use App\Entity\User;
use App\Repository\DeckCommentRepository;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class DeckCommentController extends AbstractController
{
    public function __construct(
        private readonly DeckRepository         $deckRepository,
        private readonly DeckCommentRepository  $deckCommentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface    $serializer
    )
    {
    }

    #[Route('/api/decks/{id}/comments', name: 'api_deck_comments_list', methods: ['GET'])]
    public function listComments(string $id): JsonResponse
    {
        $deck = $this->deckRepository->find($id);
        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        $comments = $this->deckCommentRepository->findBy(
            ['deck' => $deck],
            ['created_at' => 'DESC']
        );

        $normalizedComments = $this->serializer->normalize($comments, null, [
            'groups' => ['comment:read', 'uuid']
        ]);

        return $this->json([
            'comments' => $normalizedComments
        ]);
    }

    #[Route('/api/decks/{id}/comments', name: 'api_deck_comments_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createComment(string $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $deck = $this->deckRepository->find($id);
        if (!$deck) {
            return $this->json(['error' => 'Deck not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['body']) || empty(trim($data['body']))) {
            return $this->json(['error' => 'Comment body is required'], 400);
        }

        $comment = new DeckComment();
        $comment->setDeck($deck);
        $comment->setCommenter($user);
        $comment->setBody(trim($data['body']));

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $normalizedComment = $this->serializer->normalize($comment, null, [
            'groups' => ['comment:read']
        ]);

        return $this->json($normalizedComment, 201);
    }

    #[Route('/api/comments/{commentId}', name: 'api_comment_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateComment(string $commentId, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $comment = $this->deckCommentRepository->find($commentId);
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }

        if ($comment->getCommenter() !== $user) {
            return $this->json(['error' => 'You can only edit your own comments'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['body']) || empty(trim($data['body']))) {
            return $this->json(['error' => 'Comment body is required'], 400);
        }

        $comment->setBody(trim($data['body']));
        $this->entityManager->flush();

        $normalizedComment = $this->serializer->normalize($comment, null, [
            'groups' => ['comment:read']
        ]);

        return $this->json($normalizedComment);
    }

    #[Route('/api/comments/{commentId}', name: 'api_comment_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteComment(string $commentId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $comment = $this->deckCommentRepository->find($commentId);
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }

        if ($comment->getCommenter() !== $user) {
            return $this->json(['error' => 'You can only delete your own comments'], 403);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(['message' => 'Comment deleted successfully']);
    }
}
