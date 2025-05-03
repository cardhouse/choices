<div
    x-data="{
        time: @js($initialTime),
        direction: @js($direction),
        formattedTime: '00:00',
        timer: null,
        isRunning: true,
        showPopup: false,
        stopData: null,
        init() {
            this.updateDisplay();
            this.startTimer();
            this.$watch('isRunning', value => {
                if (!value) clearInterval(this.timer);
            });
            
            @this.on('timerFinished', (data) => {
                this.stopData = {
                    timestamp: parseInt(data.timestamp),
                    startTime: parseInt(data.startTime),
                    elapsedTime: parseInt(data.elapsedTime)
                };
                this.showPopup = true;
            });
        },
        startTimer() {
            this.timer = setInterval(() => {
                if (!this.isRunning) return;
                if (this.direction === 'down') {
                    if (this.time > 0) {
                        this.time--;
                        this.updateDisplay();
                    } else {
                        this.$wire.stop();
                    }
                } else {
                    this.time++;
                    this.updateDisplay();
                }
            }, 1000);
        },
        updateDisplay() {
            const hours = Math.floor(this.time / 3600);
            const minutes = Math.floor((this.time % 3600) / 60);
            const seconds = this.time % 60;
            
            let timeParts = [];
            
            if (hours > 0) {
                timeParts.push(hours.toString().padStart(2, '0'));
            }
            
            if (hours > 0 || minutes > 0) {
                timeParts.push(minutes.toString().padStart(2, '0'));
            }
            
            timeParts.push(seconds.toString().padStart(2, '0'));
            
            this.formattedTime = timeParts.join(':');
        },
        formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp * 1000);
            return date.toLocaleString();
        },
        formatElapsedTime(seconds) {
            if (!seconds) return '0s';
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            let parts = [];
            if (hours > 0) parts.push(`${hours}h`);
            if (minutes > 0) parts.push(`${minutes}m`);
            parts.push(`${secs}s`);
            
            return parts.join(' ');
        }
    }"
    class="flex flex-col items-center justify-center p-4"
>
    <div class="text-4xl font-bold text-gray-800" x-text="formattedTime"></div>
    
    @if($direction === 'up')
        <button
            wire:click="stop"
            @click.prevent="isRunning = false"
            class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
        >
            Stop Timer
        </button>
    @endif

    <!-- Timer Stop Popup -->
    <div
        x-show="showPopup"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.away="showPopup = false"
    >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Timer Stopped</h3>
            <div class="space-y-2">
                <p><span class="font-medium">Stopped at:</span> <span x-text="formatTimestamp(stopData?.timestamp)"></span></p>
                <p><span class="font-medium">Started at:</span> <span x-text="formatTimestamp(stopData?.startTime)"></span></p>
                <p><span class="font-medium">Total time:</span> <span x-text="formatElapsedTime(stopData?.elapsedTime)"></span></p>
            </div>
            <button
                @click="showPopup = false"
                class="mt-4 w-full px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
            >
                Close
            </button>
        </div>
    </div>
</div> 