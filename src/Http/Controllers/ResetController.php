<?php

namespace KeyAgency\TaxonomyTermsOrder\Http\Controllers;

use KeyAgency\TaxonomyTermsOrder\Http\Controllers\Concerns\AuthorizesTermReordering;
use Statamic\Http\Controllers\CP\CpController;

class ResetController extends CpController
{
    use AuthorizesTermReordering;

    public function __invoke($taxonomy)
    {
        $this->authorizeReorder($taxonomy);

        /**
         * The sort field must be restored first: while it's still `order`,
         * the AssignOrderToNewTerm listener would re-add an order value to
         * every term the moment it's saved without one.
         */
        if ($taxonomy->sortField() === 'order') {
            $taxonomy->setSortField(null)->setSortDirection(null)->save();
        }

        $taxonomy->queryTerms()
            ->where('site', $taxonomy->sites()->first())
            ->get()
            ->each(function ($term) {
                $localized = $term->inDefaultLocale();

                if ($localized->has('order')) {
                    $localized->data($localized->data()->forget('order'))->save();
                }
            });

        return [
            'message' => __('taxonomy-terms-order::messages.reset_success'),
        ];
    }
}
