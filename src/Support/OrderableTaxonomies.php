<?php

namespace KeyAgency\TaxonomyTermsOrder\Support;

use Illuminate\Support\Collection;
use Statamic\Facades\Taxonomy;

class OrderableTaxonomies
{
    /**
     * Determine if terms of the given taxonomy handle may be reordered.
     */
    public static function includes(string $handle): bool
    {
        $configured = config('statamic.taxonomy-terms-order.taxonomies', []);

        if (in_array('*', $configured)) {
            return Taxonomy::findByHandle($handle) !== null;
        }

        return in_array($handle, $configured);
    }

    /**
     * All taxonomies that are configured as orderable.
     */
    public static function all(): Collection
    {
        return Taxonomy::all()
            ->filter(fn ($taxonomy) => static::includes($taxonomy->handle()))
            ->values();
    }
}
