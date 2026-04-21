<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Change default URL to match your domain
        $url = config('app.url');

        $this->info("Generating sitemap for: {$url}");

        SitemapGenerator::create($url)
            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');

        return Command::SUCCESS;
    }
}
