<?php

namespace KeyAgency\TaxonomyTermsOrder\Listeners;

use KeyAgency\TaxonomyTermsOrder\Support\OrderableTaxonomies;
use Statamic\Events\TermSaving;

class AssignOrderToNewTerm
{
    /**
     * Append terms without an order value to the end of the list, so new
     * terms don't jump to the top once a taxonomy is manually ordered.
     */
    public function handle(TermSaving $event): void
    {
        $term = $event->term;
        $taxonomy = $term->taxonomy();

        if (! $taxonomy || $taxonomy->sortField() !== 'order') {
            return;
        }

        if (! OrderableTaxonomies::includes($taxonomy->handle())) {
            return;
        }

        $locale = $taxonomy->sites()->first();
        $data = $term->dataForLocale($locale);

        if ($data->has('order')) {
            return;
        }

        $max = $taxonomy->queryTerms()
            ->where('site', $locale)
            ->get()
            ->map(fn ($existing) => (int) $existing->get('order'))
            ->max() ?? 0;

        $term->dataForLocale($locale, $data->put('order', $max + 1)->all());
    }
}
