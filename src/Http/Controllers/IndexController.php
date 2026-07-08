<?php

namespace KeyAgency\TaxonomyTermsOrder\Http\Controllers;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use KeyAgency\TaxonomyTermsOrder\Support\OrderableTaxonomies;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class IndexController extends CpController
{
    public function __invoke()
    {
        $user = User::current();

        $taxonomies = OrderableTaxonomies::all()->filter(
            fn ($taxonomy) => $user->isSuper() || $user->hasPermission("reorder {$taxonomy->handle()} terms")
        );

        abort_if($taxonomies->isEmpty(), 403);

        return Inertia::render('taxonomy-terms-order::Index', [
            'title' => __('taxonomy-terms-order::messages.nav_title'),
            'icon' => File::get(__DIR__.'/../../../resources/svg/nav-icon.svg'),
            'instructions' => __('taxonomy-terms-order::messages.index_instructions'),
            'taxonomies' => $taxonomies->map(fn ($taxonomy) => [
                'handle' => $taxonomy->handle(),
                'title' => __($taxonomy->title()),
                'terms_count' => $taxonomy->queryTerms()->where('site', $taxonomy->sites()->first())->count(),
                'url' => cp_route('taxonomy-terms-order.show', $taxonomy->handle()),
            ])->values()->all(),
        ]);
    }
}
