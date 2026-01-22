<div
    x-data="{
        flashClass: '',
        startTime: Date.now(),
        elapsedTime: 0,
        timerInterval: null,
        startTimer() {
            this.startTime = Date.now();
            this.elapsedTime = 0;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                this.elapsedTime = (Date.now() - this.startTime) / 1000;
            }, 100);
            // Focus the input after a brief delay to ensure DOM is ready
            setTimeout(() => {
                const input = document.getElementById('answer-input');
                if (input) input.focus();
            }, 50);
        },
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            return this.elapsedTime;
        },
        submitAnswer() {
            const time = this.stopTimer();
            $wire.checkAnswer(time);
        },
        showConfetti() {
            if (typeof confetti !== 'undefined') {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
            this.flashClass = 'flash-correct';
            setTimeout(() => this.flashClass = '', 1000);
            const audio = document.getElementById('coin-sound');
            if (audio) {
                audio.currentTime = 0;
                audio.play();
            }
        },
        showSad() {
            this.flashClass = 'flash-wrong';
            setTimeout(() => this.flashClass = '', 1000);
            const audio = document.getElementById('sad-trombone');
            if (audio) {
                audio.currentTime = 0;
                audio.play();
            }
        }
    }"
    x-init="startTimer()"
    x-on:start-timer.window="startTimer()"
    x-on:correct-answer.window="showConfetti()"
    x-on:wrong-answer.window="showSad()"
    :class="flashClass"
    class="min-h-[80vh] flex flex-col items-center justify-center transition-colors duration-300"
