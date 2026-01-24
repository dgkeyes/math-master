<div
    x-data="{
        startTime: null,
        elapsedTime: 0,
        timerInterval: null,
        timerRunning: false,
        startTimer() {
            this.startTime = Date.now();
            this.elapsedTime = 0;
            this.timerRunning = true;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                this.elapsedTime = (Date.now() - this.startTime) / 1000;
            }, 100);
        },
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            this.timerRunning = false;
        },
        submitAnswers() {
            $wire.submit(this.elapsedTime);
        },
        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            const tenths = Math.floor((seconds % 1) * 10);
            if (mins > 0) {
                return mins + ':' + String(secs).padStart(2, '0') + '.' + tenths;
            }
            return secs + '.' + tenths + 's';
        }
    }"
    x-on:start-timer.window="startTimer()"
    x-on:stop-timer.window="stopTimer()"
    class="min-h-[80vh] flex flex-col items-center justify-center py-8"
>
    {{-- Number Selection Screen --}}
    @if($selectedNumber === null)
        <div class="text-center w-full max-w-lg">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-8 sm:p-12">
                <div class="text-5xl mb-4">📋</div>
                <h2 class="text-3xl font-bold text-zinc-800 dark:text-white mb-2">Times Tables</h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">Pick a number to practice</p>

                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    @for($i = 1; $i <= 12; $i++)
                        <button
                            wire:click="selectNumber({{ $i }})"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold text-xl py-4 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-105"
                        >
                            {{ $i }}
                        </button>
                    @endfor
                </div>
            </div>
        </div>

    {{-- Game Complete Screen --}}
    @elseif($isComplete)
        <div class="text-center w-full max-w-lg">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-8 sm:p-12">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">All Correct!</h2>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-2">You completed the {{ $selectedNumber }} times table</p>
                <p class="text-4xl font-bold text-zinc-800 dark:text-white mb-8">
                    {{ $finalTime }}s
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        wire:click="tryAgain"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200"
                    >
                        Try Again
                    </button>
                    <button
                        wire:click="pickNew"
                        class="bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-800 dark:text-white font-bold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200"
                    >
                        Pick New Number
                    </button>
                </div>
            </div>
        </div>

    {{-- Game Board --}}
    @else
        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-6 sm:p-8">
                {{-- Header with timer --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-800 dark:text-white">The {{ $selectedNumber }} Times Table</h2>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Time</div>
                        <div class="text-2xl font-mono font-bold text-blue-600 dark:text-blue-400" x-text="formatTime(elapsedTime)">0.0s</div>
                    </div>
                </div>

                {{-- Error message --}}
                @if($hasSubmitted && !empty($wrongAnswers))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 mb-4">
                        <p class="text-red-600 dark:text-red-400 font-medium text-sm">
                            {{ count($wrongAnswers) }} {{ count($wrongAnswers) === 1 ? 'answer is' : 'answers are' }} wrong. Fix the highlighted ones and try again!
                        </p>
                    </div>
                @endif

                {{-- Problems list --}}
                <div class="space-y-3">
                    @for($i = 1; $i <= 12; $i++)
                        <div class="flex items-center gap-3 {{ in_array($i, $wrongAnswers) ? 'bg-red-50 dark:bg-red-900/20 rounded-xl p-2 -mx-2 border border-red-200 dark:border-red-800' : '' }}">
                            <span class="text-lg font-semibold text-zinc-700 dark:text-zinc-300 w-24 text-right">
                                {{ $selectedNumber }} &times; {{ $i }} =
                            </span>
                            <input
                                type="number"
                                wire:model="answers.{{ $i }}"
                                class="w-24 px-3 py-2 text-lg font-semibold text-center border-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300
                                    {{ in_array($i, $wrongAnswers)
                                        ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                        : 'border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-white' }}"
                                @if($isComplete) disabled @endif
                            >
                            @if(in_array($i, $wrongAnswers))
                                <span class="text-red-500 text-lg">✗</span>
                            @endif
                        </div>
                    @endfor
                </div>

                {{-- Submit button --}}
                <div class="mt-6">
                    <button
                        x-on:click="submitAnswers()"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold text-lg py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200"
                    >
                        Submit Answers
                    </button>
                </div>

                {{-- Back button --}}
                <div class="mt-3 text-center">
                    <button
                        wire:click="pickNew"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 text-sm underline"
                    >
                        ← Pick a different number
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
