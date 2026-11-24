<?php

namespace App\View\Components;

use App\Services\Seo\SeoManager;
use Illuminate\View\Component;

class Seo extends Component
{
    public array $seo;

    public function __construct(SeoManager $manager)
    {
        $this->seo = $manager->data();
    }

    public function render()
    {
        return view('components.seo');
    }
}
