/**
 * TCM One — Frameless Hero Intro Video Handler
 */
document.addEventListener('DOMContentLoaded', () => {
    const heroPosterOverlay = document.getElementById('heroPosterOverlay');
    const heroIframePlayer = document.getElementById('heroIframePlayer');
    const heroWatchIntroBtn = document.getElementById('heroWatchIntroBtn');
    const heroVideoWrapper = document.getElementById('heroVideoWrapper');

    const YOUTUBE_VIDEO_ID = 'KZwKFz-ZJDU';
    const YOUTUBE_EMBED_URL = `https://www.youtube.com/embed/${YOUTUBE_VIDEO_ID}?autoplay=1&rel=0&enablejsapi=1`;

    function playHeroVideo() {
        if (heroPosterOverlay && heroIframePlayer) {
            heroPosterOverlay.classList.add('is-playing');
            if (!heroIframePlayer.getAttribute('src')) {
                heroIframePlayer.setAttribute('src', YOUTUBE_EMBED_URL);
            }
        }
    }

    if (heroPosterOverlay) {
        heroPosterOverlay.addEventListener('click', playHeroVideo);
    }

    if (heroWatchIntroBtn) {
        heroWatchIntroBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (heroVideoWrapper) {
                heroVideoWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    playHeroVideo();
                }, 400);
            }
        });
    }
});
