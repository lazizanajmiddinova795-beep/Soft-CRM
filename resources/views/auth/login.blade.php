<x-guest-layout>
    <div x-data="neuralAuth()" class="glass-card max-w-md w-full p-10 sm:p-12 relative overflow-hidden">
        <!-- Scanline Effect -->
        <div x-show="scanning" class="scan-line"></div>

        <div class="mb-10 text-center">
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">OBSIDIAN OS</h1>
            <p class="text-[10px] font-black text-cyan-400 tracking-[0.5em] uppercase opacity-70">Neural Auth Protocol</p>
        </div>

        <x-auth-session-status class="mb-6" :status="session('status')" />

        <div class="flex gap-6 mb-10 border-b border-white/5 pb-4">
            <div class="flex-1 pb-2 text-[11px] font-black uppercase tracking-widest text-cyan-400 border-b-2 border-cyan-400 text-center">Credentials</div>
            <a href="{{ route('register') }}" 
                class="flex-1 pb-2 text-[11px] font-black uppercase tracking-widest transition-all text-white/30 hover:text-cyan-400 text-center">Join Mainframe</a>
        </div>

        <form id="cyberLoginForm" method="POST" action="{{ route('login') }}" @submit.prevent="initiateLink">
            @csrf
            
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/40 uppercase tracking-widest ml-4">Neural Identity</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                        class="w-full input-ios" placeholder="access@itcloud.uz">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-pink-500 text-[10px] font-bold px-4 uppercase" />
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/40 uppercase tracking-widest ml-4">Access Key</label>
                    <input type="password" name="password" required 
                        class="w-full input-ios" placeholder="••••••••">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-pink-500 text-[10px] font-bold px-4 uppercase" />
                </div>
            </div>

            <button type="submit" class="w-full btn-neon py-5 mt-10 text-[12px] tracking-[0.3em] uppercase flex justify-center items-center gap-3" :disabled="scanning">
                <i class="fa-solid fa-link text-sm"></i>
                <span x-text="scanning ? 'CONNECTING...' : 'INITIATE LINK'"></span>
            </button>
        </form>

        <!-- Scanning Overlay -->
        <div x-show="scanning" style="display: none;" class="absolute inset-0 z-[100] flex flex-col items-center justify-center bg-[#05050a]">
            <div class="relative w-52 h-52 mb-8">
                <!-- Ping effect -->
                <div class="absolute inset-[-8px] border-4 border-cyan-400/20 rounded-full animate-ping"></div>
                
                <!-- Camera video feed (circular) -->
                <div class="absolute inset-0 rounded-full overflow-hidden border-2 border-cyan-400/50 bg-black">
                    <video x-ref="cameraFeed" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]"></video>
                    <!-- Fallback icon when camera not available -->
                    <div x-show="!cameraActive" class="absolute inset-0 flex items-center justify-center bg-cyan-400/5">
                        <i class="fa-solid fa-fingerprint text-5xl text-cyan-400 animate-pulse"></i>
                    </div>
                    <!-- Scan line over video -->
                    <div class="absolute left-0 right-0 h-[2px] bg-cyan-400 shadow-[0_0_12px_rgba(0,255,204,0.8)] animate-[faceScan_2s_linear_infinite]"></div>
                </div>

                <!-- Progress ring -->
                <svg class="absolute inset-[-4px] w-[calc(100%+8px)] h-[calc(100%+8px)] transform -rotate-90">
                    <circle cx="50%" cy="50%" r="106" stroke="currentColor" stroke-width="3" fill="transparent" class="text-white/5" />
                    <circle cx="50%" cy="50%" r="106" stroke="currentColor" stroke-width="3" fill="transparent" 
                        :stroke-dasharray="2 * Math.PI * 106" 
                        :stroke-dashoffset="2 * Math.PI * 106 * (1 - progress / 100)" 
                        class="text-cyan-400 transition-all duration-300" />
                </svg>
            </div>

            <h2 class="text-xs font-black text-cyan-400 uppercase tracking-[0.3em] mb-4" x-text="scanMessage"></h2>
            <div class="w-52 h-0.5 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-cyan-400 to-purple-500 transition-all duration-300 shadow-[0_0_15px_rgba(0,255,204,0.6)]" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes faceScan {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('neuralAuth', () => ({
                scanning: false,
                progress: 0,
                cameraActive: false,
                scanMessage: 'INITIALIZING CAMERA...',

                async initiateLink() {
                    this.scanning = true;
                    this.progress = 5;
                    this.scanMessage = 'INITIALIZING CAMERA...';

                    // Try to open the camera
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: 'user', width: 320, height: 320 } 
                        });
                        this.$refs.cameraFeed.srcObject = stream;
                        this.cameraActive = true;
                        this.scanMessage = 'SCANNING FACE...';
                    } catch (err) {
                        console.warn('Camera not available:', err);
                        this.cameraActive = false;
                        this.scanMessage = 'ENCRYPTING DATA...';
                    }

                    this.progress = 15;

                    // Run progress animation (takes approx. 30 seconds to reach 100%)
                    let interval = setInterval(() => {
                        this.progress += (Math.random() * 0.7) + 0.5;

                        if (this.progress >= 25 && this.progress < 50) {
                            this.scanMessage = this.cameraActive ? 'ANALYZING BIOMETRICS...' : 'ESTABLISHING HANDSHAKE...';
                        }
                        if (this.progress >= 50 && this.progress < 75) {
                            this.scanMessage = this.cameraActive ? 'FACE RECOGNIZED' : 'VERIFYING CREDENTIALS...';
                        }
                        if (this.progress >= 75 && this.progress < 95) {
                            this.scanMessage = 'VERIFYING CREDENTIALS...';
                        }
                        if (this.progress >= 95) {
                            this.scanMessage = 'ACCESS AUTHORIZED';
                        }

                        if (this.progress >= 100) {
                            this.progress = 100;
                            clearInterval(interval);

                            // Stop camera
                            if (this.cameraActive && this.$refs.cameraFeed.srcObject) {
                                this.$refs.cameraFeed.srcObject.getTracks().forEach(t => t.stop());
                            }

                            setTimeout(() => document.getElementById('cyberLoginForm').submit(), 600);
                        }
                    }, 300);
                }
            }));
        });
    </script>
</x-guest-layout>