>
    <!-- Audio elements -->
    <audio id="sad-trombone" src="/sounds/sad-trombone.mp3" preload="auto"></audio>
    <audio id="coin-sound" src="/sounds/coin.mp3" preload="auto"></audio>

    <!-- Confetti script -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <!-- Score Display -->
    <div class="mb-8 flex flex-wrap justify-center gap-4 text-center">
        <div class="bg-green-100 dark:bg-green-900/30 rounded-xl px-6 py-3">
            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Score</div>
            <div class="text-3xl font-bold text-green-700 dark:text-green-300">{{ $score }}</div>
        </div>
        <div class="bg-orange-100 dark:bg-orange-900/30 rounded-xl px-6 py-3">
            <div class="text-sm text-orange-600 dark:text-orange-400 font-medium">Streak</div>
            <div class="text-3xl font-bold text-orange-700 dark:text-orange-300">{{ $streak }}</div>
        </div>
        <div class="bg-purple-100 dark:bg-purple-900/30 rounded-xl px-6 py-3">
            <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Best Streak</div>
            <div class="text-3xl font-bold text-purple-700 dark:text-purple-300">{{ $bestStreak }}</div>
        </div>
        @if($bestTime)
        <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-xl px-6 py-3">
            <div class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Fastest Time</div>
            <div class="text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ number_format($bestTime, 1) }}s</div>
        </div>
        @endif
    </div>

    <!-- Main Game Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-8 sm:p-12 w-full max-w-lg">
        @if(!$showResult)
            <!-- Timer Display -->
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-2 bg-zinc-100 dark:bg-zinc-800 rounded-full px-4 py-2">
                    <svg class="w-5 h-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xl font-mono font-bold text-zinc-700 dark:text-zinc-300" x-text="elapsedTime.toFixed(1) + 's'"></span>
                </div>
                @if($bestTime)
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Beat {{ number_format($bestTime, 1) }}s to set a new record!
                </div>
                @endif
            </div>

            <!-- Question Display -->
            <div class="text-center mb-8">
                <div class="text-6xl sm:text-8xl font-bold text-zinc-800 dark:text-white mb-4">
                    {{ $num1 }} &times; {{ $num2 }}
                </div>
                <div class="text-4xl sm:text-5xl font-bold text-zinc-400">=  ?</div>
            </div>

            <!-- Answer Input -->
            <form x-on:submit.prevent="submitAnswer" class="space-y-6">
                <div class="flex justify-center">
                    <input
                        type="number"
                        id="answer-input"
                        wire:model="userAnswer"
                        autofocus
                        class="w-48 text-center text-4xl font-bold p-4 rounded-xl border-2 border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800 outline-none"
                        placeholder="?"
                    />
                </div>
                <div class="flex justify-center">
                    <button
                        type="submit"
                        class="px-12 py-4 text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors"
                    >
                        Check Answer
                    </button>
                </div>
            </form>
        @else
            <!-- Result Display -->
            <div class="text-center bounce-in">
                @if($isCorrect)
                    @if($newRecord)
                        <div class="text-2xl font-bold text-yellow-500 mb-2 animate-pulse">
                            NEW RECORD!
                        </div>
                    @endif
                    <div class="text-8xl mb-4">
                        @php
                            $celebrations = ['🎉', '🌟', '🎊', '✨', '🏆', '👏', '💪', '🔥'];
                            echo $celebrations[array_rand($celebrations)];
                        @endphp
                    </div>
                    <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">
                        Correct!
                    </div>
                    <div class="text-lg text-zinc-600 dark:text-zinc-400 mb-2 italic">
                        @php
                            $funnyCorrect = [
                                "Your brain is on FIRE today!",
                                "Are you secretly a calculator?",
                                "Math genius alert! 🚨",
                                "You're making this look too easy!",
                                "Did you eat your Wheaties this morning?",
                                "Boom! Nailed it!",
                                "Is it getting smart in here, or is it just you?",
                                "You're basically a human computer!",
                                "Keep going, you're unstoppable!",
                                "Your math teacher would be SO proud!",
                                "That answer was faster than my WiFi!",
                                "Einstein called - he wants his brain back!",
                                "You're on a roll! Don't stop now!",
                                "Math skills: LEGENDARY",
                                "You make multiplication look like a piece of cake!",
                                "Whoa! Did you just do that with your BRAIN?!",
                                "Someone's been practicing! 💪",
                                "You're smarter than a calculator... and cuter too!",
                                "That was smooth like butter!",
                                "High five! ✋ Wait, you can't see me...",
                            ];
                            echo $funnyCorrect[array_rand($funnyCorrect)];
                        @endphp
                    </div>
                    <div class="text-2xl text-zinc-600 dark:text-zinc-400 mb-2">
                        {{ $num1 }} &times; {{ $num2 }} = {{ $num1 * $num2 }}
                    </div>
                    @if($lastTime)
                    <div class="text-xl text-zinc-500 dark:text-zinc-400 mb-6">
                        Time: <span class="font-bold {{ $newRecord ? 'text-yellow-500' : '' }}">{{ number_format($lastTime, 1) }}s</span>
                        @if($bestTime && !$newRecord)
                            <span class="text-sm">(Best: {{ number_format($bestTime, 1) }}s)</span>
                        @endif
                    </div>
                    @endif
                @else
                    <div class="mb-4">
                        <img src="/images/sad-face.svg" alt="Sad face" class="w-32 h-32 mx-auto" />
                    </div>
                    <div class="text-4xl font-bold text-red-600 dark:text-red-400 mb-2">
                        Oops!
                    </div>
                    <div class="text-lg text-zinc-600 dark:text-zinc-400 mb-2 italic">
                        @php
                            $funnyWrong = [
                                "Your calculator is giving you the side-eye right now.",
                                "Close! But close only counts in horseshoes!",
                                "Even superheroes miss sometimes!",
                                "Your brain took a tiny vacation there.",
                                "Plot twist! That wasn't it.",
                                "Nice try! Math is just being difficult today.",
                                "Oopsie daisy! Let's pretend that didn't happen.",
                                "Don't worry, even calculators need batteries!",
                                "That answer went on an adventure... the wrong adventure.",
                                "Quick, blame it on the keyboard!",
                                "Almost! Your brain was SO close!",
                                "Hey, mistakes are just practice for getting it right!",
                                "The math gremlins got you that time!",
                                "No worries! Even Einstein had bad days!",
                                "That's okay - you're still awesome!",
                                "Hmm, let's blame that one on a sneeze.",
                                "Your fingers typed faster than your brain could think!",
                                "Was that answer from opposite day?",
                                "Whoopsie! Your brain did a little hiccup.",
                                "That's what we call a 'creative' answer!",
                            ];
                            echo $funnyWrong[array_rand($funnyWrong)];
                        @endphp
                    </div>
                    <div class="text-xl text-zinc-600 dark:text-zinc-400 mb-2">
                        {{ $num1 }} &times; {{ $num2 }} = <span class="font-bold text-green-600 dark:text-green-400">{{ $num1 * $num2 }}</span>
                    </div>
                    <div class="text-lg text-zinc-500 dark:text-zinc-500 mb-6">
                        You answered: {{ $userAnswer }}
                    </div>
                @endif

                <button
                    wire:click="nextProblem"
                    type="button"
                    class="px-12 py-4 text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors"
                >
                    Next Problem
                </button>
            </div>
        @endif
    </div>

    <!-- Progress Info -->
    @if($totalAnswered > 0)
        <div class="mt-8 text-center text-zinc-500 dark:text-zinc-400">
            {{ $score }} / {{ $totalAnswered }} correct ({{ $totalAnswered > 0 ? round(($score / $totalAnswered) * 100) : 0 }}%)
        </div>
    @endif
</div>
