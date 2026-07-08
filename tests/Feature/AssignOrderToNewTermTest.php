<?php

namespace KeyAgency\TaxonomyTermsOrder\Tests\Feature;

use KeyAgency\TaxonomyTermsOrder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;

class AssignOrderToNewTermTest extends TestCase
{
    #[Test]
    public function new_terms_are_appended_when_the_taxonomy_is_manually_ordered()
    {
        Taxonomy::make('tags')->title('Tags')->setSortField('order')->setSortDirection('asc')->save();

        Term::make()->taxonomy('tags')->slug('alpha')->data(['title' => 'Alpha', 'order' => 1])->save();
        Term::make()->taxonomy('tags')->slug('bravo')->data(['title' => 'Bravo', 'order' => 2])->save();

        Term::make()->taxonomy('tags')->slug('charlie')->data(['title' => 'Charlie'])->save();

        $this->assertSame(3, Term::find('tags::charlie')->get('order'));
    }

    #[Test]
    public function order_is_not_assigned_when_the_taxonomy_is_not_manually_ordered()
    {
        Taxonomy::make('tags')->title('Tags')->save();

        Term::make()->taxonomy('tags')->slug('alpha')->data(['title' => 'Alpha'])->save();

        $this->assertNull(Term::find('tags::alpha')->get('order'));
    }
}
