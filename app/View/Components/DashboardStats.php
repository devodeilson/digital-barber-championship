<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DashboardStats extends Component
{
    public $virtualCoins;
    public $activeChampionships;
    public $totalContents;

    public function __construct($virtualCoins, $activeChampionships, $totalContents)
    {
        $this->virtualCoins = $virtualCoins;
        $this->activeChampionships = $activeChampionships;
        $this->totalContents = $totalContents;
    }

    public function render()
    {
        return view('components.dashboard-stats');
    }
} 