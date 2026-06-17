<?php

namespace App\Livewire;

use App\Models\Influencer;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public $influencer;

    #[Layout('layouts.app')]
    public function render()
    {
        $this->influencer = $this->influencer();
        return view('livewire.dashboard');
    }

    private function influencer(){
        return Influencer::all();//->paginage(8);
        /*
        return Influencer::hydrate([
            [
                'id' => 'abcd',
                'name' => 'Person 1',
                'avatar' => 'https://picsum.photos/720/1280',
                'stagename' => 'Person 1',
            ],
            [
                'id' => 'abcd',
                'name' => 'Person 2',
                'avatar' => 'https://picsum.photos/720/1280',
                'stagename' => 'Person 2',
            ],
            [
                'id' => 'abcd',
                'name' => 'Person 3',
                'avatar' => 'https://picsum.photos/720/1280',
                'stagename' => 'Person 3',
            ]
            ,[
                'id' => 'abcd',
                'name' => 'Person 4',
                'avatar' => 'https://picsum.photos/720/1280',
                'stagename' => 'Person 4',
            ]
        ]);
        */
    }
}
