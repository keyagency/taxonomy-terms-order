<?php

namespace KeyAgency\TaxonomyTermsOrder\Http\Controllers\Concerns;

use KeyAgency\TaxonomyTermsOrder\Support\OrderableTaxonomies;
use Statamic\Facades\User;
use Statamic\Taxonomies\Taxonomy;

trait AuthorizesTermReordering
{
    /**
     * Abort when the taxonomy isn't orderable or the user lacks the reorder permission.
     */
    protected function authorizeReorder(Taxonomy $taxonomy): void
    {
        abort_unless(OrderableTaxonomies::includes($taxonomy->handle()), 404);

        $user = User::current();

        abort_unless(
            $user->isSuper() || $user->hasPermission("reorder {$taxonomy->handle()} terms"),
            403
        );
    }
}
