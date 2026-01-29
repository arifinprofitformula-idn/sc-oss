{{--
    Animated Wave Component
    
    Parameters:
    - speed: Animation duration string (default: '25s')
    - height: CSS height for the wave container (default: '15vh')
    - minHeight: Minimum height constraint (default: '100px')
    - maxHeight: Maximum height constraint (default: '150px')
    
    Usage:
    <x-animated-wave speed="20s" height="20vh" />
--}}
@props([
    'speed' => '25s',
    'height' => '15vh',
    'minHeight' => '100px',
    'maxHeight' => '150px',
])

<div {{ $attributes->merge(['class' => 'fixed bottom-0 left-0 w-full z-0 pointer-events-none']) }} 
     id="wave-container" 
     style="height: {{ $height }}; min-height: {{ $minHeight }}; max-height: {{ $maxHeight }}; --wave-speed: {{ $speed }};">
    <svg class="waves w-full h-full" xmlns="http://www.w3.org/2000/svg"
    viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto" style="display: block;">
        <g class="parallax-wave">
            <!-- Layer 1 -->
            <path d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58-18 88 18 v44h-352z" 
                  fill="rgba(52, 152, 219, 0.5)" />
            <!-- Layer 2 -->
            <path d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58-18 88-18 58-18 88 18 v44h-352z" 
                  fill="rgba(52, 152, 219, 0.7)" />
            <!-- Layer 3 -->
            <path d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58-18 88 18 v44h-352z" 
                  fill="rgba(44, 62, 80, 0.8)" />
            <!-- Layer 4 -->
            <path d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58-18 88 18 v44h-352z" 
                  fill="#2c3e50" />
        </g>
    </svg>
</div>

<style>
    .parallax-wave > path {
        animation: move-forever var(--wave-speed) cubic-bezier(.55,.5,.45,.5) infinite;
        will-change: transform;
    }
    .parallax-wave > path:nth-child(1) {
        animation-delay: -2s;
        animation-duration: calc(var(--wave-speed) * 0.28);
    }
    .parallax-wave > path:nth-child(2) {
        animation-delay: -3s;
        animation-duration: calc(var(--wave-speed) * 0.4);
    }
    .parallax-wave > path:nth-child(3) {
        animation-delay: -4s;
        animation-duration: calc(var(--wave-speed) * 0.52);
    }
    .parallax-wave > path:nth-child(4) {
        animation-delay: -5s;
        animation-duration: calc(var(--wave-speed) * 0.8);
    }
    
    @keyframes move-forever {
        0% { transform: translate3d(-90px,0,0); }
        100% { transform: translate3d(85px,0,0); }
    }
    
    /* Accessibility: Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
        .parallax-wave > path {
            animation: none;
        }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        #wave-container { height: 100px !important; }
    }
    @media (max-width: 320px) {
        #wave-container { height: 60px !important; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const waveContainer = document.getElementById('wave-container');
        // Check for reduced motion preference before adding scroll listener
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (waveContainer && !prefersReducedMotion) {
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        const scrollPosition = window.scrollY;
                        // Limit the translation to avoid performance issues
                        if (scrollPosition < 500) {
                            waveContainer.style.transform = `translateY(${scrollPosition * 0.2}px)`;
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
        }
    });
</script>
