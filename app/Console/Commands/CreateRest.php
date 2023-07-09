<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateRest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-rest {model_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create rest model, migration, model filter, request, policy, resource, collection resource, controller';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model_name = $this->argument('model_name');
        Artisan::call('make:policy', [
            'name' => $model_name . 'Policy',
            '--model' => $model_name,
        ]);
        Artisan::call('make:model ' . $model_name . ' -m');
        Artisan::call('model:filter ' . $model_name);
        Artisan::call('make:request ' . 'Post' . $model_name . 'Request');

        Artisan::call('make:resource ' . 'V1/' . $model_name . '/' . $model_name . 'CollectionResource');
        Artisan::call('make:resource ' . 'V1/' . $model_name . '/' . $model_name . 'Resource');
        Artisan::call('make:controller ' . 'Api/V1/' . $model_name . 'Controller');
    }
}
