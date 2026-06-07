{{-- ══════════════════════════════════════════════════════
     Admin Clock Widget — include inside the header flex row
     Click cycles through 4 variations; choice saved in cookie.
══════════════════════════════════════════════════════ --}}
<div id="dash-clock-widget" onclick="dashClockNext(event)" title="Click to change style"
     style="display:flex;align-items:center;gap:.55rem;cursor:pointer;user-select:none;padding-bottom:.1rem;">

    <div id="dash-clock-face" style="min-width:148px;">

        {{-- V0: Sweeping ring --}}
        <div id="dcv-0" style="display:flex;align-items:center;gap:.7rem;">
            <svg width="46" height="46" viewBox="0 0 46 46" style="flex-shrink:0;transform:rotate(-90deg);overflow:visible;">
                <defs>
                    <linearGradient id="dcG0" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#1A3C5E"/><stop offset="100%" stop-color="#2E86C1"/>
                    </linearGradient>
                </defs>
                <circle cx="23" cy="23" r="18" fill="none" stroke="rgba(26,60,94,.1)" stroke-width="2.5"/>
                <circle id="dc-ring" cx="23" cy="23" r="18" fill="none" stroke="url(#dcG0)"
                        stroke-width="2.5" stroke-linecap="round" stroke-dasharray="113.1" stroke-dashoffset="113.1"/>
            </svg>
            <div style="text-align:left;">
                <div style="display:flex;align-items:baseline;gap:.25rem;line-height:1;">
                    <span class="dc-time" style="font-size:1.75rem;font-weight:800;color:var(--primary);letter-spacing:-1.5px;font-variant-numeric:tabular-nums;"></span>
                    <span class="dc-ampm" style="font-size:.62rem;font-weight:600;color:var(--accent);opacity:.6;letter-spacing:.02em;"></span>
                </div>
                <div class="dc-date" style="font-size:.72rem;color:#6b7280;font-weight:500;margin-top:.25rem;letter-spacing:.01em;white-space:nowrap;"></div>
            </div>
        </div>

        {{-- V1: Segmented dots --}}
        <div id="dcv-1" style="display:none;text-align:right;">
            <div style="display:flex;align-items:baseline;gap:.25rem;justify-content:flex-end;line-height:1;">
                <span class="dc-time" style="font-size:1.75rem;font-weight:800;color:var(--primary);letter-spacing:-1.5px;font-variant-numeric:tabular-nums;"></span>
                <span class="dc-ampm" style="font-size:.62rem;font-weight:600;color:var(--accent);opacity:.6;letter-spacing:.02em;"></span>
            </div>
            <div style="display:inline-block;">
                <div style="display:flex;gap:4px;margin:.42rem 0 .28rem;justify-content:space-between;">
                    @for($i = 0; $i < 12; $i++)
                    <div id="dc-dot-{{ $i }}" style="width:7px;height:7px;border-radius:50%;background:rgba(26,60,94,.12);"></div>
                    @endfor
                </div>
                <div class="dc-date" style="font-size:.72rem;color:#6b7280;font-weight:500;letter-spacing:.01em;white-space:nowrap;"></div>
            </div>
        </div>

        {{-- V2: Hourglass --}}
        <div id="dcv-2" style="display:none;">
            <div style="display:flex;align-items:center;gap:.65rem;">
                <svg id="dc-hg-svg" width="38" height="48" viewBox="-22 -28 44 56" style="flex-shrink:0;filter:drop-shadow(0 1px 3px rgba(26,60,94,.15));transform-origin:50% 50%;">
                    <defs>
                        <clipPath id="dc-hg-tc"><rect id="dc-hg-top-rect" x="-22" y="-26" width="44" height="23"/></clipPath>
                        <clipPath id="dc-hg-bc"><rect id="dc-hg-bot-rect" x="-22" y="26" width="44" height="0"/></clipPath>
                    </defs>
                    <path d="M -20 -26 L 20 -26 L 3 -3 L 3 3 L 20 26 L -20 26 L -3 3 L -3 -3 Z"
                          fill="rgba(26,60,94,.04)" stroke="rgba(26,60,94,.22)" stroke-width="1.4" stroke-linejoin="round"/>
                    <path d="M -20 -26 L 20 -26 L 3 -3 L -3 -3 Z"
                          fill="#2E86C1" opacity=".78" clip-path="url(#dc-hg-tc)"/>
                    <path d="M -3 3 L 3 3 L 20 26 L -20 26 Z"
                          fill="#2E86C1" opacity=".78" clip-path="url(#dc-hg-bc)"/>
                    <circle id="dc-hg-drip" cx="0" cy="-3" r="1.6" fill="#2E86C1" opacity=".9"/>
                    <path d="M -20 -26 L 20 -26 L 3 -3 L 3 3 L 20 26 L -20 26 L -3 3 L -3 -3 Z"
                          fill="none" stroke="rgba(26,60,94,.28)" stroke-width="1.4" stroke-linejoin="round"/>
                </svg>
                <div style="text-align:left;">
                    <div style="display:flex;align-items:baseline;gap:.25rem;line-height:1;">
                        <span class="dc-time" style="font-size:1.75rem;font-weight:800;color:var(--primary);letter-spacing:-1.5px;font-variant-numeric:tabular-nums;"></span>
                        <span class="dc-ampm" style="font-size:.62rem;font-weight:600;color:var(--accent);opacity:.6;letter-spacing:.02em;"></span>
                    </div>
                    <div class="dc-date" style="font-size:.72rem;color:#6b7280;font-weight:500;margin-top:.25rem;letter-spacing:.01em;white-space:nowrap;"></div>
                </div>
            </div>
        </div>

        {{-- V3: Comet streak --}}
        <div id="dcv-3" style="display:none;text-align:right;">
            <div style="display:flex;align-items:baseline;gap:.25rem;justify-content:flex-end;line-height:1;">
                <span class="dc-time" style="font-size:1.75rem;font-weight:800;color:var(--primary);letter-spacing:-1.5px;font-variant-numeric:tabular-nums;"></span>
                <span class="dc-ampm" style="font-size:.62rem;font-weight:600;color:var(--accent);opacity:.6;letter-spacing:.02em;"></span>
            </div>
            <div style="display:inline-block;">
                <div style="position:relative;height:10px;margin:.42rem 0 .28rem;">
                    <div style="position:absolute;top:4px;left:0;right:0;height:2px;background:rgba(26,60,94,.08);border-radius:999px;"></div>
                    <div id="dc-comet-tail" style="position:absolute;top:4px;left:0;width:0;height:2px;border-radius:999px;background:linear-gradient(to right,rgba(26,60,94,.1),rgba(46,134,193,.55));"></div>
                    <div id="dc-comet" style="position:absolute;top:0;left:0;width:10px;height:10px;border-radius:50%;background:var(--accent);box-shadow:0 0 7px rgba(46,134,193,.75);transform:translateX(-50%);"></div>
                </div>
                <div class="dc-date" style="font-size:.72rem;color:#6b7280;font-weight:500;letter-spacing:.01em;white-space:nowrap;"></div>
            </div>
        </div>

    </div>

    {{-- Variation indicator dots --}}
    <div id="dash-clock-dots" style="display:flex;flex-direction:column;gap:4px;align-self:center;opacity:1;transition:opacity 1.2s ease;">
        <div id="dcvd-0" style="width:5px;height:5px;border-radius:50%;background:var(--primary);transition:background .2s,transform .2s;"></div>
        <div id="dcvd-1" style="width:5px;height:5px;border-radius:50%;background:rgba(26,60,94,.2);transition:background .2s,transform .2s;"></div>
        <div id="dcvd-2" style="width:5px;height:5px;border-radius:50%;background:rgba(26,60,94,.2);transition:background .2s,transform .2s;"></div>
        <div id="dcvd-3" style="width:5px;height:5px;border-radius:50%;background:rgba(26,60,94,.2);transition:background .2s,transform .2s;"></div>
    </div>

