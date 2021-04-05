<?php

namespace Chatter\Core\Menu;

use Illuminate\View\View;
use Chatter\Core\Models\CategoryInterface;

class MenuViewComposer
{
    protected $menuProvider;

    public function __construct(MenuProviderInterface $menuProvider)
    {
        $this->menuProvider = $menuProvider;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('categories', app(CategoryInterface::class)::orderBy('order')->orderBy('name')->get());

        $view->with('menu', $this->menuProvider->get());
    }
}
