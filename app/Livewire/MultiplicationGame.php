<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MultiplicationGame extends Component
{
    public int $num1 = 1;
    public int $num2 = 1;
    public $userAnswer = '';
    public bool $isCorrect = false;
    public bool $showResult = false;
    public int $score = 0;
    public int $streak = 0;
    public int $bestStreak = 0;
    public int $totalAnswered = 0;
    public ?float $lastTime = null;
    public ?float $bestTime = null;
    public bool $newRecord = false;

    public function mount(): void
    {
        $this->generateProblem();
    }

    public function generateProblem(): void
    {
        $this->num1 = random_int(1, 12);
        $this->num2 = random_int(1, 12);
        $this->userAnswer = '';
        $this->isCorrect = false;
        $this->showResult = false;
        $this->newRecord = false;
        $this->dispatch('start-timer');
    }

    public function checkAnswer($elapsedTime = null): void
    {
        $answer = trim((string) $this->userAnswer);

        if ($answer === '') {
            return;
        }

        $correctAnswer = $this->num1 * $this->num2;
        $this->isCorrect = ((int) $answer) === $correctAnswer;
        $this->showResult = true;
        $this->totalAnswered++;

        if ($this->isCorrect) {
            $this->score++;
            $this->streak++;
            if ($this->streak > $this->bestStreak) {
                $this->bestStreak = $this->streak;
            }

            // Track time only for correct answers
            if ($elapsedTime !== null) {
                $this->lastTime = round((float) $elapsedTime, 2);
                if ($this->bestTime === null || $this->lastTime < $this->bestTime) {
                    $this->bestTime = $this->lastTime;
                    $this->newRecord = true;
                }
            }

            $this->dispatch('correct-answer');
        } else {
            $this->streak = 0;
            $this->dispatch('wrong-answer');
        }
    }

    public function nextProblem(): void
    {
        $this->generateProblem();
    }

    public function render()
    {
        return view('livewire.multiplication-game');
    }
}
