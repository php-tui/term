<?php

namespace PhpTui\Term;

enum CursorStyle
{
    /**
     * Default cursor shape configured by the user.
     */
    case DefaultUserShape;
    /**
     * A blinking block cursor shape (■).
     */
    case BlinkingBlock;
    /**
     * A non blinking block cursor shape (inverse of `BlinkingBlock`).
     */
    case SteadyBlock;
    /**
     * A blinking underscore cursor shape(_).
     */
    case BlinkingUnderScore;
    /**
     * A non blinking underscore cursor shape (inverse of `BlinkingUnderScore`).
     */
    case SteadyUnderScore;
    /**
     * A blinking cursor bar shape (|)
     */
    case BlinkingBar;
    /**
     * A steady cursor bar shape (inverse of `BlinkingBar`).
     */
    case SteadyBar;
}
