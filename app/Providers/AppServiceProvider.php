<?php

namespace App\Providers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        View::composer('components.site-footer', function ($view) {
            $items = Cache::remember('footer_recent_posts', 600, function () {
                return Post::orderBy('published_at', 'desc')
                    ->limit(2)
                    ->get(['title', 'slug', 'thumbnail', 'published_at', 'created_at']);
            });

            $recentPosts = $items->map(function (Post $p) {
                return [
                    'title' => $p->title,
                    'url'   => route('news.detail', $p->slug),
                    'date'  => optional($p->published_at ?? $p->created_at)?->format('Y-m-d'),
                    'image' => $p->thumbnail
                        ? asset($p->thumbnail)
                        : asset('assets/img/blog/recent-post-2-1.jpg'),
                ];
            })->toArray();

            // Nama variabel harus sama dengan props di komponen footer: recentPosts
            $view->with('recentPosts', $recentPosts);
        });
    }
}
