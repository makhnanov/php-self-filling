<?php

namespace Makhnanov\PhpSelfFilling\Behaviour;

enum MissingData
{
    case REPLACE_WITH_DEFAULT;
    case IGNORE;
    case THROW_AFTER_FIRST;
}
