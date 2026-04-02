<?php

declare(strict_types=1);

return [

    /**
     * Add an additional second for every 100th word of the toast messages.
     *
     * Supported: true | false
     */
    'accessibility' => true,

    /**
     * The vertical alignment of the toast container.
     *
     * Supported: "bottom", "middle" or "top"
     */
    'alignment' => env('BEARNY_TOASTER_ALIGNMENT', 'top'),

    /**
     * Allow users to close toast messages prematurely.
     *
     * Supported: true | false
     */
    'closeable' => env('BEARNY_TOASTER_CLOSEABLE', true),

    /**
     * The on-screen duration of each toast.
     *
     * Minimum: 3000 (in milliseconds)
     */
    'duration' => env('BEARNY_TOASTER_DURATION', 3000),

    /**
     * The horizontal position of each toast.
     *
     * Supported: "center", "left" or "right"
     */
    'position' => env('BEARNY_TOASTER_POSITION', 'right'),

    /**
     * New toasts immediately replace similar ones, ensuring only one toast of a kind is visible at any time.
     * Takes precedence over the "suppress" option.
     *
     * Supported: true | false
     */
    'replace' => env('BEARNY_TOASTER_REPLACE', false),

    /**
     * Prevent the display of duplicate toast messages.
     *
     * Supported: true | false
     */
    'suppress' => env('BEARNY_TOASTER_SUPPRESS', false),

    /**
     * Whether messages passed as translation keys should be translated automatically.
     *
     * Supported: true | false
     */
    'translate' => env('BEARNY_TOASTER_TRANSLATE', true),
];
