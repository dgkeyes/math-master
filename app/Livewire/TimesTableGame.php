<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TimesTableGame extends Component
{
    public ?int $selectedNumber = null;
    public array $answers = [];
    public array $wrongAnswers = [];
    public bool $isComplete = false;
    public ?float $finalTime = null;
    public bool $hasSubmitted = false;

    public function selectNumber(int $number): void
    {
        $this->selectedNumber = $number;
        $this->answers = array_fill(1, 12, '');
        $this->wrongAnswers = [];
        $this->isComplete = false;
        $this->finalTime = null;
        $this->hasSubmitted = false;
        $this->dispatch('start-timer');
    }

    public function submit(float $elapsedTime): void
    {
        $this->hasSubmitted = true;
        $this->wrongAnswers = [];

        for ($i = 1; $i <= 12; $i++) {
            $expected = $this->selectedNumber * $i;
            $userAnswer = trim((string) ($this->answers[$i] ?? ''));

            if ($userAnswer === '' || (int) $userAnswer !== $expected) {
                $this->wrongAnswers[] = $i;
            }
        }

        if (empty($this->wrongAnswers)) {
            $this->isComplete = true;
            $this->finalTime = round($elapsedTime, 1);
            $this->dispatch('stop-timer');
        }
    }

    public function tryAgain(): void
    {
        $this->selectNumber($this->selectedNumber);
    }

    public function pickNew(): void
    {
        $this->selectedNumber = null;
        $this->answers = [];
        $this->wrongAnswers = [];
        $this->isComplete = false;
        $this->finalTime = null;
        $this->hasSubmitted = false;
    }

    public function render()
    {
        return view('livewire.times-table-game');
    }
}