</div>
<script>
(function () {
    // Generation counter — when AJAX loads a new page this increments, killing the old rAF loop.
    window._dashClockGen = (window._dashClockGen || 0) + 1;
    var myGen = window._dashClockGen;

    var DAYS   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var RING_CIRC = 2 * Math.PI * 18;
    var lastMinute = -1;
    var cometReset = false;
    var hgFlipped = false, hgFlipInProgress = false, hgFlipStart = 0, hgTotalRotation = 0;
    var dotsFadeTimer = null;

    function getCookie(n) { var m = document.cookie.match('(^| )' + n + '=([^;]+)'); return m ? m[2] : null; }
    function setCookie(n, v) { document.cookie = n + '=' + v + '; path=/; max-age=31536000; SameSite=Lax'; }

    var curV = Math.min(3, Math.max(0, parseInt(getCookie('dash_clock_v') || '0', 10)));

    function showDots() {
        clearTimeout(dotsFadeTimer);
        var d = document.getElementById('dash-clock-dots');
        if (d) d.style.opacity = '1';
    }
    function startDotsFade(delay) {
        clearTimeout(dotsFadeTimer);
        dotsFadeTimer = setTimeout(function() {
            var d = document.getElementById('dash-clock-dots');
            if (d) d.style.opacity = '0';
        }, delay || 2200);
    }

    var widget = document.getElementById('dash-clock-widget');
    if (widget) {
        widget.addEventListener('mouseenter', showDots);
        widget.addEventListener('mouseleave', function() { startDotsFade(1500); });
    }
    startDotsFade(3000);

    function showVariation(v) {
        for (var i = 0; i < 4; i++) {
            var face = document.getElementById('dcv-' + i);
            var dot  = document.getElementById('dcvd-' + i);
            if (face) face.style.display = i === v ? (i === 0 ? 'flex' : 'block') : 'none';
            if (dot) {
                dot.style.background = i === v ? 'var(--primary)' : 'rgba(26,60,94,.2)';
                dot.style.transform  = i === v ? 'scale(1.3)' : 'scale(1)';
            }
        }
    }

    window.dashClockNext = function (e) {
        if (e) e.stopPropagation();
        curV = (curV + 1) % 4;
        setCookie('dash_clock_v', curV);
        showVariation(curV);
        showDots();
        startDotsFade(2200);
    };

    showVariation(curV);

    function frame() {
        if (window._dashClockGen !== myGen) return; // stale loop — stop
        var now = new Date();
        var h = now.getHours(), m = now.getMinutes(), s = now.getSeconds(), ms = now.getMilliseconds();
        var progress = (s * 1000 + ms) / 60000;

        if (m !== lastMinute) {
            var ampm = h >= 12 ? 'pm' : 'am';
            var h12  = h % 12 || 12;
            var time = h12 + ':' + String(m).padStart(2, '0');
            var date = DAYS[now.getDay()] + ', ' + MONTHS[now.getMonth()] + ' ' + now.getDate() + ', ' + now.getFullYear();
            document.querySelectorAll('.dc-time').forEach(function(el) { el.textContent = time; });
            document.querySelectorAll('.dc-ampm').forEach(function(el) { el.textContent = ampm; });
            document.querySelectorAll('.dc-date').forEach(function(el) { el.textContent = date; });
            lastMinute = m;
        }

        // V0: ring
        var ring = document.getElementById('dc-ring');
        if (ring) ring.style.strokeDashoffset = (RING_CIRC * (1 - progress)).toFixed(4);

        // V1: segmented dots
        var activeDot = Math.floor(s / 5);
        for (var i = 0; i < 12; i++) {
            var d = document.getElementById('dc-dot-' + i);
            if (!d) continue;
            if (i < activeDot) {
                d.style.background = 'var(--accent)';
                d.style.boxShadow  = '0 0 4px rgba(46,134,193,.5)';
            } else if (i === activeDot) {
                var frac = ((s % 5) * 1000 + ms) / 5000;
                d.style.background = 'rgba(46,134,193,' + (0.15 + frac * 0.85).toFixed(3) + ')';
                d.style.boxShadow  = 'none';
            } else {
                d.style.background = 'rgba(26,60,94,.12)';
                d.style.boxShadow  = 'none';
            }
        }

        // V2: hourglass — rotates 180° on reset then refills top-down
        var hgSvg     = document.getElementById('dc-hg-svg');
        var hgTopRect = document.getElementById('dc-hg-top-rect');
        var hgBotRect = document.getElementById('dc-hg-bot-rect');
        var hgDrip    = document.getElementById('dc-hg-drip');
        if (hgTopRect && hgBotRect && hgSvg) {
            // Trigger flip on minute reset
            if (s === 0 && ms < 150 && !hgFlipInProgress) {
                hgFlipInProgress = true;
                hgFlipStart = performance.now();
            }

            if (hgFlipInProgress) {
                var elapsed = performance.now() - hgFlipStart;
                var t = Math.min(1, elapsed / 700);
                var ease = t < 0.5 ? 2*t*t : -1+(4-2*t)*t;
                hgSvg.style.transform = 'rotate(' + (hgTotalRotation + 180 * ease) + 'deg)';
                if (hgDrip) hgDrip.setAttribute('opacity', '0');
                if (t >= 1) {
                    hgTotalRotation += 180;
                    hgFlipped = !hgFlipped;
                    hgFlipInProgress = false;
                }
            } else {
                var topH, botH, botY;
                if (!hgFlipped) {
                    // Normal orientation: top drains → bottom fills
                    topH = 23 * (1 - progress);
                    botH = 23 * progress;
                    botY = 26 - 23 * progress;
                } else {
                    // Flipped 180°: in SVG coords top fills, bottom drains
                    // (visually: top fills because the SVG is upside-down)
                    topH = 23 * progress;
                    botH = 23 * (1 - progress);
                    botY = 3 + 23 * progress;
                }
                hgTopRect.setAttribute('height', topH.toFixed(3));
                hgBotRect.setAttribute('height', botH.toFixed(3));
                hgBotRect.setAttribute('y',      botY.toFixed(3));
                if (hgDrip) {
                    // Drip falls through the pinch; direction reverses with orientation
                    var dripCy = hgFlipped
                        ? (3  - 6 * (ms / 1000)).toFixed(2)
                        : (-3 + 6 * (ms / 1000)).toFixed(2);
                    hgDrip.setAttribute('cy', dripCy);
                    hgDrip.setAttribute('opacity', progress < 0.99 ? '0.88' : '0');
                }
            }
        }

        // V3: comet
        var comet = document.getElementById('dc-comet');
        var tail  = document.getElementById('dc-comet-tail');
        if (comet && tail) {
            if (s === 0 && ms < 150 && !cometReset) {
                cometReset = true;
                comet.style.transition = 'left .75s cubic-bezier(.4,0,.2,1)';
                tail.style.transition  = 'width .75s cubic-bezier(.4,0,.2,1)';
                comet.style.left = '0%';
                tail.style.width = '0%';
                setTimeout(function () {
                    cometReset = false;
                    var c2 = document.getElementById('dc-comet');
                    var t2 = document.getElementById('dc-comet-tail');
                    if (c2) c2.style.transition = 'none';
                    if (t2) t2.style.transition  = 'none';
                }, 850);
            } else if (!cometReset) {
                var pct = (progress * 100).toFixed(3) + '%';
                comet.style.left = pct;
                tail.style.width = pct;
            }
        }

        requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
})();
</script>
