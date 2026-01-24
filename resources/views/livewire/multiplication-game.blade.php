<div
    x-data="{
        flashClass: '',
        gameStarted: false,
        startTime: Date.now(),
        elapsedTime: 0,
        timerInterval: null,
        startGame() {
            this.gameStarted = true;
            this.startTimer();
        },
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
    x-on:start-timer.window="if (gameStarted) startTimer()"
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

    <!-- Start Screen -->
    <div x-show="!gameStarted" class="text-center">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-8 sm:p-12 w-full max-w-lg">
            <div class="text-6xl mb-6">✖️</div>
            <h2 class="text-3xl font-bold text-zinc-800 dark:text-white mb-4">Pop Quiz</h2>
            <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">Random Multiplication Problems</p>
            <button
                x-on:click="startGame()"
                type="button"
                class="px-12 py-4 text-xl font-bold text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors"
            >
                Start!
            </button>
        </div>
    </div>

    <!-- Score Display -->
    <div x-show="gameStarted" x-cloak class="mb-8 flex flex-wrap justify-center gap-4 text-center">
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
    <div x-show="gameStarted" x-cloak class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl p-8 sm:p-12 w-full max-w-lg">
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
                                "You're a multiplication machine!",
                                "Wow! That was lightning fast!",
                                "Your neurons are doing a happy dance!",
                                "Math wizardry at its finest!",
                                "You crushed it like a math superhero!",
                                "Is there anything you CAN'T multiply?",
                                "Gold star for you! ⭐",
                                "Brilliant! Absolutely brilliant!",
                                "Your brain cells are showing off!",
                                "That's the kind of math magic I like to see!",
                                "You're making numbers your best friends!",
                                "Calculated perfection!",
                                "Someone give this kid a trophy!",
                                "You've got multiplication superpowers!",
                                "Ka-pow! Right answer strikes again!",
                                "Your math muscles are STRONG!",
                                "That answer deserves a standing ovation!",
                                "You're on fire! 🔥 (Not literally, that would be bad)",
                                "Numbers tremble before your mighty brain!",
                                "Another one bites the dust! (The problem, I mean)",
                                "You're collecting correct answers like Pokemon!",
                                "Math level: EXPERT",
                                "You just made that look effortless!",
                                "Your brain should get its own award show!",
                                "Spectacular! Simply spectacular!",
                                "You're turning into a math legend!",
                                "That answer was chef's kiss! 👨‍🍳",
                                "Precision level: 100%",
                                "You're basically a walking textbook (but cooler)!",
                                "Math? More like piece of cake for you!",
                                "Your answer was so right, it's not even fair!",
                                "Bravo! Encore! Do it again!",
                                "You're making calculators feel insecure!",
                                "That brain of yours is working overtime!",
                                "Flawless victory!",
                                "You're dropping correct answers left and right!",
                                "Math ninja status: ACHIEVED",
                                "Your knowledge is showing!",
                                "That was mathematically magnificent!",
                                "You've got the golden touch with numbers!",
                                "Another victory for team You!",
                                "You're absolutely crushing it!",
                                "Brain power: MAXIMUM!",
                                "That answer was picture perfect!",
                                "You're on a hot streak! Keep it going!",
                                "Impressive doesn't even begin to cover it!",
                                "You make it look so easy!",
                                "Your math game is STRONG today!",
                                "Spot on! You nailed it!",
                                "That's what I call thinking fast!",
                                "You're a natural at this!",
                                "Awesome sauce! 🎉",
                                "You've got skills that pay the bills!",
                                "Math mastery in action!",
                                "You're writing your own success story!",
                                "That answer was pure gold!",
                                "You're collecting wins like trophies!",
                                "Phenomenal! Just phenomenal!",
                                "Your brain is in beast mode!",
                                "That's how champions do it!",
                                "You're spreading math excellence everywhere!",
                                "Nothing can stop you now!",
                                "You've got this down to a science!",
                                "Accuracy level: IMPRESSIVE!",
                                "You're a force of nature with numbers!",
                                "That answer was textbook perfect!",
                                "You're setting the bar HIGH!",
                                "Math goals: ACHIEVED",
                                "You're making this look like a breeze!",
                                "Stellar performance! ⭐",
                                "You're racking up wins like a pro!",
                                "That was absolutely fantastic!",
                                "Your confidence is well-earned!",
                                "Math champion in the making!",
                                "You're blazing through these problems!",
                                "Excellence is your middle name!",
                                "You're painting masterpieces with numbers!",
                                "That brain deserves a vacation after this!",
                                "You're unstoppable right now!",
                                "Math genius mode: ACTIVATED",
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
                                "Not quite, but you're getting warmer!",
                                "Your brain zigged when it should have zagged!",
                                "Math gremlins strike again!",
                                "That answer took the scenic route!",
                                "Oops! Your brain blinked!",
                                "So close you could taste it! (Don't eat math though)",
                                "That was a practice round, right?",
                                "Your answer went to a different universe!",
                                "The numbers played a trick on you!",
                                "Don't sweat it - redemption is just one problem away!",
                                "Your brain was thinking outside the box... way outside!",
                                "That answer had personality, just not accuracy!",
                                "Mistakes make your brain stronger!",
                                "You're still a math star, even when you miss!",
                                "That one slipped through the cracks!",
                                "Your neurons were having a coffee break!",
                                "Almost had it! The next one is yours!",
                                "Math decided to be sneaky that time!",
                                "No worries! Every expert was once a beginner!",
                                "That answer went rogue!",
                                "Your brain just needed a warm-up!",
                                "Shake it off and try again!",
                                "That was just a plot twist in your success story!",
                                "Numbers can be tricky little things!",
                                "Your brain was doing mental gymnastics!",
                                "That's called learning! Keep it up!",
                                "Everyone stumbles on the path to greatness!",
                                "Your answer was... unique! 😄",
                                "The math monster got you that time!",
                                "Bounce back! You've got this!",
                                "That answer took a detour!",
                                "Your brain was multitasking too hard!",
                                "Not this time, but maybe next time!",
                                "That one was a sneaky problem!",
                                "Your calculation took an unexpected turn!",
                                "Math happens! On to the next one!",
                                "That was just a tiny speed bump!",
                                "Your brain was on a mini adventure!",
                                "Whoops! Let's try that again!",
                                "That answer was in a parallel dimension!",
                                "Don't give up! Champions keep going!",
                                "Your neurons got their wires crossed!",
                                "That's what practice is for!",
                                "The answer fairy must have been sleeping!",
                                "You're still awesome, wrong answer and all!",
                                "That was a learning opportunity in disguise!",
                                "Your brain was testing out theories!",
                                "Oopsie! But you're still amazing!",
                                "That answer was feeling rebellious!",
                                "Math can be slippery sometimes!",
                                "Your thinking cap fell off for a second!",
                                "That's okay - even computers make errors!",
                                "Your brain was doing interpretive math!",
                                "Close only counts in horseshoes and hand grenades!",
                                "That answer was from an alternate timeline!",
                                "Your neurons took a wrong turn at Albuquerque!",
                                "Math gremlins: 1, You: Still winning overall!",
                                "That was a plot twist nobody saw coming!",
                                "Your answer was creative, just not correct!",
                                "Dust yourself off and try again!",
                                "That one was a tricky customer!",
                                "Your brain was thinking in cursive!",
                                "Not quite, but I believe in you!",
                                "That answer went on vacation!",
                                "Your calculation took a coffee break!",
                                "Math decided to be mysterious!",
                                "That's called character building!",
                                "Your brain was vibing on a different frequency!",
                                "Oops! But hey, you're still trying!",
                                "That answer was from the upside down!",
                                "Numbers can be mischievous little rascals!",
                                "Your thinking was in beta mode!",
                                "That was just a tiny hiccup!",
                                "Math threw you a curveball!",
                                "Your answer was fashionably incorrect!",
                                "Keep calm and math on!",
                                "That calculation went on an adventure!",
                                "Your brain was doing jazz math!",
                                "Not your best, but definitely not your last!",
                                "That answer had other plans!",
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
        <div x-show="gameStarted" x-cloak class="mt-8 text-center text-zinc-500 dark:text-zinc-400">
            {{ $score }} / {{ $totalAnswered }} correct ({{ $totalAnswered > 0 ? round(($score / $totalAnswered) * 100) : 0 }}%)
        </div>
    @endif
</div>
