<?php

namespace Makm\Tasks\TimesControl;

interface TimesControlInterface
{

    public function setup($options);

    public function resetScheme();

    public function isNow($timeNow);
}
