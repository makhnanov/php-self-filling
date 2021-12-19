<?php

namespace Makhnanov\Php81SelfFilling\Behaviour;

enum MissingData
{
    case REPLACE_WITH_DEFAULT;
    case IGNORE;
    case THROW_AFTER_FIRST;
}
