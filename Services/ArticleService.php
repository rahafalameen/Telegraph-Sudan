<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ArticleService
{
    public function saveDraft(array $data, ?Article $article = null): Article
    {
        $data['status'] = $data['status'] ?? ArticleStatus::DRAFT;
        return $this->saveArticle($data, $article);
    }

    public function submitForReview(array $data, ?Article $article = null): Article
    {
        $data['status'] = ArticleStatus::PENDING;
        $article = $this->saveArticle($data, $article);
        ActivityLog::record('article.submitted', "Article '{$article->title}' submitted for review", $article);
        return $article;
    }

public function approve(Article $article): Article
{
    $article->update([
        'status'       => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);
    ActivityLog::record('article.published', "Article '{$article->title}' approved and published", $article);
    return $article;
}   

    public function reject(Article $article, string $reason): Article
    {
        $article->update(['status' => ArticleStatus::REJECTED, 'rejection_reason' => $reason]);
        ActivityLog::record('article.rejected', "Article '{$article->title}' rejected", $article);
        return $article;
    }

    public function publish(Article $article): Article
    {
        $article->update(['status' => ArticleStatus::PUBLISHED, 'published_at' => now()]);
        ActivityLog::record('article.published', "Article '{$article->title}' published", $article);
        return $article;
    }

    public function schedule(Article $article, $scheduledAt): Article
    {
        $article->update(['status' => ArticleStatus::SCHEDULED, 'scheduled_at' => $scheduledAt]);
        ActivityLog::record('article.scheduled', "Article '{$article->title}' scheduled", $article);
        return $article;
    }

    public function uploadFeaturedImage(UploadedFile $file, ?string $oldImage = null): string
    {
        if ($oldImage && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }
        $path = $file->store('articles', 'public');
        return $path;
    }

    private function saveArticle(array $data, ?Article $article): Article
    {
        if (isset($data['featured_image_file'])) {
            $data['featured_image'] = $this->uploadFeaturedImage(
                $data['featured_image_file'],
                $article?->featured_image
            );
            unset($data['featured_image_file']);
        }

        $data['author_id'] ??= auth()->id();

        if ($article) {
            $article->update($data);
            return $article->fresh();
        }

        return Article::create($data);
    }
}
