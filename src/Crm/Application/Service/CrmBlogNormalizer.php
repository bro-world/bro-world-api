<?php

declare(strict_types=1);

namespace App\Crm\Application\Service;

use App\Blog\Domain\Entity\Blog;
use App\Blog\Domain\Entity\BlogComment;
use App\Blog\Domain\Entity\BlogPost;

use function array_map;

final class CrmBlogNormalizer
{
    /**
     * @return array<string,mixed>|null
     */
    public function normalizeBlog(?Blog $blog): ?array
    {
        if (!$blog instanceof Blog) {
            return null;
        }

        return [
            'id' => $blog->getId(),
            'title' => $blog->getTitle(),
            'slug' => $blog->getSlug(),
            'type' => $blog->getType()->value,
            'visibility' => $blog->getVisibility()->value,
            'posts' => array_map(
                fn (BlogPost $post): array => $this->normalizePost($post),
                $blog->getPosts()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function normalizePost(BlogPost $post): array
    {
        $commentTreeByParent = $this->buildCommentTreeByParent($post->getComments()->toArray());

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'comments' => $this->normalizeComments($commentTreeByParent, null),
        ];
    }

    /**
     * @param array<int, BlogComment> $comments
     *
     * @return array<string|null, list<BlogComment>>
     */
    private function buildCommentTreeByParent(array $comments): array
    {
        $tree = [];

        foreach ($comments as $comment) {
            $parentId = $comment->getParent()?->getId();
            $tree[$parentId] ??= [];
            $tree[$parentId][] = $comment;
        }

        return $tree;
    }

    /**
     * @param array<string|null, list<BlogComment>> $commentTreeByParent
     *
     * @return array<int,array<string,mixed>>
     */
    private function normalizeComments(array $commentTreeByParent, ?string $parentId): array
    {
        return array_map(function (BlogComment $comment) use ($commentTreeByParent): array {
            return [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'filePath' => $comment->getFilePath(),
                'children' => $this->normalizeComments($commentTreeByParent, $comment->getId()),
            ];
        }, $commentTreeByParent[$parentId] ?? []);
    }
}
