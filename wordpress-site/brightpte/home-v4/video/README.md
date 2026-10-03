# Student journey video (Home v4 hero)

40-second, 1280×720, 30 fps motion piece: counselling → PTE prep → university
offer → visa guidance → fly, then the free-counselling call to action.

- `journey.html` is the source. Every frame is drawn by `render(t)` for
  `t` in seconds, so the video is fully deterministic.
- `rec.js` steps `render(t)` at 30 fps in headless Chromium and pipes the
  frames to ffmpeg (H.264 MP4). `journey.html` expects `logo.png` and the
  Inter Tight / Gloria Hallelujah fontsource packages under `fonts/`.
- The WebM (VP9) copy is a fallback for browsers without H.264.

Deployed to `wp-content/uploads/2026/10/` (media IDs 3170 MP4, 3171 poster).
