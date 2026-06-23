<?php

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature   = 'articles:publish-scheduled';
    protected $description = 'Publish all articles whose scheduled_at time has passed.';

    public function handle(): void
    {
        $articles = Article::scheduledForPublishing()->get();

        foreach ($articles as $article) {
            $article->update([
                'status'       => ArticleStatus::PUBLISHED,
                'published_at' => $article->scheduled_at,
            ]);

            ActivityLog::record(
                'article.auto_published',
                "Article '{$article->title}' auto-published via scheduler",
                $article
            );
        }

        $this->info("Published {$articles->count()} scheduled articles.");
    }
}
