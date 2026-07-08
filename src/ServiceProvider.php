<?php

namespace KeyAgency\TaxonomyTermsOrder;

use Illuminate\Support\Facades\File;
use KeyAgency\TaxonomyTermsOrder\Listeners\AssignOrderToNewTerm;
use KeyAgency\TaxonomyTermsOrder\Support\OrderableTaxonomies;
use Statamic\Events\TermSaving;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $listen = [
        TermSaving::class => [
            AssignOrderToNewTerm::class,
        ],
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/taxonomy-terms-order.php',
            'taxonomy-terms-order'
        );
    }

    public function bootAddon(): void
    {
        $this->registerPermissions();
        $this->registerNav();
        $this->registerPublishables();
    }

    protected function registerPermissions(): void
    {
        Permission::group('taxonomy_terms_order', 'Taxonomy Terms Order', function () {
            Permission::register('reorder {taxonomy} terms', function ($permission) {
                $permission
                    ->label(__('taxonomy-terms-order::messages.permission_label'))
                    ->replacements('taxonomy', function () {
                        return OrderableTaxonomies::all()->map(fn ($taxonomy) => [
                            'value' => $taxonomy->handle(),
                            'label' => __($taxonomy->title()),
                        ]);
                    });
            });
        });
    }

    protected function registerNav(): void
    {
        Nav::extend(function ($nav) {
            $user = User::current();

            $taxonomies = OrderableTaxonomies::all()->filter(
                fn ($taxonomy) => $user->isSuper() || $user->hasPermission("reorder {$taxonomy->handle()} terms")
            );

            if ($taxonomies->isEmpty()) {
                return;
            }

            $nav->tools(__('taxonomy-terms-order::messages.nav_title'))
                ->icon(File::get(__DIR__.'/../resources/svg/nav-icon.svg'))
                ->route('taxonomy-terms-order.index')
                ->children(function () use ($nav, $taxonomies) {
                    return $taxonomies->map(
                        fn ($taxonomy) => $nav->item(__($taxonomy->title()))
                            ->url(cp_route('taxonomy-terms-order.show', $taxonomy->handle()))
                    )->all();
                });
        });
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // php artisan vendor:publish --tag=taxonomy-terms-order-config
        $this->publishes([
            __DIR__.'/../config/taxonomy-terms-order.php' => config_path('taxonomy-terms-order.php'),
        ], 'taxonomy-terms-order-config');
    }
}
