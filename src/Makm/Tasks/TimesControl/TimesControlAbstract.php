<?php

namespace Makm\Tasks\TimesControl;

abstract class TimesControlAbstract implements TimesControlInterface
{
    /**
     * TimesControlAbstract constructor.
     * @param array $optionSetup
     */
    public function __construct(array $optionSetup = [])
    {
        $this->resetScheme();
        $this->setup($optionSetup);
    }

}
