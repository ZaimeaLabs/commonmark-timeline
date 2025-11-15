<?php

declare(strict_types=1);

namespace Zaimea\CommonMark\Timeline\Node;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\TightBlockInterface;

class Timeline extends AbstractBlock implements TightBlockInterface
{
    private bool $tight;

    public function __construct(bool $tight = false)
    {
        parent::__construct();

        $this->tight = $tight;
    }

    public function isTight(): bool
    {
        return $this->tight;
    }

    public function setTight(bool $tight): void
    {
        $this->tight = $tight;
    }
}
