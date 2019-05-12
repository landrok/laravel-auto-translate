<?php

namespace Ben182\AutoTranslate\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Ben182\AutoTranslate\AutoTranslate;

class AllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autotrans:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translates all source translations to target translations';

    protected $autoTranslator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AutoTranslate $autoTranslator)
    {
        parent::__construct();
        $this->autoTranslator = $autoTranslator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $targetLanguages = Arr::wrap(config('auto-translate.target_language'));

        $this->line('Found '.count($targetLanguages).' languages to translate');

        $availableTranslations = 0;
        $sourceTranslations = $this->autoTranslator->getSourceTranslations();

        $availableTranslations += count(Arr::dot($sourceTranslations));


        $bar = $this->output->createProgressBar($availableTranslations);
        $bar->start();

        foreach ($targetLanguages as $targetLanguage) {
            $dottedSource = Arr::dot($sourceTranslations);

            $this->autoTranslator->translator->setTarget($targetLanguage);


            foreach ($dottedSource as $key => $value) {
                $dottedSource[$key] = is_string($value) ? $this->autoTranslator->translator->translate($value) : $value;
                $bar->advance();
            }

            $translated = $this->autoTranslator->array_undot($dottedSource);

            $this->autoTranslator->fillLanguageFiles($targetLanguage, $translated);
        }

        $bar->finish();

        $this->info("\nTranslated ".count(Arr::dot($sourceTranslations)).' language keys.');
    }
}
