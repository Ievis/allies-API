<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Consultation;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Modification;
use App\Models\PaymentPlan;
use App\Models\Problem;
use App\Models\Review;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\TeacherDescription;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';
    protected string $apiNamespace = 'App\Http\Controllers\Api';
    private array $crudInstances = [
        'user' => User::class,
        'course' => Course::class,
        'lesson' => Lesson::class,
        'problem' => Problem::class,
        'modification' => Modification::class,
        'subject' => Subject::class,
        'category' => Category::class,
        'tag' => Tag::class,
        'section' => Section::class,
        'payment_plan' => PaymentPlan::class,
        'teacher_description' => TeacherDescription::class,
        'review' => Review::class,
        'consultation' => Consultation::class,
    ];

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();
        });


    }

    protected function mapApiRoutes(): void
    {
        Route::group([
            'middleware' => ['api', 'api.version:v1'],
            'namespace' => $this->apiNamespace . '\V1',
            'prefix' => 'api/v1/auth',
        ], function () {
            require base_path('routes/Api/V1/auth.php');
        });

        Route::group([
            'middleware' => ['api', 'api.version:v1'],
            'namespace' => $this->apiNamespace . '\V1',
            'prefix' => 'api/v1',
        ], function () {
            require base_path('routes/Api/V1/payment.php');
        });

        foreach ($this->crudInstances as $crudInstanceRouteName => $crudInstancesModelClassName) {
            Route::group([
                'middleware' => ['api', 'api.version:v1'],
                'namespace' => $this->apiNamespace . '\V1',
                'prefix' => 'api/v1'
            ], function () use ($crudInstanceRouteName) {
                require base_path('routes/Api/V1/Rest/' . $crudInstanceRouteName . '.php');
            });
        }

        Route::group([
            'middleware' => ['web'],
            'namespace' => $this->apiNamespace . '\V1',
        ], function () {
            require base_path('routes/web.php');
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
