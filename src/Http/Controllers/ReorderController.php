<?php

namespace KeyAgency\TaxonomyTermsOrder\Http\Controllers;

use Illuminate\Http\Request;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\Concerns\AuthorizesTermReordering;
use Statamic\Http\Controllers\CP\CpController;

class ReorderController extends CpController
{
    use AuthorizesTermReordering;

    public function __invoke(Request $request, $taxonomy)
    {
        $this->authorizeReorder($taxonomy);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string',
        ]);

        $terms = $taxonomy->queryTerms()
            ->where('site', $taxonomy->sites()->first())
            ->get()
            ->keyBy(fn ($term) => $term->id());

        /**
         * Only ids belonging to this taxonomy are accepted. Terms missing
         * from the payload (e.g. created in another tab meanwhile) are
         * appended in their current order so every term keeps a position.
         */
        $ordered = collect($request->ids)
            ->filter(fn ($id) => $terms->has($id))
            ->concat(
                $terms->keys()->diff($request->ids)->sortBy(
                    fn ($id) => $terms[$id]->get('order') ?? PHP_INT_MAX
                )
            )
            ->values();

        $ordered->each(function ($id, $index) use ($terms) {
            $term = $terms[$id]->inDefaultLocale();
            $order = $index + 1;

            if ((int) $term->get('order') !== $order) {
                $term->set('order', $order)->save();
            }
        });

        /**
         * Sorting by order becomes the taxonomy default so the native terms
         * listing and frontend tags follow the manual order automatically.
         */
        if ($taxonomy->sortField() !== 'order') {
            $taxonomy->setSortField('order')->setSortDirection('asc')->save();
        }

        return [
            'message' => __('taxonomy-terms-order::messages.reordered'),
        ];
    }
}
