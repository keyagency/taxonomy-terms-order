<?php

namespace KeyAgency\TaxonomyTermsOrder\Http\Controllers;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\Concerns\AuthorizesTermReordering;
use Statamic\Http\Controllers\CP\CpController;

class ShowController extends CpController
{
    use AuthorizesTermReordering;

    public function __invoke($taxonomy)
    {
        $this->authorizeReorder($taxonomy);

        return Inertia::render('taxonomy-terms-order::OrderTerms', [
            'taxonomy' => $taxonomy->handle(),
            'taxonomyTitle' => __($taxonomy->title()),
            'title' => __('taxonomy-terms-order::messages.nav_title'),
            'icon' => File::get(__DIR__.'/../../../resources/svg/nav-icon.svg'),
            'instructions' => __('taxonomy-terms-order::messages.instructions'),
            'goToTermsLabel' => __('taxonomy-terms-order::messages.go_to_terms'),
            'resetLabel' => __('taxonomy-terms-order::messages.reset'),
            'resetConfirmation' => __('taxonomy-terms-order::messages.reset_confirmation'),
            'terms' => $this->terms($taxonomy),
            'submitUrl' => cp_route('taxonomy-terms-order.reorder', $taxonomy->handle()),
            'resetUrl' => cp_route('taxonomy-terms-order.reset', $taxonomy->handle()),
            'listingUrl' => cp_route('taxonomies.show', $taxonomy->handle()),
        ]);
    }

    /**
     * Terms are queried in the taxonomy's default site since the order
     * is stored once per term (shared across all sites).
     */
    protected function terms($taxonomy): array
    {
        return $taxonomy->queryTerms()
            ->where('site', $taxonomy->sites()->first())
            ->get()
            ->map(fn ($term) => [
                'id' => $term->id(),
                'title' => $term->title(),
                'slug' => $term->slug(),
                'edit_url' => $term->editUrl(),
                'order' => $term->get('order'),
            ])
            ->sortBy([
                fn ($a, $b) => ($a['order'] ?? PHP_INT_MAX) <=> ($b['order'] ?? PHP_INT_MAX),
                fn ($a, $b) => strcasecmp($a['title'], $b['title']),
            ])
            ->values()
            ->all();
    }
}
