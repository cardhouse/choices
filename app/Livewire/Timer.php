<?php

namespace App\Livewire;

use Livewire\Component;

class Timer extends Component
{
    public $timestamp;

    public $direction = 'up';

    public $isRunning = true;

    public $formattedTime = '00:00';

    public $initialTime = 0;

    public $startTime;

    public function mount($timestamp = null, $direction = 'up')
    {
        $this->direction = $direction;
        $this->timestamp = $timestamp ?? now()->timestamp;
        $this->startTime = now()->timestamp;

        if ($direction === 'down') {
            $this->initialTime = $this->timestamp - now()->timestamp;
        } else {
            $this->initialTime = now()->timestamp - $this->timestamp;
        }
    }

    public function stop()
    {
        $this->isRunning = false;
        $this->dispatch('timerFinished', [
            'timestamp' => now()->timestamp,
            'startTime' => $this->startTime,
            'elapsedTime' => now()->timestamp - $this->startTime,
        ]);
    }

    public function render()
    {
        return view('livewire.timer');
    }
}
