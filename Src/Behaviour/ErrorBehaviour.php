<?php

namespace Makhnanov\PhpSelfFilling\Behaviour;

enum ErrorBehaviour
{
    case THROW_AFTER_FIRST;
    case IGNORE;
    case REPLACE_WITH_DEFAULT;
}
