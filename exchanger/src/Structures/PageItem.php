<?php

namespace App\Structures;

class PageItem
{
    /** @var string */
    public $href;

    /** @var bool */
    public $disabled;

    /** @var string */
    public $text;

    /** @var bool */
    public $display;

    public function __construct(string $text, string $href, bool $disabled, bool $display)
    {
        $this->text = $text;
        $this->href = $href;
        $this->disabled = $disabled;
        $this->display = $display;
    }
}
