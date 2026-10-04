<?php

// Opening/closing time options for the admin shop form: every 30 minutes
// from 00:00 to 23:30. The old hand-written list skipped some slots
// (e.g. 21:00) and contained invalid values such as 24:30.
return array_map(
    fn (int $minutes) => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
    range(0, 23 * 60 + 30, 30)
);
