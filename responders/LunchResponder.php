<?php
namespace jt2k\Jarvis;

class LunchResponder extends RandomResponder
{
    public static $pattern = 'lunch';
    protected static $options = array(
        "Bread & Co",
        "Moe's",
        "Chipotle",
        "Hog Heaven",
        "Samurai Sushi",
        "Bombay Palace",
        "Panera",
        "Which Wich",
        "Taziki's",
        "P.F. Chang's"
    );
}
