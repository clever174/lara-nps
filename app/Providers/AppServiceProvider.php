<?php

namespace App\Providers;

use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Providers\ProxyApiProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiProviderFactory::class);

        $this->app->bind(AiProvider::class, fn ($app) => $app->make(AiProviderFactory::class)->make());

        $this->app->singleton(ProxyApiProvider::class, fn () => new ProxyApiProvider(
            apiKey: config('ai.providers.proxyapi.api_key'),
            defaultModel: config('ai.providers.proxyapi.model'),
            defaultAudioModel: config('ai.providers.proxyapi.audio_model'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
